<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoriesViewController extends Controller
{
    protected CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Display a listing of categories.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'is_active']);
        $categories = $this->categoryService->paginate($filters, perPage: 15);

        // Transform categories for Vue component
        $categoriesData = $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'image_url' => $category->image_url,
                'sort_order' => $category->sort_order,
                'status' => $category->is_active ? 'active' : 'inactive',
                'is_active' => $category->is_active,
                'parent' => $category->parent ? [
                    'id' => $category->parent->id,
                    'name' => $category->parent->name,
                ] : null,
                'children_count' => $category->children()->count(),
                'products_count' => $category->products()->count(),
            ];
        });

        // Get root categories for parent selection
        $parentCategories = $this->categoryService->getRootCategories();

        return Inertia::render('Categories/Index', [
            'categories' => [
                'data' => $categoriesData,
                'meta' => [
                    'current_page' => $categories->currentPage(),
                    'per_page' => $categories->perPage(),
                    'total' => $categories->total(),
                    'last_page' => $categories->lastPage(),
                ],
            ],
            'parentCategories' => $parentCategories,
            'filters' => $filters,
        ]);
    }

    /**
     * Store a newly created category.
     */
    public function store(StoreCategoryRequest $request)
    {
        try {
            $category = $this->categoryService->create($request->validated());

            return back()->with('success', 'Category created successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Update the specified category.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        try {
            $this->categoryService->update($category, $request->validated());

            return back()->with('success', 'Category updated successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Delete the specified category.
     */
    public function destroy(Category $category)
    {
        try {
            $this->categoryService->delete($category);

            return back()->with('success', 'Category deleted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Toggle category status.
     */
    public function toggleStatus(Category $category)
    {
        try {
            $this->categoryService->toggleStatus($category);

            return back()->with('success', 'Category status updated successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
