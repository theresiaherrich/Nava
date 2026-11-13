<?php

namespace App\Policies;

use App\Models\NilaiTp;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class NilaiTpPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, NilaiTp $nilaiTp): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, NilaiTp $nilaiTp): bool
    {
        return $user->can('nilai.input') 
            && $nilaiTp->tp->pengampu->guru->user_id === $user->id
            && $nilaiTp->tp->periode->is_open;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, NilaiTp $nilaiTp): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, NilaiTp $nilaiTp): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, NilaiTp $nilaiTp): bool
    {
        return false;
    }
}
