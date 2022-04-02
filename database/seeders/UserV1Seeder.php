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
        $this->createOrUpdateUser("mp@mp.com","mp@eduvaud.ch","mp","rincipal","mp");
        $this->createOrUpdateUser("padawan@mp.com","padawan@eduvaud.ch","pada","wan","eleve");

        User::factory()->count(30)->create();
    }

    public function createOrUpdateUser($email,$username,$fn,$ln,...$roles)
    {
        $user = User::updateOrCreate([
                'firstname' => $fn,
                'lastname' => $ln,
                'email' => $email,
                'username' => $username
            ]);

        //reset
        $user->syncRoles([]);

        //fill
        $user->assignRole($roles);
    }

}
