<?php

namespace App\Console\Commands;

use App\Imports\UsersImport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:sync {input : source file (csv,excel,libreoffice)} {--c|commit : to persist changes} {--f|force : do not ask confirmation for commit}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync users (create/update) from excel/csv to db:

    Expected headers are: firstname,lastname,email,roles

    Supported formats are described in third-party library https://docs.laravel-excel.com/3.1/imports/import-formats.html';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $input = $this->argument('input');
        $commit = $this->option('commit');

        if(!file_exists($input))
        {
            $this->error('File does not exist: '.$input);
            return 2;
        }


        //Courtesy of https://stackoverflow.com/questions/22906844/laravel-using-try-catch-with-dbtransaction
        DB::beginTransaction();

        try {

            $this->output->title('Starting import');
            $import = new UsersImport();
            $import->withOutput($this->output)->import($input);

            //Stats
            $this->table(['Added','Updated'],[[sizeof($import->added),sizeof($import->updated)]]);

            if($this->output->isVerbose())
            {
                $this->output->section('Added');
                $this->info(implode(',',$import->added));

                $this->output->section('Updated');
                $this->warn(implode(',',$import->updated));
            }

            if($commit && (
                $this->option('force') ||
                $this->confirm('Commit ?')))
            {
                DB::commit();
                $this->output->success('Operations committed');
            }
            else
            {
                $this->rollback();
            }

            return 0;

        }
        catch (\Exception $e)
        {
            $this->rollback();
            $this->error($e);
            return 1;
        }


    }

    protected function rollback()
    {
        DB::rollback();
        $this->warn('Operations rolled back');
    }
}
