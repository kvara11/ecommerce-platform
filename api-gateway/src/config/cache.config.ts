import { CacheModuleAsyncOptions } from '@nestjs/cache-manager';
import { createKeyv } from '@keyv/redis';

export const cacheConfig: CacheModuleAsyncOptions = {
  isGlobal: true,
  useFactory: (): {
    ttl: number;
    stores: ReturnType<typeof createKeyv>[];
  } => ({
    ttl: Number(process.env.CACHE_TTL_SECONDS ?? '300') * 1000,
    stores: [
      createKeyv(
        `redis://${process.env.REDIS_HOST ?? 'localhost'}:${Number(process.env.REDIS_PORT ?? 6379)}`,
      ),
    ],
  }),
};
