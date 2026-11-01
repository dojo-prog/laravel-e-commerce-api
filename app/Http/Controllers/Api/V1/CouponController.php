<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Coupon\IndexCouponRequest;
use App\Http\Requests\Coupon\StoreCouponRequest;
use App\Http\Requests\Coupon\UpdateCouponRequest;
use App\Http\Resources\CouponResource;
use App\Models\Coupon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexCouponRequest $request)
    {
        $this->authorize('viewAny', Coupon::class);

        $filters = $request->validated();

        $query = Coupon::query()
            ->when(
                $filters['search'] ?? null, 
                fn ($q, $search) =>
                    $q->where('code', 'ilike', "%{$search}%")
            )
            ->when(
                $filters['is_active'] ?? null, 
                fn ($q, $is_active) =>
                    $q->where('is_active', $is_active)
            )
            ->when(
                $filters['type'] ?? null, 
                fn ($q, $type) =>
                    $q->where('type', $type)
            );

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';

        $coupons = $query
            ->orderBy($sortBy, $sortOrder)
            ->paginate($filters['per_page'] ?? 15);

        return CouponResource::collection($coupons);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCouponRequest $request)
    {
        $this->authorize('create', Coupon::class);

        $validated = $request->validated();

        $coupon = Coupon::create($validated);

        return (CouponResource::make($coupon))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Coupon $coupon)
    {
        $this->authorize('view', $coupon);

        return CouponResource::make($coupon);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCouponRequest $request, Coupon $coupon)
    {
        $this->authorize('update', $coupon);

        $coupon->update($request->validated());

        return CouponResource::make($coupon);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Coupon $coupon)
    {
        $this->authorize('delete', $coupon);

        $coupon->delete();

        return response()->noContent();
    }
}
