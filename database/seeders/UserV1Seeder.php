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
        $this->createOrUpdateUser("prof@prof.com","prof@eduvaud.ch","prof","esseur","prof");
        $this->createOrUpdateUser("root@r.com","root@eduvaud.ch","ro","ot","root");
        $this->createOrUpdateUser("mp@mp.com","mp@eduvaud.ch","ro","ot","mp");
    }

    public function createOrUpdateUser($email,$username,$fn,$ln,$role)
    {
        $user = User::where('email',$email)->first();
        if($user ===null) {
            //
            $user = User::create([
                'firstname' => $fn,
                'lastname' => $ln,
                'email' => $email,
                'username' => $username
            ]);
        }

        //reset
        $user->syncRoles([]);

        //fill
        $user->assignRole($role);
    }

}
