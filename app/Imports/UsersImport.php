<?php

namespace App\Imports;

use App\Constants\RoleName;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithProgressBar;
use Maatwebsite\Excel\Concerns\WithValidation;

class UsersImport implements ToCollection, WithHeadingRow, WithValidation,WithProgressBar
{
    use Importable;

    public array $added=[];
    public array $updated=[];

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        foreach ($collection as $row) {

            //GET DATA
            $firstname = $row['firstname'];
            $lastname = $row['lastname'];
            $email = $row['email'];
            $login = $row['login'];
            $roles =$row['roles'];

            //Look for an existing user
            $user = User::where('email','=',$email)->firstOrNew();
            $isUpdate = $user->id!==null;

            //Fill data
            $user->firstname=$firstname;
            $user->lastname=$lastname;
            $user->email=$email;
            $user->username=$login;

            //UPDATE DB
            $user->save();

            //RESET ROLES
            $user->syncRoles(explode(',',$roles));

            if($isUpdate)
            {
                $this->updated[]=$email;
            }
            else
            {
                $this->added[]=$email;
            }

            //TODO for student, add/update groupMember...

        }
    }

    public function rules(): array
    {
        return [
            'email'=> function ($attribute, $value, $onFailure) {
                    if(!Str::endsWith($value,'@eduvaud.ch'))
                    {
                        $onFailure('Invalid email');
                    }
                },
            'roles' => function ($attribute, $value, $onFailure) {
                $roles = explode(',', $value);
                foreach ($roles as $role)
                {
                    if(!in_array($role,RoleName::AVAILABLE_ROLES))
                    {
                        $onFailure('Invalid role ');
                        break;
                    }
                }

            }];
    }

}
