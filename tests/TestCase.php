<?php

class TestCase extends Orchestra\Testbench\TestCase
{
    /**
     * @var \DKulyk\Eloquent\Propertier\Manager
     */
    protected $manager;
    /**
     * Setting up test.
     */
    public function setUp()
    {
        parent::setUp();
        $this->runMigrations();
        $this->withFactories(__DIR__.'/support/factories');
        $this->manager = $this->app->make('dkulyk.propertier');
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testing');
        $app['config']->set(
            'database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]
        );
    }

    /**
     * Get package providers.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            \Illuminate\Validation\ValidationServiceProvider::class,
            \DKulyk\Eloquent\ServiceProvider::class,
        ];
    }

    /**
     * Run database migrations.
     */
    protected function runMigrations()
    {
        // Migrating testing tables
        $this->artisan(
            'migrate', ['--realpath' => realpath(__DIR__.'/support/migrations')]
        );
    }
}
