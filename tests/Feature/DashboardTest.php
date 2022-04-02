<?php

use App\Models\User;
use Database\Seeders\PermissionV1Seeder;
use Database\Seeders\UserV1Seeder;
use Illuminate\Testing\TestResponse;

beforeEach(function()
{
    $this->seed(UserV1Seeder::class);
});

test('Prof can see FAQ tool and url shortener', function () {
    //Given
    $prof = $this->CreateUser(roles: 'prof');

    //When
    $response = $this->get('/dashboard');

    //Then
    assertSeeAll($response);

});

test('Root can see FAQ tool and url shortener', function () {
    //Given
    $prof = $this->CreateUser(roles: 'root');

    //When
    $response = $this->get('/dashboard');

    //Then
    assertSeeAll($response);

});

function assertSeeAll(TestResponse $response)
{
    $response->assertSeeText("dis.section-inf.ch");
    $response->assertSeeText("ici.section-inf.ch");
}

test('Eleve cannot see FAQ tool/url shortener but git', function () {
    //Given
    $eleve = $this->CreateUser(roles:'eleve');

    //When
    $response = $this->get('/dashboard');

    //Then
    $response->assertDontSeeText("dis.section-inf.ch");
    $response->assertDontSeeText("ici.section-inf.ch");
    $response->assertSeeText("git.section-inf.ch");
});
