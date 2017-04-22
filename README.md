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

Then edit `/config/gitdeploy.php`, which has been well commented.

## Future Plans

* Branch management (i.e. only tigger on changes to active branch).
* Email report on code conflicts that prevent a pull.
