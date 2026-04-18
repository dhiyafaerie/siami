<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Berkas;
use Illuminate\Auth\Access\HandlesAuthorization;

class BerkasPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_berkas::prodi');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Berkas $berkas): bool
    {
        return $user->can('view_berkas::prodi');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_berkas::prodi');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Berkas $berkas): bool
    {
        return $user->can('update_berkas::prodi');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Berkas $berkas): bool
    {
        return $user->can('delete_berkas::prodi');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_berkas::prodi');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Berkas $berkas): bool
    {
        return $user->can('force_delete_berkas::prodi');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_berkas::prodi');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Berkas $berkas): bool
    {
        return $user->can('restore_berkas::prodi');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_berkas::prodi');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Berkas $berkas): bool
    {
        return $user->can('replicate_berkas::prodi');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_berkas::prodi');
    }
}
