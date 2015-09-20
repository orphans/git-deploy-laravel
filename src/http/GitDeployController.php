<?php

namespace Orphans\GitDeploy\Http;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
// use App\Http\Requests;

class GitDeployController extends Controller
{
    public function gitHook(Request $request) {

		// Limit to known servers
		if (!empty(config('gitdeploy.allowed_sources')) && !in_array($_SERVER['REMOTE_ADDR'], config('gitdeploy.allowed_sources'))) {
			return Response::json([
				'success' => false,
				'message' => 'Request must come from an approved IP',
			]);
		}

		// Collect the posted data
		$postdata = json_decode($request->getContent(), TRUE);
		if (empty($postdata)) {
			return Response::json([
				'success' => false,
				'message' => 'Web hook data does not look valid',
			]);
		}

		// Check the config's directory
		$repo_dir = config('gitdeploy.repo_path');
		if (!empty($repo_dir) && !file_exists($repo_dir.'/.git/config')) {
			return Response::json([
				'success' => false,
				'message' => 'Invalid repo path in config',
			]);
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
			return Response::json([
				'success' => false,
				'message' => 'Could not determine the repo path',
			]);
		}

		// Do the pull
		$cmd = 'git -C '.escapeshellarg($repo_dir).' pull origin master 2>&1';
		$server_response = [
			'cmd' => $cmd,
			'user' => shell_exec('whoami'),
			'response' => shell_exec($cmd),
		];

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

			\Mail::send('gitdeploy::email', [ 'server' => $server_response, 'git' => $postdata ], function($message) use ($postdata, $addressdata) {
				$message->sender($addressdata['sender_address'], $addressdata['sender_name']);
				foreach ($addressdata['recipients'] as $recipient) {
					$message->to($recipient['address'], $recipient['name']);
				}
				$message->subject('Repo: ' . $postdata['repository']['name'] . ' updated');
			});

		}

		return Response::json(true);

	}
}
