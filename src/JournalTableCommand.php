<?php namespace Lnk\Journaling;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

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
     * Create a new liqpay table command instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
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

        $this->call('dump-autoload');
    }

    /**
     * Create a base migration file for the session.
     *
     * @return string
     */
    protected function createBaseMigration()
    {
        $name = 'create_journaling_table';

        $path = $this->laravel['path'].'/database/migrations';

        return $this->laravel['migration.creator']->create($name, $path);
    }

}