# Deployment of Laravel projects using Git webhooks

git-deploy-laravel allow for automated deployment using webhook requests from your repository's server, and automatically pulling project code using the local Git binary.

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

### Step 4 Optional
In case you need additional action after a successful commit, you can add you own Event Listener.
For example you can write your own update script to run migrations etc.

**1)**  Create a Listener to perform action when a git deployment is done.
Open the `App/Listeners` directory (or create it if it doesn't exist). Now create a new file and call it `GitDeployedListener.php`. Paste in this code:

```php
<?php

namespace App\Listeners;

use \Orphans\GitDeploy\Events\GitDeployed;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;

class GitDeployedListener implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        // Here you can setup something
    }

    /**
     * Handle the event.
     *
     * @param  ReactionAdded  $event
     * @return void
     */
    public function handle(GitDeployed $gitDeployed)
    {
        // Do some magic with event data $gitDeployed contains the commits

    }
}

```
As you can see, it's a normal event listener. You might notice that the listener `implements ShouldQueue` — it's useful because our app must respond fast. If you want some long-running stuff in your event listener, you need to configure a queue.

**2)** Now we add this listener to `/App/Providers/EventServiceProvider.php` like any other event listener:
```php
// ...

protected $listen = [

        // ...

       \Orphans\GitDeploy\Events\GitDeployed::class => [
            \App\Listeners\GitDeployedListener::class
        ]
    ];

// ...
```


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

];

```

## Future Plans

* Email report on code conflicts that prevent a pull
* Support for performing `composer install` after deployment
* Support for restarting laravel queues after deployment with `artisan queue:restart`
* Support for running custom artisan commands after successful pulls
