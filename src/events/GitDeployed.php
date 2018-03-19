<?php

namespace Orphans\GitDeploy\Events;

use Illuminate\Queue\SerializesModels;

class GitDeployed
{
    use SerializesModels;

    public $commits;

    /**
     * Create a new event instance.
     *
     * @param  Order  $order
     * @return void
     */
    public function __construct($commits)
    {
        $this->commits = $commits;
    }

}