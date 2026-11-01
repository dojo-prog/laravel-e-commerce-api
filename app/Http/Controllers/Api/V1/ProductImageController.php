<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductImage\StoreProductImageRequest;
use App\Http\Resources\ProductImageResource;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\CloudinaryService;
use Illuminate\Support\Facades\DB;

class ProductImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Product $product)
    {
        $this->authorize('viewAny', ProductImage::class);

        return ProductImageResource::collection(
            $product->images
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductImageRequest $request, CloudinaryService $cloudinary)
    {
        $this->authorize('create', ProductImage::class);

        $validated = $request->validated();

        $file = $request->file('image');

        $result = $cloudinary->upload($file, "products/$validated->product_id");
        
        $validated['image_url'] = $result['secure_url'];
        $validated['image_public_id'] = $result['public_id'];

        $image = DB::transaction(function () use ($validated) {
            if ($validated['is_primary'] === true) {
                ProductImage::query()
                    ->where('product_id', $validated['product_id'])
                    ->update(['is_primary' => false]);
            }

            return ProductImage::create($validated);
        });

        return (ProductImageResource::make($image))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductImage $image) 
    {
        $this->authorize('delete', $image);

        $image->delete();

        return response()->noContent();
    }
}
