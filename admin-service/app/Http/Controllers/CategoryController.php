<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\CategoryCollection;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(Request $request)
    {
        $query = Category::query();

        // Load relationships
        if ($request->get('with_parent')) {
            $query->with('parent');
        }

        if ($request->get('with_children')) {
            $query->with('children');
        }

        // Count relationships
        $query->withCount(['children', 'products']);

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by parent (root categories)
        if ($request->has('parent_id')) {
            if ($request->parent_id === 'null' || $request->parent_id === null) {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $request->parent_id);
            }
        }

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Include soft deleted
        if ($request->get('with_trashed')) {
            $query->withTrashed();
        }

        // Only trashed
        if ($request->get('only_trashed')) {
            $query->onlyTrashed();
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'sort_order');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        
        if ($request->get('no_pagination')) {
            $categories = $query->get();
            return CategoryResource::collection($categories);
        }

        $categories = $query->paginate($perPage);

        return new CategoryCollection($categories);
    }

    /**
     * Get category tree (nested structure).
     */
    public function tree(Request $request)
    {
        $query = Category::whereNull('parent_id')
            ->with(['children' => function($q) {
                $q->orderBy('sort_order', 'asc');
            }])
            ->withCount(['children', 'products']);

        if ($request->get('is_active')) {
            $query->where('is_active', true);
        }

        $query->orderBy('sort_order', 'asc');

        $categories = $query->get();

        return response()->json([
            'success' => true,
            'data' => CategoryResource::collection($categories)
        ]);
    }

    /**
     * Store a newly created category.
     */
    public function store(StoreCategoryRequest $request)
    {
        // Auto-generate slug if not provided
        $data = $request->validated();
        
        if (!isset($data['slug']) && isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $category = Category::create($data);

        $category->load('parent', 'children')
                 ->loadCount(['children', 'products']);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data' => new CategoryResource($category)
        ], 201);
    }

    /**
     * Display the specified category.
     */
    public function show($id, Request $request)
    {
        $query = Category::query();

        if ($request->get('with_trashed')) {
            $query->withTrashed();
        }

        $category = $query->with(['parent', 'children'])
                          ->withCount(['children', 'products'])
                          ->find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new CategoryResource($category)
        ]);
    }

    /**
     * Update the specified category.
     */
    public function update(UpdateCategoryRequest $request, $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        // Prevent circular reference
        if ($request->has('parent_id') && $this->wouldCreateCircularReference($category, $request->parent_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot set parent category: circular reference detected'
            ], 422);
        }

        $category->update($request->validated());

        $category->load('parent', 'children')
                 ->loadCount(['children', 'products']);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data' => new CategoryResource($category)
        ]);
    }

    /**
     * Remove the specified category (soft delete).
     */
    public function destroy($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        // Check if category has children
        if ($category->children()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with subcategories'
            ], 422);
        }

        // Check if category has products
        if ($category->products()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with products'
            ], 422);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully'
        ]);
    }

    /**
     * Restore soft deleted category.
     */
    public function restore($id)
    {
        $category = Category::onlyTrashed()->find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        $category->restore();

        $category->load('parent', 'children')
                 ->loadCount(['children', 'products']);

        return response()->json([
            'success' => true,
            'message' => 'Category restored successfully',
            'data' => new CategoryResource($category)
        ]);
    }

    /**
     * Permanently delete category.
     */
    public function forceDelete($id)
    {
        $category = Category::withTrashed()->find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        $category->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Category permanently deleted'
        ]);
    }

    /**
     * Toggle category active status.
     */
    public function toggleStatus($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        $category->update([
            'is_active' => !$category->is_active
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category status updated successfully',
            'data' => new CategoryResource($category)
        ]);
    }

    /**
     * Reorder categories.
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'categories' => 'required|array',
            'categories.*.id' => 'required|exists:categories,id',
            'categories.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($request->categories as $item) {
            Category::where('id', $item['id'])->update([
                'sort_order' => $item['sort_order']
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Categories reordered successfully'
        ]);
    }

    /**
     * Check if setting parent would create circular reference.
     */
    private function wouldCreateCircularReference(Category $category, $parentId)
    {
        if (!$parentId) {
            return false;
        }

        $parent = Category::find($parentId);
        
        while ($parent) {
            if ($parent->id === $category->id) {
                return true;
            }
            $parent = $parent->parent;
        }

        return false;
    }
}