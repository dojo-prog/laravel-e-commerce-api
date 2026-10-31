<?php

namespace App\Policies;

use App\Models\OrderStatusHistory;
use App\Models\User;

class OrderStatusHistoryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, OrderStatusHistory $orderStatusHistory): bool
    {
        $orderStatusHistory->loadMissing('order');

        return $orderStatusHistory->order->user_id === $user->id || $user->hasRole(['admin']);
    }

    /**
     * Determine whether the user can create models.
     *
     * Status histories are append-only records written by order transitions.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, OrderStatusHistory $orderStatusHistory): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, OrderStatusHistory $orderStatusHistory): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, OrderStatusHistory $orderStatusHistory): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, OrderStatusHistory $orderStatusHistory): bool
    {
        return false;
    }
}
