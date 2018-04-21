<?php

namespace Orphans\GitDeploy\Http;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

use Artisan;
use Log;
use Event;

use Orphans\GitDeploy\Events\GitDeployed;

class GitDeployController extends Controller
{
    public function gitHook(Request $request)
    {

        // create a log channel
        $log = new Logger('gitdeploy');
        $log->pushHandler(new StreamHandler(storage_path('logs/gitdeploy.log'), Logger::WARNING));

        $git_path = !empty(config('gitdeploy.git_path')) ? config('gitdeploy.git_path') : 'git';
        $git_remote = !empty(config('gitdeploy.remote')) ? config('gitdeploy.remote') : 'origin';

        // Limit to known servers
        if (!empty(config('gitdeploy.allowed_sources'))) {
            $remote_ip = $this->formatIPAddress($_SERVER['REMOTE_ADDR']);
            $allowed_sources = array_map([$this, 'formatIPAddress'], config('gitdeploy.allowed_sources'));

            if (!in_array($remote_ip, $allowed_sources)) {
                $log->addError('Request must come from an approved IP');
                return Response::json([
                            'success' => false,
                            'message' => 'Request must come from an approved IP',
                        ], 401);
            }
        }

        // Collect the posted data
        $postdata = json_decode($request->getContent(), true);
        if (empty($postdata)) {
            $log->addError('Web hook data does not look valid');
            return Response::json([
                        'success' => false,
                        'message' => 'Web hook data does not look valid',
                ], 400);
        }

        // Check the config's directory
        $repo_dir = config('gitdeploy.repo_path');
        if (!empty($repo_dir) && !file_exists($repo_dir.'/.git/config')) {
            $log->addError('Invalid repo path in config');
            return Response::json([
                'success' => false,
                'message' => 'Invalid repo path in config',
            ], 500);
        }

        // Try to determine Laravel's directory going up paths until we find a .env
        if (empty($repo_dir)) {
            $checked[] = $repo_dir;
            $repo_dir = __DIR__;
            do {
                $repo_dir = dirname($repo_dir);
            } while ($repo_dir !== '/' && !file_exists($repo_dir.'/.env'));
        }

        // This is not necessarily the repo's root so go up more paths if necessary
        if ($repo_dir !== '/') {
            while ($repo_dir !== '/' && !file_exists($repo_dir.'/.git/config')) {
                $repo_dir = dirname($repo_dir);
            }
        }

        // So, do we have something valid?
        if ($repo_dir === '/' || !file_exists($repo_dir.'/.git/config')) {
            $log->addError('Could not determine the repo path');
            return Response::json([
                'success' => false,
                'message' => 'Could not determine the repo path',
            ], 500);
        }

        // Check signatures
        if (!empty(config('gitdeploy.secret'))) {
            $header = config('gitdeploy.secret_header');
            $header_data = $request->header($header);

            /**
             * Check for valid header
             */
            if (!$header_data) {
                $log->addError('Could not find header with name ' . $header);
                return Response::json([
                    'success' => false,
                    'message' => 'Could not find header with name ' . $header,
                ], 401);
            }

            /**
             * Sanity check for key
             */
            if (empty(config('gitdeploy.secret_key'))) {
                $log->addError('Secret was set to true but no secret_key specified in config');
                return Response::json([
                    'success' => false,
                    'message' => 'Secret was set to true but no secret_key specified in config',
                ], 500);
            }

            /**
             * Check plain secrets (Gitlab)
             */
            if (config('gitdeploy.secret_type') == 'plain') {
                if ($header_data !== config('gitdeploy.secret_key')) {
                    $log->addError('Secret did not match');
                    return Response::json([
                        'success' => false,
                        'message' => 'Secret did not match',
                    ], 401);
                }
            }

            /**
             * Check hmac secrets (Github)
             */
            elseif (config('gitdeploy.secret_type') == 'mac') {
                if (!hash_equals('sha1=' . hash_hmac('sha1', $request->getContent(), config('gitdeploy.secret')))) {
                    $log->addError('Secret did not match');
                    return Response::json([
                        'success' => false,
                        'message' => 'Secret did not match',
                    ], 401);
                }
            }

            /**
             * Catch all for anything odd in config
             */
            else {
                $log->addError('Unsupported secret type');
                return Response::json([
                    'success' => false,
                    'message' => 'Unsupported secret type',
                ], 422);
            }

            // If we get this far then the secret matched, lets go ahead!
        }

        // Get current branch this repository is on
        $cmd = escapeshellcmd($git_path) . ' --git-dir=' . escapeshellarg($repo_dir . '/.git') .  ' --work-tree=' . escapeshellarg($repo_dir) . ' rev-parse --abbrev-ref HEAD';
        $current_branch = trim(exec($cmd)); //Alternativly shell_exec

        // Get branch this webhook is for if push event
        if (isset($postdata['ref'])) {
            $pushed_branch = explode('/', $postdata['ref']);
            $pushed_branch = trim($pushed_branch[2]);
            // Get branch this webhook is for if pull request
        } elseif (isset($postdata['base']['ref'])) {
            $pushed_branch = explode('/', $postdata['base']['ref']);
            $pushed_branch = trim($pushed_branch[2]);
            // Get branch fails
        } else {
            $log->addWarning('Could not determine refs for action');
            return Response::json([
                'success' => false,
                'message' => 'Could not determine refs for action',
            ], 422);
        }

        // If the refs don't matchthis branch, then no need to do a git pull
        if ($current_branch !== $pushed_branch) {
            $log->addWarning('Pushed refs do not match current branch');
            return Response::json([
                'success' => false,
                'message' => 'Pushed refs do not match current branch',
            ], 422);
        }

        // At this point we're happy everything is OK to pull, lets put Laravel into Maintenance mode.
        if (!empty(config('gitdeploy.maintenance_mode'))) {
            Log::info('Gitdeploy: putting site into maintenance mode');
            Artisan::call('down');
        }

        // git pull
        Log::info('Gitdeploy: Pulling latest code on to server');
        $cmd = escapeshellcmd($git_path) . ' --git-dir=' . escapeshellarg($repo_dir . '/.git') . ' --work-tree=' . escapeshellarg($repo_dir) . ' pull ' . escapeshellarg($git_remote) . ' ' . escapeshellarg($current_branch) . ' &>> ' . escapeshellarg($repo_dir . '/storage/logs/gitdeploy.log');

        $server_response = [
            'cmd' => $cmd,
            'user' => shell_exec('whoami'),
            'response' => shell_exec($cmd),
        ];

        //Lets see if we have commands to run and run them
        if (!empty(config('gitdeploy.commands'))) {
            $commands = config('gitdeploy.commands');
            $command_results = array();
            foreach ($commands as $command) {
                $cmd = escapeshellcmd($command). ' &>> ' . escapeshellarg($repo_dir . '/storage/logs/gitdeploy.log');
                Log::info('Gitdeploy: Running post pull command: '.$cmd);
                exec($cmd, $output, $returnCode);
                array_push($command_results, [
                    'cmd' => $cmd,
                    'output' => $output,
                    'return_code' => $returnCode,
                ]);
                Log::info('Gitdeploy: ' . $cmd . 'finished with code: ' . $returnCode);
                Log::info('Gitdeploy: ' . $cmd . 'output: ' . $output);
            }
        }

        // Put site back up and end maintenance mode
        if (!empty(config('gitdeploy.maintenance_mode'))) {
            Artisan::call('up');
            Log::info('Gitdeploy: taking site out of maintenance mode');
        }

        // Fire Event that git were deployed
        if (!empty(config('gitdeploy.fire_event'))) {
            event(new GitDeployed($postdata['commits']));
            Log::debug('Gitdeploy: Event GitDeployed fired');
        }

        if (!empty(config('gitdeploy.email_recipients'))) {

            // Humanise the commit log
            foreach ($postdata['commits'] as $commit_key => $commit) {

                // Split message into subject + description (Assumes Git's recommended standard where first line is the main summary)
                $subject = strtok($commit['message'], "\n");
                $description = '';

                // Beautify date
                $date = new \DateTime($commit['timestamp']);
                $date_str = $date->format('d/m/Y, g:ia');

                $postdata['commits'][$commit_key]['human_id'] = substr($commit['id'], 0, 9);
                $postdata['commits'][$commit_key]['human_subject'] = $subject;
                $postdata['commits'][$commit_key]['human_description'] = $description;
                $postdata['commits'][$commit_key]['human_date'] = $date_str;
            }

            // Use package's own sender or the project default?
            $addressdata['sender_name'] = config('mail.from.name');
            $addressdata['sender_address'] = config('mail.from.address');
            if (config('gitdeploy.email_sender.address') !== null) {
                $addressdata['sender_name'] = config('gitdeploy.email_sender.name');
                $addressdata['sender_address'] = config('gitdeploy.email_sender.address');
            }

            // Recipients
            $addressdata['recipients'] = config('gitdeploy.email_recipients');

            // Template
            $emailTemplate = config('gitdeploy.email_template', 'gitdeploy::email');

            // Todo: Put Mail send into queue to improve performance
            \Mail::send($emailTemplate, [ 'server' => $server_response, 'git' => $postdata, 'command_results' => $command_results ], function ($message) use ($postdata, $addressdata) {
                $message->from($addressdata['sender_address'], $addressdata['sender_name']);
                foreach ($addressdata['recipients'] as $recipient) {
                    $message->to($recipient['address'], $recipient['name']);
                }
                $message->subject('Repo: ' . $postdata['repository']['name'] . ' updated');
            });
        }

        return Response::json(true);
    }


    /**
     * Make sure we're comparing like for like IP address formats.
     * Since IPv6 can be supplied in short hand or long hand formats.
     *
     * e.g. ::1 is equalvent to 0000:0000:0000:0000:0000:0000:0000:0001
     *
     * @param  string $ip   Input IP address to be formatted
     * @return string   Formatted IP address
     */
    private function formatIPAddress(string $ip)
    {
        return inet_ntop(inet_pton($ip));
    }
}
