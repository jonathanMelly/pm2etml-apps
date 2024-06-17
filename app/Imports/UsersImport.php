<?php

namespace App\Imports;

use App\Constants\RoleName;
use App\Models\AcademicPeriod;
use App\Models\Contract;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use App\Models\WorkerContract;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithProgressBar;
use Maatwebsite\Excel\Concerns\WithValidation;

class UsersImport implements SkipsEmptyRows, SkipsOnFailure, ToCollection, WithHeadingRow, WithProgressBar, WithValidation
{
    use Importable, SkipsFailures;

    public array $added = [];

    public array $updated = [];

    public array $same = [];

    public array $deleted = [];

    public array $restored = [];

    public array $warning = [];

    public function collection(Collection $collection)
    {

        foreach ($collection as $row) {

            //GET DATA
            $firstname = $row['firstname'];
            $lastname = $row['lastname'];
            /*$email = $row['email'];*/
            $login = $row['login'];
            $roles = collect(explode(',', $row['roles']))->filter()/*removes empty '' entries*/ ->transform(fn ($el) => strtolower($el));
            $newGroupNameNames = collect(explode(',', $row['groups']))->filter()->transform(fn ($el) => strtolower($el));
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

            if ($isUpdate) {
                $reportInfo = $user->getFirstnameL(true);
            } else {
                $reportInfo = ($firstname ?? 'unknown').' '.($lastname ?? 'unknown').'<?>';
            }

            $year = $period->start->year;

            //Trash handling
            if (Str::contains($comment, 'rupture', true)) {
                if ($isUpdate) {
                    if ($user->trashed()) {
                        $this->warning[] = $reportInfo.' already deleted -> ignoring';
                    } else {
                        $user->delete(); //soft delete
                        $user->groupMembersForPeriod($period->id)->each(fn ($gm) => $gm->delete()); //soft delete association for current period
                        $this->deleted[] = $reportInfo;
                    }

                } else {
                    $this->warning[] = $reportInfo.' marked as deleted but was never added before -> ignoring';
                }

                continue;
            } elseif ($isUpdate && $user->trashed()) {
                $user->restore();
                $this->restored[] = $reportInfo; //add restore count even if it can also be updated / added / ... (thus 1 person may be counted as restored + updated)
            }

            $somethingHasBeenUpdated = false;

            //Fill data
            foreach (['firstname', 'lastname', 'email'] as $attribute) {
                $old = $user->getAttribute($attribute);
                $new = $row[$attribute];
                if (! stringNullOrEmpty($new) && $old != $new) {
                    $user->setAttribute($attribute, $new);
                    $somethingHasBeenUpdated = true;
                    $reportInfo .= '['.$attribute.':'.$old.'=>'.$new.']';
                }
            }

            if ($somethingHasBeenUpdated || ! $isUpdate) {
                $user->save();
            }

            //Smart role sync
            $missingRoles = collect($roles)->filter(fn ($newRole) => ! $user->hasRole($newRole));
            if ($missingRoles->isNotEmpty()) {
                $user->assignRole($missingRoles);
                $somethingHasBeenUpdated = true;
                $reportInfo .= '[role:+'.$missingRoles->implode(',+').']';
            }

            $rolesToRemove = collect($user->roles->pluck('name'))->filter(fn ($existingRole) => $roles->doesntContain($existingRole));
            if ($rolesToRemove->isNotEmpty()) {
                $rolesToRemove->each(fn ($role) => $user->removeRole($role));
                $somethingHasBeenUpdated = true;
                $reportInfo .= '[role:-'.$missingRoles->implode(',-').']';
            }

            $groupsToAdd = collect();
            //TEACHER custom
            if ($user->hasRole(RoleName::TEACHER)) {
                $reportInfo = '<PROF> '.$reportInfo;
                $currentGroups = $user->getGroupNames($period->id);

                $groupsToAdd = $newGroupNameNames->filter(fn ($newGroup) => $currentGroups->doesntContain($newGroup));

                $groupsToRemove = $currentGroups->filter(fn ($existingGroup) => $newGroupNameNames->doesntContain($existingGroup));

                //Remove old groups
                if ($groupsToRemove->isNotEmpty()) {
                    $user->groupMembersForPeriod($period->id)
                        ->whereHas('group.groupName', fn ($q) => $q->whereIn('name', $groupsToRemove))
                        ->each(fn ($gm) => $gm->delete());

                    $somethingHasBeenUpdated = true;
                    $reportInfo .= '[groups/'.$year.':-'.$groupsToRemove->implode(',-').']';
                }

            } //STUDENT custom
            elseif ($user->hasRole(RoleName::STUDENT)) {
                $reportInfo = '<ELEVE> '.$reportInfo;

                $newGroupName = $newGroupNameNames[0]; /*A student is only in 1 class....*/

                //Student should have only 1 groupMember for a given period
                $currentGroupMembers = $user->groupMembersForPeriod($period->id)->with('group.groupName')->get();

                //DB Cleanup if necessary
                $currentGroupMember = null;
                if ($currentGroupMembers->count() > 1) {
                    Log::warning("Detected student with id {$user->id} in multiple groups, trying to clean up the mess");
                    foreach ($currentGroupMembers as $currentGroupMemberTemp) {
                        /* @var $currentGroupMemberTemp GroupMember */
                        $currentGroupName = $currentGroupMemberTemp->group->groupName->name;
                        if ($currentGroupName != $newGroupName) {
                            Log::info("Removing group {$currentGroupName} from user id {$user->id} and period {$period->id}");
                            $currentGroupMemberTemp->forceDelete();
                            $reportInfo .= '[groups/'.$year.':-'.$currentGroupName.']';
                            $somethingHasBeenUpdated = true;
                        } else {
                            //keep that info for later checks
                            $currentGroupMember = $currentGroupMemberTemp;
                        }
                    }
                } else {
                    //load first group if existing
                    $currentGroupMember = $user->groupMember($period->id, true);
                }

                if ($currentGroupMember !== null) {
                    //Group change (mutation)
                    $currentGroupName = $currentGroupMember->group->groupName->name;
                    $previousGroupYear = $currentGroupMember->group->groupName->year;

                    if ($currentGroupName != $newGroupName) {

                        //Migrate instead of delete/add to keep track of previous evaluations (if not repetition)
                        $newGroup = Group::where('academic_period_id', '=', $period->id)
                            ->whereRelation('groupName', 'name', '=', $newGroupName)
                            ->with('groupName')
                            ->firstOrFail();
                        $currentGroupMember->group_id = $newGroup->id;

                        $currentGroupMember->save();

                        //Year is diminishing => Repetition => drop contracts
                        if ($previousGroupYear > $newGroup->groupName->year) {
                            if (! Str::contains($comment, 'redoublement', true)) {
                                $this->warning[] = "{$user->getFirstnameL(true)} : repetition detected but missing conventional comment, please check !";
                            }

                            $droppedWcs = collect();
                            $droppedContracts = collect();

                            $user->contractsAsAWorker($period->id)->each(
                                function (Contract $contract) use ($user, $period, $droppedWcs, $droppedContracts) {

                                    //Drop each worker contract already evaluated for this period and this user
                                    $contract->workerContract($user->groupMember($period->id))
                                        ->whereNotNull('success_date')
                                        ->each(
                                            function (WorkerContract $wc) use ($droppedWcs) {
                                                $wc->softDelete();

                                                $droppedWcs[] = $wc->id;
                                            }
                                        );
                                    $contract->refresh();

                                    //Do not drop contract if others workers are still in the game...
                                    //Remember that manuallySoftDeleted wc are auto filtered...
                                    $remainingWorkersContracts = $contract->workerContract($user->groupMember($period->id))->count();
                                    if ($remainingWorkersContracts == 0) {
                                        $contract->delete();
                                        $droppedContracts[] = $contract->id;
                                    }
                                });

                            if ($droppedContracts->count() > 0 || $droppedWcs->count() > 0) {
                                $this->warning[] = "{$user->getFirstnameL(true)} : repetition, deleted cids: {$droppedContracts->implode(',')} | wcids:{$droppedWcs->implode(',')}";
                            }
                        }

                        $reportInfo .= '[groups/'.$year.': '.$currentGroupName.' => '.$newGroupName.']';
                        $somethingHasBeenUpdated = true;
                    }
                    //Else Group is already good, we do nothing ;-)
                } else {
                    //brand-new group
                    $groupsToAdd = collect([$newGroupName]);
                }
            }

            //Add new ones
            if ($groupsToAdd->isNotEmpty()) {
                $somethingHasBeenUpdated = true;
                $groupsToAdd->each(fn ($groupToAdd) => $user->joinGroup($period->id, $groupToAdd));

                $reportInfo .= '[groups/'.$year.':+'.$groupsToAdd->implode(',+').']';
            }

            if ($isUpdate) {
                if ($somethingHasBeenUpdated) {
                    $this->updated[] = $reportInfo;
                } else {
                    $this->same[] = $reportInfo;
                }

            } else {
                $this->added[] = $reportInfo;
            }

        }
    }

    public function rules(): array
    {
        return [
            'login' => 'required',
            'email' => function ($attribute, $value, $onFailure) {
                if (! stringNullOrEmpty($value) && ! Str::endsWith($value, '@eduvaud.ch')) {
                    $onFailure("Invalid email ($value)");
                }
            },
            'roles' => function ($attribute, $value, $onFailure) {
                if (! stringNullOrEmpty($value)) {
                    $roles = explode(',', $value);
                    foreach ($roles as $role) {
                        if (! in_array($role, RoleName::AVAILABLE_ROLES)) {
                            $onFailure("Invalid role ($role)");
                            break;
                        }
                    }
                    if (in_array(RoleName::STUDENT, $roles) && in_array(RoleName::TEACHER, $roles)) {
                        $onFailure('User cannot be '.RoleName::TEACHER.' and '.RoleName::STUDENT);
                    }
                }

            }];
    }
}
