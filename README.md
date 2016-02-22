# git-deploy-laravel

Helps automate the deployment of Laravel projects onto servers by utilising webhooks.

This should work out-of-the-box with webhooks from GitHub and GitLab servers.

**Only tested with Laravel 5.1.1 and GitLab 7.14**

This is an internal tool to help with our common workflow pattern but please feel free to borrow, change and improve.

## Installation

Add the following to your `composer.json` file:

    {
        "require" : {
            "orphans/git-deploy-laravel" : "dev-master"
        }
    }

Then install/update your composer project as normal.

Add the following line to you providers in `config/app.php`:

    Orphans\GitDeploy\GitDeployServiceProvider::class,

And the `git-deploy` route to the `$except` variable in your route CRSF middleware file in `app/Http/Middleware/VerifyCsrfToken.php`:

    protected $except = [
        'git-deploy',
    ];

## Usage

Add a webhook for http://your.website.url/git-deploy to your project in GitHub/GitLab and this package will take care of the rest.

It will automatically receive POST messages from the repo manager and perform a Git pull.

## Configuration

There is (potentially) important configuration in the package's config file, for things like the email notifications and your repository's root path on the file system.

> Note that this tool tries to automatically determine the repository's root path and that should work suffiently in most cases.

To add custom configuration run:

    php artisan vendor:publish

Then edit `/config/gitdeploy.php` which has been well commented.

## Future Plans

* Testing on GitHub and different versions of Laravel & GitLab.
* Branch management (i.e. only tigger on changes to active branch).
* Email report on code conflicts that prevent a pull.
