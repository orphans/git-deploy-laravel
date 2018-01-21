# Deployments Laravel projects using Git webhooks

git-deploy-laravel assists deployment by receiving a push event message from your repository's server and automatically pulling project code.

This should work out-of-the-box with Laravel 5.x using with webhooks from GitHub and GitLab servers.

This is an internal tool to help with our common workflow pattern but please feel free to borrow, change and improve.

## Installation


### Step 1

Add the following to your `composer.json` file then update your composer as normal:

    {
        "require" : {
            "orphans/git-deploy-laravel" : "dev-master"
        }
    }

Or run:

    composer require orphans/git-deploy-laravel

### Step 2

Add the following line to you providers in `config/app.php`:

    Orphans\GitDeploy\GitDeployServiceProvider::class,
    
** New in Laravel 5.5 - Service provide should be automatically detected without the above change **

### Step 3

Add the _/git-deploy_ route to CSRF exceptions so your repo's host can send messages to your project.


In file in `app/Http/Middleware/VerifyCsrfToken.php` add:

    protected $except = [
        'git-deploy',
    ];

## Usage

Add a webhook for http://your.website.url/git-deploy to your project in GitHub/GitLab and this package will take care of the rest. The webhook should fire on push-events.

Your website will automatically receive POST messages from the repo manager and perform a Git pull.

## Configuration

In most cases the package will find the correct Git repository and Git executable but we advise publishing our config anyway because it will let you enable extra security options and email notifications.

To add custom configuration run:

    php artisan vendor:publish --provider="Orphans\GitDeploy\GitDeployServiceProvider"

Then edit `/config/gitdeploy.php` to suit your needs.

```php
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

];

```

## Future Plans

* Branch management (i.e. only tigger on changes to active branch).
* Email report on code conflicts that prevent a pull.
