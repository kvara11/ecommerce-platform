<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Inventory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    
    public function paginate(array $filters, int $perPage = 15)
    {
        $query = Product::query()->with(['category', 'images', 'inventory']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('sku', 'ILIKE', "%{$search}%")
                  ->orWhere('description', 'ILIKE', "%{$search}%");
            });
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        return $query->latest()->paginate($perPage)->withQueryString();
    }


    public function create(array $data): Product
    {
        $payload = [
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'description' => $data['description'] ?? null,
            'short_description' => $data['short_description'] ?? null,
            'price' => $data['price'],
            'cost_price' => $data['cost_price'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'meta_title' => $data['meta_title'] ?? $data['name'],
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
        ];

        if (empty($data['sku'])) {
            $payload['sku'] = $this->generateSku($data['name']);
        } else {
            $payload['sku'] = $data['sku'];
        }

        $product = Product::create($payload);


        // Handle image upload
        if (!empty($data['image'])) {
            $this->uploadImage($product, $data['image']);
        }

        // Create inventory record
        if (!empty($data['quantity'])) {
            Inventory::create([
                'product_id' => $product->id,
                'quantity' => $data['quantity'],
                'reserved' => 0,
            ]);
        }

        return $product->load(['category', 'images', 'inventory']);
    }


    public function update(Product $product, array $data): Product
    {
        $payload = collect($data)->only([
            'category_id', 'name', 'description', 'short_description', 'price',
            'cost_price', 'is_active', 'meta_title', 'meta_description', 'meta_keywords'
        ])->toArray();

        // Update slug if name changed
        if (!empty($data['name']) && $data['name'] !== $product->name) {
            $payload['slug'] = Str::slug($data['name']);
        }

        // Update SKU if provided
        if (!empty($data['sku'])) {
            $payload['sku'] = $data['sku'];
        }

        $product->update($payload);

        // Handle image upload
        if (!empty($data['image'])) {
            $this->uploadImage($product, $data['image']);
        }

        // Update inventory quantity
        if (isset($data['quantity'])) {
            $inventory = $product->inventory ?? new Inventory(['product_id' => $product->id]);
            $inventory->quantity = $data['quantity'];
            $inventory->save();
        }

        return $product->load(['category', 'images', 'inventory']);
    }


    public function delete(Product $product): void
    {
        // Delete images
        if ($product->images) {
            foreach ($product->images as $image) {
                $this->deleteImage($image);
            }
        }

        // Delete inventory
        if ($product->inventory) {
            $product->inventory->delete();
        }

        // Soft delete product
        $product->delete();
    }


    public function toggleStatus(Product $product): Product
    {
        $product->update(['is_active' => !$product->is_active]);
        return $product->fresh()->load(['category', 'images', 'inventory']);
    }


    private function generateSku(string $name): string
    {
        $sku = Str::upper(Str::substr(Str::slug($name), 0, 3)) . '-' . time();
        
        // Ensure uniqueness
        while (Product::where('sku', $sku)->exists()) {
            $sku = Str::upper(Str::substr(Str::slug($name), 0, 3)) . '-' . time();
        }

        return $sku;
    }


    private function uploadImage(Product $product, $imageFile): void
    {
        $path = Storage::disk('public')->putFile('products', $imageFile);
        $hasImg = $product->images()->exists();

        ProductImage::create([
            'product_id' => $product->id,
            'url' => $path,
            'is_main' => !$hasImg,
            // 'sort_order' => $hasImg ? ($product->images()->max('sort_order') + 1) : 0,
        ]);

        return; 
    }   


    private function deleteImage(ProductImage $image): void
    {
        $imagePath = $image->url ?? $image->image_path ?? null;

        if ($imagePath && Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }

        $image->delete();
    }


    public function getById(int $id): ?Product
    {
        return Product::with(['category', 'images', 'inventory'])->find($id);
    }


    public function getQuantity(Product $product): int
    {
        return $product->inventory?->quantity ?? 0;
    }


    public function getImageUrl(Product $product): ?string
    {
        $primaryImage = $product->images->firstWhere('is_main', true);
        $imagePath = $primaryImage?->url ?? $primaryImage?->image_path;

        if(str_contains($imagePath, 'http')) {
            return $imagePath;
        }
        
        if ($imagePath) {
            return Storage::url($imagePath);
        }

        return null;
    }
}
