<?php namespace DKulyk\Eloquent;

use Illuminate\Foundation\Application;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    protected function configPath()
    {
        return __DIR__.'/../../../config/eloquent-extra.php';
    }

    public function register()
    {
        $this->mergeConfigFrom($this->configPath(), 'eloquent-extra');

        $this->app->bind(
            'command.eloquent-extra.logging-table', function (Application $app) {
            return new Logging\TableCommand(
                $app->make('migration.creator'),
                $app->make('composer')
            );
        }, true
        );

        $this->commands('command.eloquent-extra.logging-table');
    }

    public function boot()
    {
        $configPath = __DIR__.'/../config/eloquent-extra.php';
        $this->publishes([$configPath => $this->app->make('path.config').'/eloquent-extra.php'], 'config');
    }
}