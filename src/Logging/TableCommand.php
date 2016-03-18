<?php

namespace DKulyk\Eloquent\Logging;

use Illuminate\Database\Console\Migrations\BaseCommand;
use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Support\Composer;
use Illuminate\Support\Str;

class TableCommand extends BaseCommand
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature
        = 'eloquent-extra:logging-table
        {--path= : The location where the migration file should be created.}';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'eloquent-extra:logging-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a migration for the logging table';

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
     * Create a new session table command instance.
     *
     * @param MigrationCreator $creator
     * @param Composer         $composer
     */
    public function __construct(MigrationCreator $creator, Composer $composer)
    {
        parent::__construct();

        $this->creator = $creator;
        $this->composer = $composer;
    }

    /**
     * Execute the console command.
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function fire()
    {
        $files = $this->creator->getFilesystem();

        $log = new LoggingModel();
        $table = $log->getTable();
        $name = 'create_'.Str::snake($table).'_table';

        $path = $this->creator->create($name, $this->getMigrationPath());

        $file = pathinfo($path, PATHINFO_FILENAME);

        $stub = $files->get(__DIR__.'/stubs/database.stub');

        $files->put($path, $this->creator->populateStub($name, $stub, $table));

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
