<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Address\StoreAddressRequest;
use App\Http\Requests\Address\UpdateAddressRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return AddressResource::collection(
            request()->user()->addresses()
                ->orderByDesc('is_default')
                ->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAddressRequest $request)
    {
        $this->authorize('create', Address::class);

        $count = $request->user()->addresses()->count();

        abort_if($count >= 3, 409, 'You can only register 3 address max');

        $validated = $request->validated();
        $validated['user_id'] = $request->user()->id;

        $address = Address::create($validated);

        return (AddressResource::make($address))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Address $address)
    {
        $this->authorize('view', $address);

        return AddressResource::make($address);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAddressRequest $request, Address $address)
    {
        $this->authorize('update', $address);

        $address->update($request->validated());

        return AddressResource::make($address);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Address $address)
    {
        $this->authorize('delete', $address);

        $address->delete();

        return response()->noContent();
    }
}
