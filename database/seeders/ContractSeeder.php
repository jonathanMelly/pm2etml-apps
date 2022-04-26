<?php

namespace Database\Seeders;

use App\Constants\RoleName;
use App\Models\Contract;
use App\Models\JobDefinition;
use App\Models\User;
use Faker\Generator;
use Illuminate\Container\Container;
use Illuminate\Database\Seeder;

class ContractSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Container::getInstance()->make(Generator::class);

        $jobs = [0,app()->environment('testing')?2:5];
        //First 10 users always have some contracts
        $usersWithJobs = [10, app()->environment('testing')?11:100];

        $user=0;
        foreach (User::role(RoleName::STUDENT)
                     ->orderBy('id')
                     ->limit($faker->numberBetween($usersWithJobs[0], $usersWithJobs[1]))
                     ->get()
                 as $worker)
        {
            foreach (JobDefinition::published()
                        //first users have the maximum of contracts
                         ->limit($faker->numberBetween($user++<$usersWithJobs[0]?$jobs[1]:$jobs[0], $jobs[1]))
                         ->get() as $job)
            {
                $client = $job->providers[0];

                $contract = Contract::make();
                $contract->start = $faker->dateTimeThisMonth;
                $contract->end = $faker->dateTimeBetween($contract->start, '+3 months');
                $contract->jobDefinition()->associate($job->id);

                $contract->save();
                $contract->clients()->attach($client->id);
                $contract->workers()->attach($worker->groupMember()->id);//set worker

            }
        }

    }
}
