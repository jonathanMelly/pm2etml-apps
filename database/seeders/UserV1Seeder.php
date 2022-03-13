<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
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
        $user = User::where('email','jonathan.melly@gmail.com')->first();
        if($user ===null) {
            //
            $user = User::create([
                'firstname' => 'jonathan',
                'lastname' => 'melly',
                'email' => 'jonathan.melly@gmail.com',
                'username' => 'jonathan.melly@eduvaud.ch'
            ]);
        }
        //empty
        $user->syncRoles([]);

        //fill
        $user->assignRole('prof');
    }
}
