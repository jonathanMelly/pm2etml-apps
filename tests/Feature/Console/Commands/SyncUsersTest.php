<?php

test('Test valid users import', function () {
    /* @var $this \Tests\TestCase */

    //GIVEN
    $bob = \App\Models\User::factory()->create();
    $bob->email='bob@eduvaud.ch';
    $bob->firstname='before';
    $bob->save();

    //WHEN
    $this->artisan('users:sync',[
        'input' => base_path('tests/data/users-import.xlsx'),
        '--commit'=>null,
        '-v'=>null])
        ->expectsConfirmation('Commit ?', 'yes')
        ->expectsOutputToContain('bob@eduvaud.ch')
        ->expectsOutputToContain('fr@eduvaud.ch')
        ->expectsTable(['Added','Updated'],[['1','1']])
        ->assertExitCode(0);

    //THEN
    $bob = \App\Models\User::where('email','=','bob@eduvaud.ch')->firstOrFail();
    $this->assertEquals('bob',$bob->firstname);
    $this->assertTrue($bob->hasRole([\App\Constants\RoleName::TEACHER,\App\Constants\RoleName::ADMIN]));

});

test('Test invalid file import', function () {
    /* @var $this \Tests\TestCase */

    $this->artisan('users:sync',['input' => 'notexist'])
        ->assertExitCode(2);

});
