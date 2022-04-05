<?php

namespace Database\Seeders;

use App\Enums\ContractStatus;
use App\Enums\RoleName;
use App\Models\Contract;
use App\Models\JobDefinition;
use App\Models\User;
use Faker\Generator;
use Illuminate\Container\Container;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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

        $jobs=10;
        $contracts_per_job=[2,5];

        foreach (JobDefinition::published()->limit($jobs)->get() as $job)
        {
            $client = $job->providers[0];
            foreach(User::role(RoleName::STUDENT)
                        ->orderBy('id')
                        ->limit($faker->numberBetween($contracts_per_job[0],$contracts_per_job[1]))
                        ->get()
                    as $worker)
            {
                $contract = Contract::make();
                $contract->start_date = $faker->dateTimeThisMonth;
                $contract->end_date = $faker->dateTimeBetween($contract->start_date,'+3 months');
                $contract->jobDefinition()->associate($job->id);

                $contract->status = ContractStatus::cases()[array_rand(ContractStatus::cases())];

                $contract->save();
                $contract->clients()->attach($client->id);
                $contract->workers()->attach($worker->id);//set worker

            }
        }

    }
}
