<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Super admin bypass.
     *
     * This is the key fix for the Users menu not showing in Filament.
     * The project uses Filament Shield, so normal policy methods depend on
     * granular permissions. A user with the super_admin role should always
     * be allowed to manage admin resources.
     */
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('super_admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_user');
    }

    public function view(User $user, User $model): bool
    {
        return $user->can('view_user');
    }

    public function create(User $user): bool
    {
        return $user->can('create_user');
    }

    public function update(User $user, User $model): bool
    {
        return $user->can('update_user');
    }

    public function delete(User $user, User $model): bool
    {
        return $user->can('delete_user');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_user');
    }

    public function forceDelete(User $user, User $model): bool
    {
        return $user->can('force_delete_user');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_user');
    }

    public function restore(User $user, User $model): bool
    {
        return $user->can('restore_user');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_user');
    }

    public function replicate(User $user, User $model): bool
    {
        return $user->can('replicate_user');
    }

    public function reorder(User $user): bool
    {
        return $user->can('reorder_user');
    }
}
