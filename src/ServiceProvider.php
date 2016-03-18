<?php

namespace DKulyk\Eloquent;

use DKulyk\Eloquent\Propertier\Field;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Foundation\Application;
use DKulyk\Eloquent\Propertier\Manager;
use InvalidArgumentException;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;
    
    protected function configPath()
    {
        return __DIR__.'/../config/eloquent-extra.php';
    }

    /**
     * Register the service provider.
     *
     * @throws InvalidArgumentException
     */
    public function register()
    {
        $this->mergeConfigFrom($this->configPath(), 'eloquent-extra');

        $this->app->bind('dkulyk.propertier', function () {
            return new Manager(
                $this->app->make(CacheRepository::class),
                $this->app->make(ConfigRepository::class)
            );
        }, true);

        Field::deleted(function (Field $eloquent) {
            $this->app->make('dkulyk.propertier')->removeField($eloquent);
        });

        $this->registerCommands();
    }

    public function registerCommands()
    {
        $this->app->bind(
            'command.eloquent-extra.logging-table',
            function (Application $app) {
                return new Logging\TableCommand(
                    $app->make('migration.creator'),
                    $app->make('composer')
                );
            }, true
        );

        $this->app->bind(
            'command.eloquent-extra.properties-table',
            function (Application $app) {
                return new Propertier\MakeMigrationCommand(
                    $app->make('migration.creator'),
                    $app->make('composer')
                );
            }, true
        );

        $this->commands('command.eloquent-extra.logging-table');
        $this->commands('command.eloquent-extra.properties-table');
    }

    /**
     * Boot service provider
     */
    public function boot()
    {
        $this->publishes([$this->configPath() => $this->app->make('path.config').'/eloquent-extra.php'], 'config');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['dkulyk.propertier'];
    }
}
