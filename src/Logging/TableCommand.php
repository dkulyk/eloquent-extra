<?php namespace DKulyk\Eloquent\Logging;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;

class TableCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'journaling:table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a migration for the journaling table';

    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * @var Composer
     */
    protected $composer;

    /**
     * Create a new session table command instance.
     *
     * @param  Filesystem $files
     * @param  Composer   $composer
     */
    public function __construct(Filesystem $files, Composer $composer)
    {
        parent::__construct();

        $this->files = $files;
        $this->composer = $composer;
    }

    /**
     * Execute the console command.
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function fire()
    {
        $fullPath = $this->createBaseMigration();

        $this->files->put($fullPath, $this->files->get(__DIR__.'/stubs/database.stub'));

        $this->info('Migration created successfully!');

        $this->composer->dumpAutoloads();
    }

    /**
     * Create a base migration file for the session.
     *
     * @return string
     */
    protected function createBaseMigration()
    {
        $name = 'create_journaling_table';

        $path = $this->laravel->make('path.base').'/database/migrations';

        return $this->laravel->make('migration.creator')->create($name, $path);
    }

}