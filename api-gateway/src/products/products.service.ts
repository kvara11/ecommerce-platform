import { Injectable, Inject } from '@nestjs/common';
import { Repository } from 'typeorm';
import { InjectRepository } from '@nestjs/typeorm';
import { CACHE_MANAGER } from '@nestjs/cache-manager';
import type { Cache } from 'cache-manager';
import { createHash } from 'crypto';
import type { RedisClientType } from 'redis';
import { ProductEntity } from './entities/product.entity';
import { CreateProductDto } from './dto/create-product.dto';
import { UpdateProductDto } from './dto/update-product.dto';
import { ProductFilterDto } from './dto/product-filter.dto';

export interface ProductListResult {
  data: ProductEntity[];
  pagination: {
    total: number;
    page: number;
    limit: number;
    pages: number;
  };
}

@Injectable()
export class ProductService {

  private readonly cacheTtl: number;

  constructor(
    @InjectRepository(ProductEntity)
    private readonly productRepository: Repository<ProductEntity>,

    @Inject(CACHE_MANAGER)
    private readonly cacheManager: Cache,

    @Inject('REDIS_CLIENT')
    private readonly redisClient: RedisClientType,
  ) {

    this.cacheTtl = 0;
  }

  private getListCacheKey(filters: ProductFilterDto): string {
    const normalized = {
      search: filters.search || '',
      categoryId: filters.categoryId || 0,
      minPrice: filters.minPrice ?? 0,
      maxPrice: filters.maxPrice ?? 0,
      page: filters.page ?? 1,
      limit: filters.limit ?? 20,
    };
    const hash = createHash('md5')
      .update(JSON.stringify(normalized))
      .digest('hex');
    return `products:list:${hash}`;
  }

  private getItemCacheKey(id: number): string {
    return `product:item:${id}`;
  }

  private async fetchProductsFromDb(filters: ProductFilterDto): Promise<ProductListResult> {
    const query = this.productRepository
      .createQueryBuilder('product')
      .where('product.is_active = :isActive', { isActive: true });

    if (filters.search) {
      query.andWhere(
        '(product.name ILIKE :search OR product.description ILIKE :search)',
        { search: `%${filters.search}%` },
      );
    }

    if (filters.categoryId) {
      query.andWhere('product.category_id = :categoryId', {
        categoryId: filters.categoryId,
      });
    }

    if (filters.minPrice !== undefined) {
      query.andWhere('product.price >= :minPrice', {
        minPrice: filters.minPrice,
      });
    }

    if (filters.maxPrice !== undefined) {
      query.andWhere('product.price <= :maxPrice', {
        maxPrice: filters.maxPrice,
      });
    }

    const skip = ((filters.page ?? 1) - 1) * (filters.limit ?? 20);
    query.skip(skip).take(filters.limit ?? 20).orderBy('product.created_at', 'DESC');

    const [data, total] = await query.getManyAndCount();

    return {
      data,
      pagination: {
        total,
        page: filters.page ?? 1,
        limit: filters.limit ?? 20,
        pages: Math.ceil(total / (filters.limit ?? 20)),
      },
    };
  }



  async findAll(filters: ProductFilterDto): Promise<ProductListResult> {

    const cacheKey = this.getListCacheKey(filters);
    const cached = await this.cacheManager.get<ProductListResult>(cacheKey);

    if (cached) {
      return cached;
    }

    const result = await this.fetchProductsFromDb(filters);
    await this.cacheManager.set(cacheKey, result, this.cacheTtl);

    return result;
  }


  async findOne(id: number): Promise<ProductEntity | null> {
    const cacheKey = this.getItemCacheKey(id);
    const cached = await this.cacheManager.get<ProductEntity>(cacheKey);

    if (cached) {
      return cached;
    }

    const product = await this.productRepository.findOne({
      where: { id, is_active: true },
    });

    if (product) {
      await this.cacheManager.set(cacheKey, product, this.cacheTtl);
    }

    return product;
  }

  async create(createProductDto: CreateProductDto): Promise<ProductEntity> {

    const product = this.productRepository.create(createProductDto);
    const saved = await this.productRepository.save(product);
    await this.invalidateProductListCaches();
    
    return saved;
  }

  async update(
    id: number,
    updateProductDto: UpdateProductDto,
  ): Promise<ProductEntity | null> {

    await this.productRepository.update(id, updateProductDto);
    await this.cacheManager.del(this.getItemCacheKey(id));
    await this.invalidateProductListCaches();
    const updated = await this.productRepository.findOne({ where: { id } });
    return updated;
  }

  async remove(id: number): Promise<{ id: number; message: string }> {

    await this.productRepository.update(id, { is_active: false });
    await this.cacheManager.del(this.getItemCacheKey(id));
    await this.invalidateProductListCaches();
    
    return { id, message: 'Product deleted' };
  }

  async invalidateProductListCaches(): Promise<void> {
    let cursor: string | number = 0;
    const batchSize = 100;

    do {
      const result = await this.redisClient.scan(String(cursor), {
        MATCH: 'products:list:*',
        COUNT: batchSize,
      });

      cursor = result.cursor;
      const keys = result.keys;

      if (keys.length > 0) {
        await this.redisClient.del(keys);
      }
    } while (cursor !== '0');
  }
}
