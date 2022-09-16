<?php

namespace App\Console\Commands;

use App\Mail\EvaluationChanged;
use App\Models\WorkerContractEvaluationLog;
use App\Models\GroupMember;
use App\Models\User;
use Illuminate\Console\Command;
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
     *
     * @return int
     */
    public function handle()
    {

        //find evaluation not yet reported
        $logs = WorkerContractEvaluationLog::query()
            ->whereNull('reported_at')
            ->with('contract.workers.group.groupName')
            ->with('contract.clients')
            ->with('contract.jobDefinition')
            ->with('contract.workersContracts.groupMember')
            ->get();

        //Group by client
        $contractsPerClient=[];
        foreach($logs as $log)
        {
            /* @var $log WorkerContractEvaluationLog */
            $clients = $log->contract->clients->pluck('email')->implode(',');

            if(!array_key_exists($clients,$contractsPerClient))
            {
                $contractsPerClient[$clients]=[];
            }

            $contractsPerClient[$clients][]=$log;

        }

        foreach ($contractsPerClient as $clientEmails=>$logs)
        {
            $informations = [];
            foreach ($logs as $log)
            {
                /* @var $log WorkerContractEvaluationLog */

                foreach ($log->contract->workersContracts as $workerContract)
                {
                    /* @var $log WorkerContractEvaluationLog */
                    /* @var $worker User */
                    /* @var $workerContract \App\Models\WorkerContract */

                    $worker = $workerContract->groupMember->user;
                    $group = $workerContract->groupMember->group->groupName->name;

                    $informations[]=[
                        'group'=>$group,
                        'name'=>$worker->getFirstnameL(),
                        'log'=>$log,
                        'job'=>Str::limit($log->contract->jobDefinition->title,10),
                    ];
                }

            }

            $sortedInfo = collect($informations)->sortBy([['group','asc'],['name','asc']]);

            Mail::to(explode(',',$clientEmails))
                ->send(new EvaluationChanged($sortedInfo->toArray()));

            //Wait for mail to be sent before marked as reported...
            foreach ($logs as $log)
            {
                $log->reported_at = now();
                $log->update();
            }
        }

        return 0;
    }
}