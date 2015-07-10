<?php namespace Lnk\Journaling;


class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $this->app->bind('command.journaling.database', function ($app) {
            return new JournalTableCommand($app['files']);
        }, true);

        $this->commands('command.journaling.database');
    }
}