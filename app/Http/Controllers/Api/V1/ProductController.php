<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\IndexProductRequest;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexProductRequest $request)
    {
        $this->authorize('viewAny', Product::class);

        $isAdmin = $request->user()?->hasRole(['admin']);

        $filters = $request->validated();

        $query = Product::query()
            ->with(['category', 'images']);

        $query
            ->when(
                $filters['search'] ?? null,
                fn ($query, $search) => 
                    $query->where(function ($sub) use ($search) {
                        $sub->where('name', 'ilike', "%{$search}%")
                        ->orWhere('sku', 'ilike', "%{$search}%");
                    })
            )
            ->when(
                $filters['category_id'] ?? null, 
                fn ($query, $category_id) =>
                    $query->where('category_id', $category_id)
            )
            ->when(
                $filters['min_price'] ?? null, 
                fn ($query, $min_price) =>
                    $query->where('price', '>=', $min_price)
            )
            ->when(
                $filters['max_price'] ?? null, 
                fn ($query, $max_price) =>
                    $query->where('price', '<=', $max_price)
            )
            ->when(
                ($filters['status'] ?? null) && $isAdmin , 
                fn ($query, $status) =>
                    $query->where('status', $status)
            )
            ->when(
                $filters['in_stock'] ?? null, 
                fn ($query) =>
                    $query->where('stock_quantity', '>', 0)
            );

        if (! $isAdmin) {
            $query->where('status', 'active');
        }    
        
        $sortBy = $filters['sort_by'] ?? 'created_at'; 
        $sortOrder = $filters['sort_order'] ?? 'desc';
        
        $products = $query
            ->orderBy($sortBy, $sortOrder)
            ->paginate($filters['per_page'] ?? 15);

        return ProductResource::collection($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $this->authorize('create', Product::class);

        $product = Product::create($request->validated());
        $product->load(['category', 'images']);

        return (ProductResource::make($product))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $isAdmin = request()->user()?->hasRole(['admin']);

        $product = Product::with(['category', 'images'])
            ->when(! $isAdmin, fn ($query) => $query->where('status', 'active'))
            ->findOrFail($id);

        return ProductResource::make($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->update($request->validated());
        $product->load(['category', 'images']);

        return ProductResource::make($product);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->noContent();
    }
}
