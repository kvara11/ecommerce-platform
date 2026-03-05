import { Module } from '@nestjs/common';
import { TypeOrmModule } from '@nestjs/typeorm';
import { JwtModule } from '@nestjs/jwt';
import { PassportModule } from '@nestjs/passport';
import { AuthService } from './auth.service';
import { AuthController } from './auth.controller';
import { JwtStrategy } from './strategies/jwt.strategy';
import { RolesGuard } from './guards/roles.guard';
import { UsersModule } from '../users/users.module';
import { RefreshTokenEntity } from './entities/refresh-token.entity';
import { AutoRefreshJwtInterceptor } from '../common/interceptors/auto-refresh-jwt.interceptor';


// Auth Module (JWT, Refresh Tokens)


const jwtAccessTokenExpiration = Number(process.env.JWT_ACCESS_TOKEN_EXPIRES_IN ?? 900); // 15 minutes

const jwtSecret = process.env.SHARED_JWT_SECRET || 'ecommerce-secret-key';

@Module({
  imports: [
    UsersModule,
    PassportModule,
    TypeOrmModule.forFeature([RefreshTokenEntity]),
    JwtModule.register({
      secret: jwtSecret,
      signOptions: {
        issuer: process.env.JWT_ISSUER || 'ecommerce-platform',
        audience: process.env.JWT_AUDIENCE || 'ecommerce-clients',
        expiresIn: Number.isNaN(jwtAccessTokenExpiration) ? 900 : jwtAccessTokenExpiration,
      },
    }),
  ],
  controllers: [AuthController],
  providers: [AuthService, JwtStrategy, RolesGuard, AutoRefreshJwtInterceptor],
  exports: [AuthService, JwtStrategy, RolesGuard, AutoRefreshJwtInterceptor],
})
export class AuthModule {}
