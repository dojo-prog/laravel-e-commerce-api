<?php

namespace App\Policies;

use App\Models\CouponUsage;
use App\Models\User;

class CouponUsagePolicy
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
    public function view(User $user, CouponUsage $couponUsage): bool
    {
        return $couponUsage->user_id === $user->id || $user->hasRole(['admin']);
    }

    /**
     * Determine whether the user can create models.
     *
     * Usages are recorded by the checkout flow when a coupon is applied.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CouponUsage $couponUsage): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CouponUsage $couponUsage): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CouponUsage $couponUsage): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CouponUsage $couponUsage): bool
    {
        return false;
    }
}
