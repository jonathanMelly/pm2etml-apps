<?php

namespace App\Policies;

use App\Models\JobDefinition;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class JobDefinitionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('jobDefinitions.view');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\JobDefinition  $job
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, JobDefinition $jobDefinition): bool
    {
        //Currently all users can see all jobs...
        return self::viewAny($user);
    }

    /**
     * Determine whether the user can create models.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user): bool
    {
        return $user->can('jobDefinitions.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, JobDefinition $jobDefinition): bool
    {
        return $user->can('jobDefinitions') ||
            ($user->can('jobDefinitions.edit') && $jobDefinition->providers()->find($user) !== null);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, JobDefinition $jobDefinition): bool
    {
        if ($user->can('jobDefinitions.trash')) {
            return $user->can('jobDefinitions') || $jobDefinition->providers()->find($user->id) !== null;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, JobDefinition $jobDefinition): bool
    {
        //same as delete (softdelete = trash)
        return $this->delete($user, $jobDefinition);
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, JobDefinition $jobDefinition): bool
    {
        return false; //no real delete through web interface
    }
}
