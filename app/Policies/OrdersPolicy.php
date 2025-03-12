<?php

namespace App\Policies;

use App\Models\Orders;
use App\Models\Users;

class OrdersPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Users $users): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Users $users, Orders $orders): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Users $users): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Users $users, Orders $orders): bool
    {
        return $users->user_id === $orders->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Users $users, Orders $orders): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Users $users, Orders $orders): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Users $users, Orders $orders): bool
    {
        return false;
    }
}
