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
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->can('jobs.view');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\JobDefinition  $job
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, JobDefinition $job)
    {
        //Currently all users can see all jobs...
        return self::viewAny($user);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->can('jobs.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\JobDefinition  $job
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, JobDefinition $job)
    {
        if($user->can('jobs.update')){
            return $user->can('jobs') || $job->providers()->find($user->id)->containsOneItem();
        }

    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\JobDefinition  $job
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, JobDefinition $job)
    {
        if($user->can('jobs.trash')){
            return $user->can('jobs') || $job->providers()->find($user->id)->containsOneItem();
        }
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\JobDefinition  $job
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, JobDefinition $job)
    {
        //same as delete (softdelete = trash)
        return $this->delete($user,$job);
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\JobDefinition  $job
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, JobDefinition $job)
    {
        return false;//no real delete through web interface
    }
}
