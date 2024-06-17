<?php

namespace Database\Seeders;

use App\Constants\RoleName;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class UserV1Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $exitCode = Artisan::call('db:seed', [
            '--class' => GroupSeeder::class, // as students mainly exists in real class for 1 year...
            '--force' => true,
            //'-vvv' does not bring more output
        ]);

        if ($exitCode != 0) {
            throw new \InvalidArgumentException('Cannot run GroupSeeder upon which this class depends !');
        }

        $this->createOrUpdateUser('prof@prof.com', 'prof@eduvaud.ch', 'prof', 'esseur', 'prof');
        $this->createOrUpdateUser('root@r.com', 'root@eduvaud.ch', 'ro', 'ot', 'root');
        $this->createOrUpdateUser('mp@mp.com', 'mp@eduvaud.ch', 'mp', 'rincipal', 'mp');
        $padawan = $this->createOrUpdateUser('padawan@mp.com', 'padawan@eduvaud.ch', 'pada', 'wan', 'eleve');

        //Creates 24 teachers
        foreach (User::factory()->count(app()->environment('test') ? 3 : 24)->create() as $user) {
            $user->assignRole('prof');
        }

        //Creates 250 students

        //$groupNames = GroupName::all();
        //$periods = AcademicPeriod::where('year(end)','=',now()->year)->get();
        $currentGroups = Group::whereHas('academicPeriod', function (Builder $query) {
            return $query
                ->where('start', '<=', now())
                ->where('end', '>=', now());
        })->get();
        $pastGroups = Group::whereHas('academicPeriod', function (Builder $query) {
            return $query->where('end', '<', now());
        })->get();

        $students = User::factory()->count(app()->environment('testing') ? 3 : 60)->create()
            ->prepend($padawan); // main test user must have some contracts

        //Create groupMember
        foreach ($students as $user) {
            /* @var $user \App\Models\User */
            $user->assignRole(RoleName::STUDENT);

            //groupMember for current period
            $randomGroup = $currentGroups[array_rand($currentGroups->toArray(), 1)];
            GroupMember::create([
                'user_id' => $user->id,
                'group_id' => $randomGroup->id,
            ]);

            //Random history of groupMember
            foreach (array_rand($pastGroups->toArray(), 3) as $randomPastGroupKey) {
                GroupMember::create([
                    'user_id' => $user->id,
                    'group_id' => $pastGroups[$randomPastGroupKey]->id,
                ]);
            }

        }

    }

    public function createOrUpdateUser($email, $username, $fn, $ln, ...$roles): \Illuminate\Database\Eloquent\Model|User
    {
        $user = User::updateOrCreate([
            'firstname' => $fn,
            'lastname' => $ln,
            'email' => $email,
            'username' => $username,
        ]);

        //reset
        $user->syncRoles([]);

        //fill
        $user->assignRole($roles);

        return $user;
    }
}
