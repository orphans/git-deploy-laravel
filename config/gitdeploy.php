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
    |     ['name' => 'Joe Bloggs', 'email' => 'email@example1.com'],
    |     ['name' => 'Jane Doe', 'email' => 'email@example2.com'],
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
    | Email notifications
    |--------------------------------------------------------------------------
    |
    | An email will be sent to each person in this list after each successful
    | update.
    |
    | [
    |     ['name' => 'Joe Bloggs', 'email' => 'email@example1.com'],
    |     ['name' => 'Jane Doe', 'email' => 'email@example2.com'],
    |     ...
    | ]
    |
    */

    'notify' => [],

];
