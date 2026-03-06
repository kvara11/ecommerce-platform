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

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'is_active']);
        
        $allCategories = Category::with(['parent', 'children'])
            ->when(isset($filters['search']), function ($query) use ($filters) {
                $query->where('name', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            })
            ->when(isset($filters['is_active']), function ($query) use ($filters) {
                $query->where('is_active', $filters['is_active']);
            })
            ->orderBy('sort_order')
            ->get();

        $transformCategory = function ($category) use (&$transformCategory) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'image_url' => $category->image_url,
                'sort_order' => $category->sort_order,
                'status' => $category->is_active ? 'active' : 'inactive',
                'is_active' => $category->is_active,
                'parent_id' => $category->parent_id,
                'parent' => $category->parent ? [
                    'id' => $category->parent->id,
                    'name' => $category->parent->name,
                ] : null,
                'children' => $category->children->map($transformCategory)->toArray(),
                'children_count' => $category->children->count(),
                'products_count' => $category->products()->count(),
            ];
        };

        $categoriesData = $allCategories->map($transformCategory);

        $parentCategories = $this->categoryService->getRootCategories();

        return Inertia::render('Categories/Index', [
            'categories' => [
                'data' => $categoriesData,
                'meta' => [
                    'current_page' => 1,
                    'per_page' => $categoriesData->count(),
                    'total' => $categoriesData->count(),
                    'last_page' => 1,
                ],
            ],
            'parentCategories' => $parentCategories,
            'filters' => $filters,
        ]);
    }

    public function store(StoreCategoryRequest $request)
    {
        try {
            $category = $this->categoryService->create($request->validated());

            return back()->with('success', 'Category created successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }


    public function update(UpdateCategoryRequest $request, Category $category)
    {
        try {
            $this->categoryService->update($category, $request->validated());

            return back()->with('success', 'Category updated successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }


    public function destroy(Category $category)
    {
        try {
            $this->categoryService->delete($category);

            return back()->with('success', 'Category deleted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

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
