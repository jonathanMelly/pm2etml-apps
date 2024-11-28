<?php

use Database\Seeders\AcademicPeriodSeeder;

test('Test valid teachers and students import', function () {
    /* @var $this \Tests\TestCase */

    //GIVEN
    $this->multiSeed(
        AcademicPeriodSeeder::class,
        \Database\Seeders\GroupSeeder::class
    );

    $period = \App\Models\AcademicPeriod::forDate(\Carbon\Carbon::create(2022, 8))->firstOrFail();

    $bob = \App\Models\User::factory()->create();
    $bob->username = 'px5@eduvaud.ch';
    $bob->firstname = 'before';
    $bob->save();

    $same = \App\Models\User::factory()->create();
    $same->username = 'same@eduvaud.ch';
    $same->email = 'samemail@eduvaud.ch';
    $same->firstname = 'samefn';
    $same->lastname = 'sameln';
    $same->assignRole(\App\Constants\RoleName::STUDENT);
    $same->joinGroup($period->id, 'cin2b', 2);
    $same->save();

    $existing = \App\Models\User::factory()->create();
    $existing->username = 'login@eduvaud.ch';
    $existing->email = 'existing@eduvaud.ch';
    $existing->firstname = 'existingfn';
    $existing->lastname = 'existingln';
    $existing->assignRole(\App\Constants\RoleName::TEACHER);
    $existing->joinGroup($period->id, 'cin2b', 2);
    $existing->save();

    $migrator = \App\Models\User::factory()->create();
    $migrator->username = 'migrator@eduvaud.ch';
    $migrator->email = 'migrator@eduvaud.ch';
    $migrator->firstname = 'migra';
    $migrator->lastname = 'tor';
    $migrator->assignRole(\App\Constants\RoleName::STUDENT);
    $migrator->joinGroup($period->id, 'min2', 2);
    $migrator->save();

    $repetor = \App\Models\User::factory()->create();
    $repetor->username = 'rep@eduvaud.ch';
    $repetor->email = 'rep@eduvaud.ch';
    $repetor->firstname = 'repe';
    $repetor->lastname = 'tor';
    $repetor->assignRole(\App\Constants\RoleName::STUDENT);
    $repetor->joinGroup($period->id, 'cin2a', 2);
    $repetor->save();
    $repetor->refresh()->with('groupMembers');

    //create some contracts
    /* @var $c \App\Models\WorkerContract */
    $c = $this->createClientAndJob(1, [$repetor], $period)['workerContracts'][0];
    $c->evaluate(true); //should be later deleted

    //WHEN

    /*    $this->withoutMockingConsoleOutput()->artisan('users:sync',[
            'input' => base_path('tests/data/users-import.xlsx'),
            '-v'=>null]);
        dd(Artisan::output());*/

    $this->artisan('users:sync', [
        'input' => base_path('tests/data/users-import.xlsx'),
        '--commit' => null,
        '-v' => null])
        ->expectsConfirmation('Commit ?', 'yes')
        ->expectsOutputToContain('fr@eduvaud.ch')
        ->expectsOutputToContain('stud2@eduvaud.ch')
        ->expectsOutputToContain('Le champ login est obligatoire')
        ->expectsOutputToContain('ghost ghost<?> marked as deleted but was never added before -> ignoring')
        ->expectsOutputToContain('repe to.<5> : repetition, deleted cids: 1 | wcids:1')
        ->expectsTable(\App\Console\Commands\SyncUsersCommand::RESULT_HEADERS, [[/*+*/ '5', /*-*/ '2', /*#*/ '10', /*=*/ '1', /***/ '1', /*!*/ '3', /*/!\*/ '3']])
        ->assertExitCode(0);

    //THEN
    $bob = \App\Models\User::where('username', '=', 'px5@eduvaud.ch')->firstOrFail();
    $this->assertEquals('bob', $bob->firstname);
    $this->assertTrue($bob->hasRole([\App\Constants\RoleName::TEACHER, \App\Constants\RoleName::ADMIN]));

    $students = \App\Models\User::role(\App\Constants\RoleName::STUDENT)->get();
    $this->assertCount(5, $students);

    foreach ([
        ['email' => 'studfnln@eduvaud.ch', 'roles' => [\App\Constants\RoleName::STUDENT], 'class' => 'cin1a', 'year' => 2022],
        ['email' => 'studfnln@eduvaud.ch', 'roles' => [\App\Constants\RoleName::STUDENT], 'class' => 'cin2a', 'year' => 2023],
        ['email' => 'stud2@eduvaud.ch', 'roles' => [\App\Constants\RoleName::STUDENT], 'class' => 'fin1', 'year' => 2021],
        ['email' => 'stud2@eduvaud.ch', 'roles' => [\App\Constants\RoleName::STUDENT], 'class' => 'fin1', 'year' => 2022],
        ['email' => 'fr@eduvaud.ch', 'roles' => [\App\Constants\RoleName::PRINCIPAL, \App\Constants\RoleName::ADMIN, \App\Constants\RoleName::DEAN], 'class' => null, 'year' => null],
        ['email' => 'bob@eduvaud.ch', 'roles' => [\App\Constants\RoleName::TEACHER, \App\Constants\RoleName::ADMIN], 'class' => 'fin1', 'year' => 2022],
        ['email' => 'samemail@eduvaud.ch', 'roles' => [\App\Constants\RoleName::STUDENT], 'class' => 'cin2b', 'year' => 2022],
        ['email' => 'modif@eduvaud.ch', 'roles' => [\App\Constants\RoleName::TEACHER], 'class' => 'fin2', 'year' => 2022],
        ['email' => 'tbr@eduvaud.ch', 'roles' => [\App\Constants\RoleName::TEACHER], 'class' => 'min1', 'year' => 2022],
        ['email' => 'migrator@eduvaud.ch', 'roles' => [\App\Constants\RoleName::STUDENT], 'class' => 'cin2a', 'year' => 2022],

    ] as $user) {
        /* @var $dbStudent \App\Models\User */
        $dbStudent = \App\Models\User::role($user['roles'])->where('email', '=', $user['email'])->firstOrFail();
        if ($user['year'] !== null && $user['class'] !== null) {
            $period = \App\Models\AcademicPeriod::forDate(\Carbon\Carbon::createMidnightDate($user['year'], 8, 1))->firstOrFail();
            $groupMember = $dbStudent->groupMember($period->id, true);
            $this->assertNotNull($groupMember, 'Group member entry not found for $user '.$user['email'].' and class '.$user['class'].' in period '.$period);
            \PHPUnit\Framework\assertEquals(
                $user['class'],
                $groupMember->group->groupName->name,
                'Student '.$user['email'].' is not member of class '.$user['class'].' for 1.8.'.$user['year'].' as expected');
        }

    }

    $this->assertTrue(\App\Models\User::withTrashed()->where('username', '=', 'ghost@eduvaud.ch')->doesntExist());

    $tbd = \App\Models\User::withTrashed()->where('username', '=', 'tbd@eduvaud.ch')->firstOrFail();
    $this->assertTrue($tbd->trashed());
    $this->assertNull($tbd->groupMember($period->id), 'tbd and his cin1c membership should be trashed');
    $this->assertEquals($tbd->groupMembers()->withTrashed()->firstOrFail()->group->groupName->name, 'cin1c', 'tbd is not found in cin1c 2022-23 trashed group');

    $c->refresh();
    $this->assertNotNull($c->deleted_at);

    /*    $this->withoutMockingConsoleOutput()->artisan('users:sync',[
            'input' => base_path('tests/data/users-import.xlsx'),
            '-v'=>null]);
        dd(Artisan::output());*/

    //relaunch and only updates should have been detected + validate rollback by default
    $this->artisan('users:sync', ['input' => base_path('tests/data/users-import.xlsx')])
        ->expectsTable(\App\Console\Commands\SyncUsersCommand::RESULT_HEADERS, [['0', '2', '4', '12', '2', '1', '3']])
        ->assertExitCode(3); //3=rollback*/

});

test('Test non existing file import', function () {
    /* @var $this \Tests\TestCase */

    $this->artisan('users:sync', ['input' => 'notexist'])
        ->assertExitCode(2);

});
