import { Module } from '@nestjs/common';
import { WebhooksController } from './webhooks.controller';
import { ProductsModule } from '../products/products.module';

/**
 * WebhooksModule
 * 
 * Handles incoming webhooks from external services (Laravel admin-service)
 */
@Module({
  imports: [ProductsModule],
  controllers: [WebhooksController],
})
export class WebhooksModule {}
