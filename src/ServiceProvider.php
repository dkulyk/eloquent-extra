<?php namespace Lnk\Journaling;


class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $configPath = __DIR__.'/../config/journaling.php';
        $this->mergeConfigFrom($configPath, 'journaling');

        $this->app->bind('command.journaling.database', function ($app) {
            return new JournalTableCommand($app['files'], $app['composer']);
        }, true);

        $this->commands('command.journaling.database');
    }

    public function boot()
    {
        $configPath = __DIR__.'/../config/journaling.php';
        $this->publishes([$configPath => $this->app->make('path.config').'/journaling.php'], 'config');
    }
}