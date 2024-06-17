<?php

namespace Database\Seeders;

use App\Constants\RoleName;
use App\Models\AcademicPeriod;
use App\Models\Contract;
use App\Models\JobDefinition;
use App\Models\User;
use App\Models\WorkerContract;
use Faker\Generator;
use Illuminate\Container\Container;
use Illuminate\Database\Seeder;

class ContractSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Container::getInstance()->make(Generator::class);

        $jobs = [0, app()->environment('testing') ? 5 : 6];
        //First 10 users always have some contracts
        $usersWithJobs = [10, app()->environment('testing') ? 20 : 100];

        $user = 0;

        foreach (JobDefinition::published()
                        //first users have the maximum of contracts
            ->orderBy('id')
            ->limit($faker->numberBetween($user++ < $usersWithJobs[0] ? $jobs[1] : $jobs[0], $jobs[1]))
            ->get() as $job) {
            $currentPeriod = AcademicPeriod::current(false);

            $past = $faker->boolean(30);
            $startMax = $past ? now()->toImmutable()->subDays(2) : $currentPeriod->end->toImmutable()->subDay();

            $start = $faker->dateTimeBetween($currentPeriod->start, $startMax);

            $end = $faker->dateTimeBetween($start, $past ?
                today()->toImmutable()->subDay()
                : $currentPeriod->end);

            $workersQuery = User::role(RoleName::STUDENT)
                ->orderBy('id')
                ->limit($faker->numberBetween($usersWithJobs[0], $usersWithJobs[1]))
                ->whereHas('groupMembers.group.academicPeriod',
                    fn ($q) => $q->where(tbl(AcademicPeriod::class).'.id', '=', AcademicPeriod::current()));

            //$sql = $workersQuery->getQuery()->toSql();
            $workers = $workersQuery->get();
            $evaluatedCount = 0; // force at least 1 evaluated contract...
            foreach ($workers as $worker) {
                $client = $job->providers[0];

                /* @var $contract Contract */
                $contract = Contract::make();
                $contract->start = $start;
                $contract->end = $end;
                $contract->jobDefinition()->associate($job->id);

                $contract->save();
                $contract->clients()->attach($client->id);
                $contract->workers()->attach($worker->groupMember()->id); //set worker

                if ($end < now() || $evaluatedCount == 0) {
                    $success = $faker->boolean(80);
                    $comment = $success ? null : 'Autonomie, respect des délais, structure du code';
                    $contract->workers()->where('user_id', '=', $worker->id)->firstOrFail()->pivot->evaluate($success, $comment);
                    $evaluatedCount++;
                }

                /* @var $workerContract WorkerContract */
                $workerContract = $contract->workerContract($worker->groupMember())->firstOrFail();
                $workerContract->name = '';
                $workerContract->allocated_time = $job->allocated_time;
                $workerContract->save();

                if ($job->one_shot) {
                    //Only 1 worker for 1 shots
                    break;
                }

            }
        }

    }
}
