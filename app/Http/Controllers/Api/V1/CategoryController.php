<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\IndexCategoryRequest;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Jobs\DestroyCloudinaryImage;
use App\Models\Category;
use App\Services\CloudinaryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexCategoryRequest $request)
    {
        $this->authorize('viewAny', Category::class);

        $filters = $request->validated();

        $query = Category::query()
            ->when(
                $filters['search'] ?? null,
                fn ($query, $search) =>
                    $query->where('name', 'ilike', "%{$search}%")
            );

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';

        $categories = $query
            ->orderBy($sortBy, $sortOrder)
            ->paginate($filters['per_page'] ?? 15);

        return CategoryResource::collection($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request, CloudinaryService $cloudinary)
    {
        $this->authorize('create', Category::class);

        $validated = $request->validated();

        if ($request->hasFile('image')) {
            $result = $cloudinary
                ->upload($request->file('image'), 'categories');
            
            $validated['image_url'] = $result['secure_url'];
            $validated['image_public_id'] = $result['public_id'];
        }

        $category = Category::create($validated);

        return (CategoryResource::make($category))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        $this->authorize('view', $category);

        return CategoryResource::make($category);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category, CloudinaryService $cloudinary)
    {
        $this->authorize('update', $category);

        $validated = $request->validated();
        $oldImagePublicId = $category->image_public_id;


        if ($request->hasFile('image')) {
            $result = $cloudinary
                ->upload($request->file('image'), 'categories');
            
            $validated['image_url'] = $result['secure_url'];
            $validated['image_public_id'] = $result['public_id'];
        }

        $category->update($validated);

        if ($request->hasFile('image') && $oldImagePublicId) {
            DestroyCloudinaryImage::dispatch($oldImagePublicId);
        }

        return CategoryResource::make($category);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category, CloudinaryService $cloudinary)
    {
        $this->authorize('delete', $category);
        
        $category->delete();

        if ($category->image_public_id) {
            DestroyCloudinaryImage::dispatch($category->image_public_id);
        }

        return response()->noContent();
    }
}
