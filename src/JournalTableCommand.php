<?php namespace Lnk\Journaling;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Composer;

class JournalTableCommand extends Command
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
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * @var \Illuminate\Foundation\Composer
     */
    protected $composer;

    /**
     * Create a new session table command instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @param  \Illuminate\Foundation\Composer  $composer
     * @return void
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
     * @return void
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

        $path = $this->laravel['path.base'].'/database/migrations';

        return $this->laravel['migration.creator']->create($name, $path);
    }

}