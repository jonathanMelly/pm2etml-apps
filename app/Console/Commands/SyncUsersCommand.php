<?php

namespace App\Console\Commands;

use App\Imports\UsersSheetImport;
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

    Expected headers are: firstname,lastname,email       ,login        ,roles,groups,period
    Example:              bob      ,marley  ,b@eduvaud.ch,px@eduvaud.ch,eleve,cin1a ,01.08.2022

    BEWARE: login is mandatory (it used as ID)
    WARNING: if roles is empty, user won’t have any role
    INFO: if period is empty, it will get the current one

    Supported formats are described in third-party library https://docs.laravel-excel.com/3.1/imports/import-formats.html';

    public const RESULT_HEADERS = ['+ ADDED', '- REMOVED', '# UPDATED', '= SAME', '* RESTORED', '! WARNING', '/!\\ ERROR'];

    //For verbose output infos
    public const OUTPUT_APPEND = 0;

    public const OUTPUT_NEW_LINE = 1;

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
     */
    public function handle(): int
    {
        $input = $this->argument('input');
        $commit = $this->option('commit');

        if (! file_exists($input)) {
            $this->error('File does not exist: '.$input);

            return 2;
        }

        //Courtesy of https://stackoverflow.com/questions/22906844/laravel-using-try-catch-with-dbtransaction
        DB::beginTransaction();

        try {

            $this->output->title('Starting import');
            $import = new UsersSheetImport();
            //Excel::import($import, $input);

            $import->withOutput($this->output)->import($input);

            $import = $import->firstSheetImport;
            $errors = [];
            foreach ($import->failures() as $failure) {
                $row = $failure->row(); // row that went wrong
                //$attribute = $failure->attribute(); // either heading key (if using heading row concern) or column index
                $description = implode(',', $failure->errors()); // Actual error messages from Laravel validator
                //$value =implode(',',$failure->values()); // The values of the row that has failed.

                $errors[] = "Line $row : $description";
            }

            //Stats
            $addedCount = count($import->added);
            $updatedCount = count($import->updated);
            $deleted = count($import->deleted);
            $sameCount = count($import->same);
            $restoredCount = count($import->restored);
            $warningCount = count($import->warning);
            $errorsCount = count($errors);

            $this->table(self::RESULT_HEADERS, [[
                $addedCount,
                $deleted,
                $updatedCount,
                $sameCount,
                $restoredCount,
                $warningCount,
                $errorsCount]]);

            if ($this->output->isVerbose()) {
                $this->newLine();
                $i = 0;
                foreach ([
                    ['info', $import->added, $addedCount, self::OUTPUT_NEW_LINE],
                    ['info', $import->deleted, $deleted, self::OUTPUT_APPEND],
                    ['info', $import->updated, $updatedCount, self::OUTPUT_NEW_LINE],
                    ['', $import->same, $sameCount, self::OUTPUT_APPEND],
                    ['info', $import->restored, $restoredCount, self::OUTPUT_APPEND],
                    ['comment', $import->warning, $warningCount, self::OUTPUT_NEW_LINE],
                    ['error', $errors, $errorsCount, self::OUTPUT_NEW_LINE],
                ] as $report) {
                    $data = $report[1];
                    $style = $report[0];

                    if (count($data) > 0) {
                        $this->line(self::RESULT_HEADERS[$i].'['.$report[2].']', $style);
                        $data = collect($data)->sort();
                        if ($report[3] == self::OUTPUT_NEW_LINE) {
                            $data->each(fn ($el) => $this->line('    '.$el));
                        } else {
                            $this->line($data->implode(','));
                        }

                        $this->newLine();
                    }
                    $i++;
                }

            }

            if ($commit && (
                $this->option('force') ||
                $this->confirm('Commit ?'))) {
                DB::commit();
                $this->output->success('Operations committed');

                return 0;
            } else {
                $this->rollback();

                return 3;
            }

        } catch (\Exception $e) {
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
