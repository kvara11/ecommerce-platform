<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CategoryService
{
    
    public function paginate(array $filters, int $perPage = 15)
    {
        $query = Category::query()->with(['parent', 'children']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('description', 'ILIKE', "%{$search}%");
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        if (!empty($filters['parent_id'])) {
            $query->where('parent_id', $filters['parent_id']);
        } else {
            // Show only root categories by default if not filtering
            $query->whereNull('parent_id');
        }

        return $query->orderBy('sort_order')->latest()->paginate($perPage)->withQueryString();
    }


    public function create(array $data): Category
    {
        $payload = [
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'description' => $data['description'] ?? null,
            'parent_id' => $data['parent_id'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $data['is_active'] ?? true,
        ];

        $category = Category::create($payload);

        // Handle image upload
        if (!empty($data['image'])) {
            $this->uploadImage($category, $data['image']);
        }

        return $category->load(['parent', 'children']);
    }


    public function update(Category $category, array $data): Category
    {
        $payload = collect($data)->only([
            'name', 'description', 'parent_id', 'sort_order', 'is_active'
        ])->toArray();

        // Update slug if name changed
        if (!empty($data['name']) && $data['name'] !== $category->name) {
            $payload['slug'] = Str::slug($data['name']);
        }

        $category->update($payload);

        // Handle image upload
        if (!empty($data['image'])) {
            // Delete old image if exists
            if ($category->image_url) {
                $this->deleteImage($category);
            }
            $this->uploadImage($category, $data['image']);
        }

        return $category->load(['parent', 'children']);
    }


    public function delete(Category $category): void
    {
        // Delete image if exists
        if ($category->image_url) {
            $this->deleteImage($category);
        }

        // Move children to parent category
        if ($category->children()->exists()) {
            $category->children()->update(['parent_id' => $category->parent_id]);
        }

        // Soft delete category
        $category->delete();
    }


    public function toggleStatus(Category $category): Category
    {
        $category->update(['is_active' => !$category->is_active]);
        return $category->fresh()->load(['parent', 'children']);
    }


    /**
     * Upload category image
     */
    private function uploadImage(Category $category, $imageFile): void
    {
        $path = Storage::disk('public')->putFile('categories', $imageFile);
        $category->update(['image_url' => Storage::disk('public')->url($path)]);
    }


    /**
     * Delete category image
     */
    private function deleteImage(Category $category): void
    {
        if ($category->image_url) {
            $pathToDelete = str_replace(Storage::disk('public')->url(''), '', $category->image_url);
            if (Storage::disk('public')->exists($pathToDelete)) {
                Storage::disk('public')->delete($pathToDelete);
            }
        }
    }


    /**
     * Get all root categories (for parent selection)
     */
    public function getRootCategories()
    {
        return Category::whereNull('parent_id')->orderBy('sort_order')->get();
    }


    /**
     * Get category with all relationships
     */
    public function getById(int $id): ?Category
    {
        return Category::with(['parent', 'children', 'products'])->find($id);
    }
}
