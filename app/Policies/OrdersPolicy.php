<?php

namespace App\Policies;

use App\Models\Orders;
use App\Models\ApiUser;
use App\Enums\OrderStatus;

class OrdersPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(ApiUser $user): bool
    {
        return false; // Only admins can view all orders
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(ApiUser $user, Orders $order): bool
    {
        return $user->user_id === $order->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(ApiUser $user): bool
    {
        return $user->email_verified; // Only verified users can create orders
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(ApiUser $user, Orders $order): bool
    {
        return $user->user_id === $order->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(ApiUser $user, Orders $order): bool
    {
        return false; // Maybe only admins can delete orders
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(ApiUser $users, Orders $orders): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(ApiUser $users, Orders $orders): bool
    {
        return false;
    }

    public function markAsPaid(ApiUser $user, Orders $order): bool
    {
        
        return $user->user_id === $order->user_id && 
            $order->status === OrderStatus::PENDING;
    }
}
