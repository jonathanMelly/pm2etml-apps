<?php

namespace App\Imports;

use App\Constants\RoleName;
use App\Models\AcademicPeriod;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithProgressBar;
use Maatwebsite\Excel\Concerns\WithValidation;

class UsersImport implements ToCollection, WithHeadingRow, WithValidation, WithProgressBar, SkipsOnFailure,SkipsEmptyRows
{
    use Importable, SkipsFailures;

    public array $added = [];
    public array $updated = [];
    public array $same = [];
    public array $deleted = [];
    public array $restored = [];
    public array $warning = [];

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {

        foreach ($collection as $row) {

            //GET DATA
            /*$firstname = $row['firstname'];
            $lastname = $row['lastname'];
            $email = $row['email'];*/
            $login = $row['login'];
            $roles = collect(explode(',', $row['roles']))->transform(fn($el)=>strtolower($el));
            $newGroupNameNames = collect(explode(',', $row['groups']))->transform(fn($el)=>strtolower($el));
            $period = $row['period'];
            //Get corresponding db period
            if ($period === null || trim($period) === '') {
                $period = AcademicPeriod::current(false);
            } else {
                $period = AcademicPeriod::forDate(
                    new \Carbon\Carbon(
                        \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($period)
                    )
                )->firstOrFail();
            }
            $comment = $row['comment'];

            //Look for an existing user
            $user = User::withTrashed()->firstOrNew(['username' => $login]);
            $isUpdate = $user->id !== null;

            //Trash handling
            if(Str::contains($comment,'rupture',true))
            {
                if($isUpdate)
                {
                    if($user->trashed())
                    {
                        $this->warning[]=$login.' already deleted -> ignoring';
                    }
                    else
                    {
                        $user->delete(); //soft delete
                        $user->groupMembersForPeriod($period->id)->each(fn($gm)=>$gm->delete());//soft delete association for current period
                        $this->deleted[] = $login;
                    }

                }
                else
                {
                    $this->warning[]=$login.' marked as deleted but was never added before -> ignoring';
                }

                continue;
            }
            else if($isUpdate && $user->trashed())
            {
                $user->restore();
                $this->restored[]=$login; //add restore count even if it can also be updated / added / ... (thus 1 person may be counted as restored + updated)
            }

            $somethingHasBeenUpdated = false;

            //Fill data
            foreach (['firstname', 'lastname', 'email'] as $attribute) {
                if (!stringNullOrEmpty($row[$attribute]) && $user->getAttribute($attribute) != $row[$attribute]) {
                    $user->setAttribute($attribute, $row[$attribute]);
                    $somethingHasBeenUpdated = true;
                }
            }

            if ($somethingHasBeenUpdated || !$isUpdate) {
                $user->save();
            }


            //Smart role sync
            $missingRoles = collect($roles)->filter(fn($newRole) => !$user->hasRole($newRole));
            if ($missingRoles->isNotEmpty()) {
                $user->assignRole($missingRoles);
                $somethingHasBeenUpdated = true;
            }

            $rolesToRemove = collect($user->roles->pluck('name'))->filter(fn($existingRole) => $roles->doesntContain($existingRole));
            if ($rolesToRemove->isNotEmpty()) {
                $rolesToRemove->each(fn($role) => $user->removeRole($role));
                $somethingHasBeenUpdated = true;
            }

            $groupsToAdd = collect();
            //TEACHER custom
            if ($user->hasRole(RoleName::TEACHER)) {
                $currentGroups = $user->getGroupNames($period->id);

                $groupsToAdd = $newGroupNameNames->filter(fn($newGroup) => $currentGroups->doesntContain($newGroup));

                $groupsToRemove = $currentGroups->filter(fn($existingGroup) => $newGroupNameNames->doesntContain($existingGroup));

                //Remove old groups
                if ($groupsToRemove->isNotEmpty()) {
                    $user->groupMembersForPeriod($period->id)
                        ->whereHas('group.groupName', fn($q) => $q->whereIn('name', $groupsToRemove))
                        ->delete();

                    $somethingHasBeenUpdated = true;
                }


            } //STUDENT custom
            else if ($user->hasRole(RoleName::STUDENT)) {
                //Student should have only 1 groupMember for a given period
                $currentGroupMember = $user->groupMember($period->id, true);
                $createGroupMember = false;
                if ($currentGroupMember !== null) {
                    //Group change
                    if ($currentGroupMember->group->groupName->name != $newGroupNameNames[0]) {
                        $currentGroupMember->delete();
                        $groupsToAdd = collect([$newGroupNameNames[0]]);
                    }
                    //Else Group is already good, we do nothing ;-)
                } else {
                    $groupsToAdd = collect([$newGroupNameNames[0]]);
                }
            }

            //Add new ones
            if ($groupsToAdd->isNotEmpty()) {
                $somethingHasBeenUpdated = true;
                $groupsToAdd->each(fn($groupToAdd) => $user->joinGroup($period->id, $groupToAdd));
            }


            if ($isUpdate) {
                if ($somethingHasBeenUpdated) {
                    $this->updated[] = $login;
                } else {
                    $this->same[] = $login;
                }

            } else {
                $this->added[] = $login;
            }

        }
    }

    public function rules(): array
    {
        return [
            'login'=> 'required',
            'email' => function ($attribute, $value, $onFailure) {
                if (!stringNullOrEmpty($value) && !Str::endsWith($value, '@eduvaud.ch')) {
                    $onFailure("Invalid email ($value)");
                }
            },
            'roles' => function ($attribute, $value, $onFailure) {
                if (!stringNullOrEmpty($value)) {
                    $roles = explode(',', $value);
                    foreach ($roles as $role) {
                        if (!in_array($role, RoleName::AVAILABLE_ROLES)) {
                            $onFailure("Invalid role ($role)");
                            break;
                        }
                    }
                    if (in_array(RoleName::STUDENT, $roles) && in_array(RoleName::TEACHER, $roles)) {
                        $onFailure('User cannot be ' . RoleName::TEACHER . ' and ' . RoleName::STUDENT);
                    }
                }


            }];
    }

}
