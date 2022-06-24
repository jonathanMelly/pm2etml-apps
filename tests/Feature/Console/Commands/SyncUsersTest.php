<?php


use Database\Seeders\AcademicPeriodSeeder;

test('Test valid teachers and students import', function() {
    /* @var $this \Tests\TestCase */

    //GIVEN
    $this->multiSeed(
        AcademicPeriodSeeder::class,
    );
    $bob = \App\Models\User::factory()->create();
    $bob->username='px5@eduvaud.ch';
    $bob->firstname='before';
    $bob->save();

    $same = \App\Models\User::factory()->create();
    $same->username='same@eduvaud.ch';
    $same->email='samemail@eduvaud.ch';
    $same->firstname='samefn';
    $same->lastname='sameln';
    $same->assignRole(\App\Constants\RoleName::STUDENT);
    $same->joinGroup(\App\Models\AcademicPeriod::forDate(\Carbon\Carbon::create(2022,8))->firstOrFail()->id,'cin2b',2);
    $same->save();

    $existing = \App\Models\User::factory()->create();
    $existing->username='login@eduvaud.ch';
    $existing->email='existing@eduvaud.ch';
    $existing->firstname='existingfn';
    $existing->lastname='existingln';
    $existing->assignRole(\App\Constants\RoleName::TEACHER);
    $existing->joinGroup(\App\Models\AcademicPeriod::forDate(\Carbon\Carbon::create(2022,8))->firstOrFail()->id,'cin2b',2);
    $existing->save();

    //WHEN
    $this->artisan('users:sync',[
        'input' => base_path('tests/data/users-import.xlsx'),
        '--commit'=>null,
        '-v'=>null])
        ->expectsConfirmation('Commit ?', 'yes')
        ->expectsOutputToContain('px5@eduvaud.ch')
        ->expectsOutputToContain('py6@eduvaud.ch')
        ->expectsOutputToContain('Le champ login est obligatoire')
        ->expectsTable(\App\Console\Commands\SyncUsersCommand::RESULT_HEADERS,[['3','7','1','3']])
        ->assertExitCode(0);

    //THEN
    $bob = \App\Models\User::where('username','=','px5@eduvaud.ch')->firstOrFail();
    $this->assertEquals('bob',$bob->firstname);
    $this->assertTrue($bob->hasRole([\App\Constants\RoleName::TEACHER,\App\Constants\RoleName::ADMIN]));

    $students = \App\Models\User::role(\App\Constants\RoleName::STUDENT)->get();
    $this->assertCount(3,$students);

    foreach( [
        ['email'=>'studfnln@eduvaud.ch','roles'=>[\App\Constants\RoleName::STUDENT],'class'=>'cin1a','year'=>2022],
        ['email'=>'studfnln@eduvaud.ch','roles'=>[\App\Constants\RoleName::STUDENT],'class'=>'cin2a','year'=>2023],
        ['email'=>'stud2@eduvaud.ch','roles'=>[\App\Constants\RoleName::STUDENT],'class'=>'fin1','year'=>2021],
        ['email'=>'stud2@eduvaud.ch','roles'=>[\App\Constants\RoleName::STUDENT],'class'=>'fin1','year'=>2022],
        ['email'=>'fr@eduvaud.ch','roles'=>[\App\Constants\RoleName::PRINCIPAL,\App\Constants\RoleName::ADMIN,\App\Constants\RoleName::DEAN],'class'=>null,'year'=>null],
        ['email'=>'bob@eduvaud.ch','roles'=>[\App\Constants\RoleName::TEACHER,\App\Constants\RoleName::ADMIN],'class'=>'fin1','year'=>2022],
        ['email'=>'samemail@eduvaud.ch','roles'=>[\App\Constants\RoleName::STUDENT],'class'=>'cin2b','year'=>2022],
        ['email'=>'modif@eduvaud.ch','roles'=>[\App\Constants\RoleName::TEACHER],'class'=>'fin2','year'=>2022]

             ] as $user) {
        /* @var $dbStudent \App\Models\User */
        $dbStudent = \App\Models\User::role($user['roles'])->where('email', '=', $user['email'])->firstOrFail();
        if ($user['year'] !== null && $user['class']!==null)
        {
            $period = \App\Models\AcademicPeriod::forDate(\Carbon\Carbon::createMidnightDate($user['year'],8,1))->firstOrFail();
            $groupMember = $dbStudent->groupMember($period->id,true);
            $this->assertNotNull($groupMember,'Group member entry not found for $user '.$user['email'].' and class '.$user['class']. ' in period '.$period);
            \PHPUnit\Framework\assertEquals(
                $user['class'],
                $groupMember->group->groupName->name,
                'Student '.$user['email']. ' is not member of class '.$user['class'].' for 1.8.'.$user['year'].' as expected');
        }

    }

    //relaunch and only updates should have been detected + validate rollback by default
    $this->artisan('users:sync',['input' => base_path('tests/data/users-import.xlsx')])
        ->expectsTable(\App\Console\Commands\SyncUsersCommand::RESULT_HEADERS,[['0','2','9','3']])
        ->assertExitCode(3);//3=rollback*/


});

test('Test non existing file import', function () {
    /* @var $this \Tests\TestCase */

    $this->artisan('users:sync',['input' => 'notexist'])
        ->assertExitCode(2);

});
