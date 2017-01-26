<?php

namespace Orphans\GitDeploy;

use Illuminate\Support\ServiceProvider;

class GitDeployServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $source = realpath(__DIR__.'/../config/gitdeploy.php');
        $this->publishes([$source => config_path('gitdeploy.php')]);
        $this->loadViewsFrom(__DIR__.'/views', 'gitdeploy');
        require __DIR__.'/http/routes.php';
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $source = realpath(__DIR__.'/../config/gitdeploy.php');
        $this->mergeConfigFrom($source, 'gitdeploy');
        $this->app->bind('git_deploy', function ($app) {
            return new GitDeploy;
        });
    }
}
