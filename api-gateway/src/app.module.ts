import { Module } from '@nestjs/common';
import { APP_INTERCEPTOR } from '@nestjs/core';
import { ConfigModule } from '@nestjs/config';
import { CacheModule } from '@nestjs/cache-manager';
import { TypeOrmModule } from '@nestjs/typeorm';
import { cacheConfig } from './config/cache.config';
import { AppController } from './app.controller';
import { AppService } from './app.service';
import { AuthModule } from './auth/auth.module';
import { RefreshTokenEntity } from './auth/entities/refresh-token.entity';
import { UsersModule } from './users/users.module';
import { ProductsModule } from './products/products.module';
import { OrdersModule } from './orders/orders.module';
import { WebhooksModule } from './webhooks/webhooks.module';
import { ProductEntity } from './products/entities/product.entity';
import { OrderEntity } from './orders/entities/order.entity';
import { OrderItemEntity } from './orders/entities/order-item.entity';
import { RoleEntity } from './users/entities/role.entity';
import { UserEntity } from './users/entities/user.entity';
import { AutoRefreshJwtInterceptor } from './common/interceptors/auto-refresh-jwt.interceptor';

@Module({
  imports: [
    ConfigModule.forRoot({ isGlobal: true }),
    CacheModule.registerAsync(cacheConfig),

    TypeOrmModule.forRoot({
      type: 'postgres',
      host: process.env.DB_HOST ?? 'localhost',
      port: Number(process.env.DB_PORT ?? 5432),
      username: process.env.DB_USERNAME,
      password: process.env.DB_PASSWORD,
      database: process.env.DB_DATABASE,
      entities: [
        UserEntity,
        RoleEntity,
        RefreshTokenEntity,
        ProductEntity,
        OrderEntity,
        OrderItemEntity,
      ],
      synchronize: false,
    }),
    UsersModule,
    AuthModule,
    ProductsModule,
    OrdersModule,
    WebhooksModule,
  ],
  controllers: [AppController],
  providers: [
    AppService,
    {
      provide: APP_INTERCEPTOR,
      useClass: AutoRefreshJwtInterceptor,
    },
  ],
})
export class AppModule { }
