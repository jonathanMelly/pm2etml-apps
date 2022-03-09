<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class UserV1Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $user = User::create([
            'firstname' => 'jonathan',
            'lastname' => 'melly',
            'email' => 'jonathan.melly@gmail.com',
            'username' => 'jonathan.melly@eduvaud.ch'
        ]);
    }
}
