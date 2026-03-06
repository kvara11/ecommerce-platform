import { Module } from '@nestjs/common';
import { TypeOrmModule } from '@nestjs/typeorm';
import { RedisModule } from '../redis/redis.module';
import { ProductsController } from './products.controller';
import { ProductService } from './products.service';
import { ProductEntity } from './entities/product.entity';

@Module({
  imports: [
    TypeOrmModule.forFeature([ProductEntity]),
    RedisModule,
  ],
  controllers: [ProductsController],
  providers: [ProductService],
  exports: [ProductService],
})
export class ProductsModule {}
