<?php

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProductObserver
{
    public function saved(Product $product): void
    {
        $this->syncToNestJs($product, 'saved');
    }
    public function updated(Product $product): void
    {
        $this->syncToNestJs($product, 'updated');
    }

    public function deleted(Product $product): void
    {
        $this->syncToNestJs($product, 'deleted');
    }

    private function syncToNestJs(Product $product, string $action): void
    {
        try {

            $syncUrl = config('services.nestjs.sync_url');
            $syncKey = config('services.nestjs.sync_key');

            if (empty($syncKey)) {
                Log::warning('SYNC_KEY is not configured. Product sync skipped.', [
                    'product_id' => $product->id,
                ]);
                return;
            }

            $payload = [
                'action' => $action,
                'product' => [
                    'id' => $product->id,
                    'category_id' => $product->category_id,
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'description' => $product->description,
                    'short_description' => $product->short_description,
                    'price' => $product->price,
                    'cost_price' => $product->cost_price,
                    'is_active' => $product->is_active,
                    'meta_title' => $product->meta_title,
                    'meta_description' => $product->meta_description,
                    'meta_keywords' => $product->meta_keywords,
                    'created_at' => $product->created_at?->toIso8601String(),
                    'updated_at' => $product->updated_at?->toIso8601String(),
                ],
            ];

            $response = Http::timeout(5)
                ->withHeaders([
                    'X-Sync-Key' => $syncKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($syncUrl, $payload);

            if ($response->successful()) {
                Log::info('Product synced to Nest.js successfully', [
                    'product_id' => $product->id,
                    'action' => $action,
                    'status' => $response->status(),
                ]);
            } else {
                Log::warning('Product sync to Nest.js failed', [
                    'product_id' => $product->id,
                    'action' => $action,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Product sync to Nest.js throw exception', [
                'product_id' => $product->id,
                'action' => $action,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
