<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Email recipients
    |--------------------------------------------------------------------------
    |
    | The email address and name that notification emails will be sent to.
    | Leave the array empty to disable emails.
    |
    | [
    |     ['name' => 'Joe Bloggs', 'address' => 'email@example1.com'],
    |     ['name' => 'Jane Doe', 'address' => 'email@example2.com'],
    |     ...
    | ]
    |
    */

    'email_recipients' => [],

    /*
    |--------------------------------------------------------------------------
    | Email sender
    |--------------------------------------------------------------------------
    |
    | The email address and name that notification emails will be sent from.
    | This will default to the sender in config(mail.from) if left null.
    |
    */

    'email_sender' => ['address' => null, 'name' => null],

    /*
    |--------------------------------------------------------------------------
    | Email template
    |--------------------------------------------------------------------------
    |
    | We have a good email template but if you need to change the view, create your own email template and change it here.
    | Default is 'gitdeploy::email'
    |
    */
    'email_template' => 'gitdeploy::email',
    /*
    |--------------------------------------------------------------------------
    | Repository path
    |--------------------------------------------------------------------------
    |
    | This the root path of the Git repository that will be pulled. If this
    | is left empty the script will try to determine the directory itself
    | but looking for the project's .env file it's nearby .git directory.
    |
    | No trailing slash
    |
    */

    'repo_path' => '',

    /*
    |--------------------------------------------------------------------------
    | Allowed sources
    |--------------------------------------------------------------------------
    |
    | A request will be ignored unless it comes from an IP listed in this
    | array. Leave the array empty to allow all sources.
    |
    | This is useful for a little extra security if you run your own Git
    | repo server.
    |
    | Relies on the REMOTE_ADDR of the connecting client matching a value
    | in the array below. So if using IPv6 on both the server and the
    | notifing git server, then make sure to add it to the array. If your git
    | server listens on IPv4 and IPv6 it would be safest to add both.
    |
    | e.g.
    |
    | 'allowed_sources' => ['192.160.0.1', '::1'],
    |
    */

    'allowed_sources' => [],

    /*
    |--------------------------------------------------------------------------
    | Remote name
    |--------------------------------------------------------------------------
    |
    | The name of the remote repository to pull the changes from
    |
    */

    'remote' => 'origin',

    /*
    |--------------------------------------------------------------------------
    | Git binary path
    |--------------------------------------------------------------------------
    |
    | The full path to the system git binary. e.g. /usr/bin/git
    |
    | Leave blank to let the system detect using the current PATH variable
    |
    */

    'git_path' => '',

    /*
    |--------------------------------------------------------------------------
    | Maintenance mode
    |--------------------------------------------------------------------------
    |
    | Allow the git hook to put the site into maintenance mode before doing
    | the pull from the remote server.
    |
    | After a successful pull the site will be switched back to normal
    | operations. This does leave a possibility of the site remaining in
    | maintenance mode should an error occur during the pull.
    |
    */

    'maintenance_mode' => true,

    /*
    |--------------------------------------------------------------------------
    | Fire Event
    |--------------------------------------------------------------------------
    |
    | Allow the git hook to fire a event "GitDeployed" so that everybody can listen to that event.
    | See readme how to create a nice listener on that.
    |
    */
    'fire_event' => true,

    /*
    |--------------------------------------------------------------------------
    | Secret signature
    |--------------------------------------------------------------------------
    |
    | Allow webhook requests to be signed with a secret signature.
    |
    | If 'secret' is set to true, Gitdeploy will deny requests where the
    | signature does not match. If set to false it will ignore any signature
    | headers it recieves.
    |
    | For Gitlab servers, you probably want the settings below:
    |
    |     'secret_type' => 'plain',
    |     'secret_header' => 'X-Gitlab-Token',
    |
    | For Github, use something like the below (untested):
    |
    |    'secret_type' => 'hmac',
    |    'secret_header' => 'X-Hub-Signature',
    */

    'secret' => false,

    /**
     * plain|hmac
     */
    'secret_type' => 'plain',

    /**
     * X-Gitlab-Token|X-Hub-Signature
     */
    'secret_header' => 'X-Gitlab-Token',

    /**
     * The key you specified in the pushing client
     */
    'secret_key' => '',

    /*
    |--------------------------------------------------------------------------
    | Post Pull Commands
    |--------------------------------------------------------------------------
    |
    | Array of commands to complete after the pull.
    | All commands will be run from the repo path
    |
    | 'commands' => [
    |     'composer install --no-dev --optimize-autoloader',
    |     'php artisan migrate --force'
    |     'npm install --production',
    |     'npm run production',
    |     'php artisan config:cache',
    |     'php artisan route:cache',
    |     'php artisan view:cache',
    | ]
    */

    'commands' => [
    ],
];
