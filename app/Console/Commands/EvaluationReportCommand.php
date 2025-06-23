<?php

namespace App\Console\Commands;

use App\Mail\EvaluationChanged;
use App\Models\User;
use App\Models\WorkerContract;
use App\Models\WorkerContractEvaluationLog;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EvaluationReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:evaluation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check evaluation log for any changes to be reported by email';

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

        //find evaluation not yet reported
        $logs = WorkerContractEvaluationLog::query()
            ->whereNull('reported_at')
            ->with([
                'contract.workers.group.groupName',
                'contract.clients',
                'contract.jobDefinition',
                //Get also trashed if race condition upon student quitting... and evaluation (they are not  cleaned as auto trigger...)
                'contract.workersContracts.groupMember' => function ($query) {
                    $query->withTrashed();//in case a student stops abruptly his formation and there is an eval...
                },
                'contract.workersContracts.groupMember.user' => function ($query) {
                    $query->withTrashed();
                }])
            ->get();

        //Group by client
        $contractsPerClient = [];
        foreach ($logs as $log) {
            //Contract has most probably been deleted, lets update the status without notification
            if ($log->contract === null) {
                $dummyDate = Carbon::now();
                $dummyDate->setHour(12)->setMinute(12)->setSecond(12);
                $log->reported_at = $dummyDate;
                $log->save();

                continue;
            }

            /* @var $log WorkerContractEvaluationLog */
            foreach ($log->contract->clients->pluck('email') as $client) {
                if (!array_key_exists($client, $contractsPerClient)) {
                    $contractsPerClient[$client] = [];
                }

                $contractsPerClient[$client][] = $log;
            }

        }

        foreach ($contractsPerClient as $clientEmail => $logs) {
            $informations = [];
            foreach ($logs as $log) {
                /* @var $log WorkerContractEvaluationLog */

                foreach ($log->contract->workersContracts as $workerContract) {
                    /* @var $log WorkerContractEvaluationLog */
                    /* @var $worker User */
                    /* @var $workerContract WorkerContract */

                    $worker = $workerContract->groupMember->user;
                    $group = $workerContract->groupMember->group->groupName->name;

                    $informations[] = [
                        'group' => $group,
                        'name' => $worker->getFirstnameL(),
                        'log' => $log,
                        'job' => Str::limit($log->contract->jobDefinition->title, 10),
                    ];
                }

            }

            $sortedInfo = collect($informations)->sortBy([['group', 'asc'], ['name', 'asc']]);

            Mail::to($clientEmail)
                ->send(new EvaluationChanged($sortedInfo->toArray()));

            Log::info('Evaluation report [' . count($informations) . ' update(s)] sent to ' . $clientEmail . ']');

            //Wait for mail to be sent before marked as reported...
            foreach ($logs as $log) {
                $log->reported_at = now();
                $log->update();
            }
        }

        Log::info('Evaluation report finished, handled ' . count($logs) . ' evaluation logs entries');

        return 0;
    }
}
