<?php

test('Client load percentage is accurate and check involved groupNames', function () {
    //Arrange
    $this->seed(\Database\Seeders\AcademicPeriodSeeder::class);
    /* @var $client \App\Models\User */
    $client = $this->CreateUser(roles: \App\Constants\RoleName::TEACHER, be: false);

    //JobSeeder needs some clients
    $this->seed(\Database\Seeders\GroupSeeder::class);
    for ($i = 0; $i < 10; $i++) {
        $otherClient = $this->CreateUser(roles: \App\Constants\RoleName::TEACHER, be: false);
    }
    $this->seed(\Database\Seeders\JobSeeder::class);
    $job = \App\Models\JobDefinition::firstOrFail();

    $employeesCount = 10;

    for ($i = 0; $i < $employeesCount; $i++) {
        $users[] = $this->CreateUser(roles: \App\Constants\RoleName::STUDENT, be: false);
    }

    $i = 0;
    foreach ($users as $user) {
        $contract = \App\Models\Contract::make([
            'start' => today()->subWeek(),
            'end' => today()->addWeek()]);
        $contract->job_definition_id = $job->id;
        $contract->save();

        $contract->clients()->attach($i < 3 ? $client->id : $otherClient->id);
        $contract->workers()->attach($user->groupMember()->id);

        $i++;
    }

    //Act
    $load = $client->getClientLoad(\App\Models\AcademicPeriod::current());

    //Assert
    $this->assertEquals(['percentage' => 30, 'mine' => 3, 'total' => $employeesCount], $load);

    $this->assertEquals(1, $client->involvedGroupNames(\App\Models\AcademicPeriod::current())->count());
});
