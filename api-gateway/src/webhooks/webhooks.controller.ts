import { Controller, Post, Body, UseGuards, HttpCode, HttpStatus, Inject } from '@nestjs/common';
import { SyncKeyGuard } from '../common/guards/sync-key.guard';
import { CACHE_MANAGER } from '@nestjs/cache-manager';
import type { Cache } from 'cache-manager';
import { ProductService } from '../products/products.service';

interface SyncProductDto {
    action: 'saved' | 'deleted';
    product: {
        id: number;
        category_id: number;
        sku: string;
        name: string;
        slug: string;
        description: string;
        short_description: string;
        price: string;
        cost_price: string;
        is_active: boolean;
        meta_title: string;
        meta_description: string;
        meta_keywords: string;
        created_at: string;
        updated_at: string;
    };
}


@Controller('api/webhooks')
export class WebhooksController {

    constructor(
        @Inject(CACHE_MANAGER) private cacheManager: Cache,
        private productService: ProductService,
    ) { }


    @Post('sync')
    @UseGuards(SyncKeyGuard)
    @HttpCode(HttpStatus.OK)
    async syncProduct(@Body() syncData: SyncProductDto) {

        const { action, product } = syncData;

        console.log(`[WebhookSync] Received ${action} event for product ID ${product.id}`);

        try {
            if (action === 'deleted') {
                await this.invalidateProductCache(product.id);
                console.log(`[WebhookSync] Deleted product ${product.id} from cache`);
            } else if (action === 'saved' || action === 'updated') {
                await this.updateProductCache(product);
                console.log(`[WebhookSync] Updated product ${product.id} in cache`);
            }

            await this.productService.invalidateProductListCaches();


            return {
                success: true,
                message: `Product ${action} sync completed`,
                productId: product.id,
            };
        } catch (error) {
            console.error('[WebhookSync] Error processing sync:', error);
            // Return success to prevent Laravel from retrying indefinitely
            return {
                success: false,
                message: 'Sync processing failed but acknowledged',
                error: error instanceof Error ? error.message : 'Unknown error',
            };
        }
    }

    private async invalidateProductCache(productId: number): Promise<void> {
        try {

            const productKey = `products:item:${productId}`;
            await this.cacheManager.del(productKey);

        } catch (error) {
            console.error('[WebhookSync] Error invalidating cache:', error);
            throw error;
        }
    }


    private async updateProductCache(product: SyncProductDto['product']): Promise<void> {
        try {

            const productKey = `products:item:${product.id}`;
            await this.cacheManager.set(productKey, product);

        } catch (error) {
            console.error('[WebhookSync] Error updating cache:', error);
            throw error;
        }
    }
}
