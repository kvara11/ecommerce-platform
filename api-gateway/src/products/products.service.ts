import { Injectable, Inject } from '@nestjs/common';
import { Repository } from 'typeorm';
import { InjectRepository } from '@nestjs/typeorm';
import { CACHE_MANAGER } from '@nestjs/cache-manager';
import type { Cache } from 'cache-manager';
import { createHash } from 'crypto';
import { ProductEntity } from './entities/product.entity';
import { CreateProductDto } from './dto/create-product.dto';
import { UpdateProductDto } from './dto/update-product.dto';
import { ProductFilterDto } from './dto/product-filter.dto';

@Injectable()
export class ProductService {

  private readonly cacheTtl = parseInt(process.env.CACHE_TTL_SECONDS ?? '60', 10);

  constructor(

    @InjectRepository(ProductEntity)
    private readonly productRepository: Repository<ProductEntity>,
    
    @Inject(CACHE_MANAGER)
    private readonly cacheManager: Cache,
  
  ) {}

  private getListCacheKey(filters: ProductFilterDto): string {
    const hash = createHash('md5')
      .update(JSON.stringify(filters))
      .digest('hex');
    return `products:list:${hash}`;
  }

  private getItemCacheKey(id: number): string {
    return `products:item:${id}`;
  }

  async findAll(filters: ProductFilterDto) {

    const cacheKey = this.getListCacheKey(filters);
    const cached = await this.cacheManager.get(cacheKey);
    if (cached) {
      return cached;
    }

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

    const result = {
      data,
      pagination: {
        total,
        page: filters.page ?? 1,
        limit: filters.limit ?? 20,
        pages: Math.ceil(total / (filters.limit ?? 20)),
      },
    };
    
    await this.cacheManager.set(cacheKey, result, this.cacheTtl * 1000).catch(err => {
      console.error('Error setting cache:', err);
    });

    return result;
  }

  async findOne(id: number) {
    const cacheKey = this.getItemCacheKey(id);

    const cached = await this.cacheManager.get(cacheKey);
    if (cached) {
      return cached;
    }

    const product = await this.productRepository.findOne({
      where: { id, is_active: true },
    });

    if (product) {
      await this.cacheManager.set(cacheKey, product, this.cacheTtl * 1000);
    }

    return product;
  }

  async create(createProductDto: CreateProductDto) {
    const product = this.productRepository.create(createProductDto);
    const saved = await this.productRepository.save(product);

    await this.invalidateListCaches();

    return saved;
  }

  async update(id: number, updateProductDto: UpdateProductDto) {
    await this.productRepository.update(id, updateProductDto);

    await this.cacheManager.del(this.getItemCacheKey(id));
    await this.invalidateListCaches();

    const updated = await this.productRepository.findOne({ where: { id } });
    return updated;
  }

  async remove(id: number) {

    await this.productRepository.update(id, { is_active: false });

    await this.cacheManager.del(this.getItemCacheKey(id));
    await this.invalidateListCaches();

    return { id, message: 'Product deleted' };
  }

  private async invalidateListCaches(): Promise<void> {
    const store = (this.cacheManager as any).stores?.[0];
    const keys = await store?.keys?.();
    if (keys) {
      const listKeys = keys.filter(key => key.startsWith('products:list:'));
      for (const key of listKeys) {
        await this.cacheManager.del(key);
      }
    }
  }
}
