<?php namespace DKulyk\Eloquent\Logging;

use Illuminate\Console\Command;
use Illuminate\Database\Console\Migrations\BaseCommand;
use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;

class TableCommand extends BaseCommand
{

    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature
        = 'eloquent-extra:log-table
        {--path= : The location where the migration file should be created.}';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'eloquent-extra:log-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a migration for the journaling table';

    /**
     * The migration creator instance.
     *
     * @var \Illuminate\Database\Migrations\MigrationCreator
     */
    protected $creator;

    /**
     * The Composer instance.
     *
     * @var \Illuminate\Support\Composer
     */
    protected $composer;

    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * Create a new session table command instance.
     *
     * @param MigrationCreator $creator
     * @param Composer         $composer
     * @param Filesystem       $files
     */
    public function __construct(MigrationCreator $creator, Composer $composer, Filesystem $files)
    {
        parent::__construct();

        $this->creator = $creator;
        $this->composer = $composer;
        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function fire()
    {
        $name = 'create_eloquent_log_table';

        $path = $this->creator->create($name, $this->getMigrationPath());

        $file = pathinfo($path, PATHINFO_FILENAME);

        $this->files->put($path, $this->files->get(__DIR__.'/stubs/database.stub'));

        $this->line("<info>Created Migration:</info> $file");

        $this->composer->dumpAutoloads();
    }

    /**
     * Get migration path (either specified by '--path' option or default location).
     *
     * @return string
     */
    protected function getMigrationPath()
    {
        if (!is_null($targetPath = $this->input->getOption('path'))) {
            return $this->laravel->basePath().'/'.$targetPath;
        }

        return parent::getMigrationPath();
    }

}