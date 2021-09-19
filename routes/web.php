<?php

use Illuminate\Support\Facades\Route;

Route::post('git-deploy', 'Orphans\GitDeploy\Http\GitDeployController@gitHook');
