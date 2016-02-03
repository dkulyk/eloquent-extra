<?php namespace DKulyk\Eloquent;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $configPath = __DIR__.'/../config/eloquent-extra.php';
        $this->mergeConfigFrom($configPath, 'eloquent-extra');

        $this->app->bind('command.eloquent-extra.logging-table', function ($app) {
            return new Logging\TableCommand($app['files'], $app['composer']);
        }, true);

        $this->commands('command.eloquent-extra.logging-table');
    }

    public function boot()
    {
        $configPath = __DIR__.'/../config/eloquent-extra.php';
        $this->publishes([$configPath => $this->app->make('path.config').'/eloquent-extra.php'], 'config');
    }
}