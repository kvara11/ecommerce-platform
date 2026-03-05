import {
  BadRequestException,
  Injectable,
  UnauthorizedException,
} from '@nestjs/common';
import { JwtService } from '@nestjs/jwt';
import { Repository } from 'typeorm';
import { InjectRepository } from '@nestjs/typeorm';
import { compare, hash } from 'bcryptjs';
import { randomBytes } from 'crypto';
import { LoginDto } from './dto/login.dto';
import { AuthResponseDto } from './dto/auth-response.dto';
import { Role } from './enums/role.enum';
import { RefreshTokenEntity } from './entities/refresh-token.entity';
import { UsersService } from '../users/users.service';



@Injectable()
export class AuthService {

  private readonly accessTokenExpiration = Number(process.env.JWT_ACCESS_TOKEN_EXPIRES_IN ?? 900); // 15 minutes
  private readonly refreshTokenExpiration = Number(process.env.JWT_REFRESH_TOKEN_EXPIRES_IN ?? 604800); // 7 days

  constructor(

    private readonly jwtService: JwtService,
    private readonly usersService: UsersService,
    
    @InjectRepository(RefreshTokenEntity)
    private readonly refreshTokenRepository: Repository<RefreshTokenEntity>,
  
  ) {}


  async login(loginDto: LoginDto): Promise<AuthResponseDto> {

    const user = await this.usersService.findByEmail(loginDto.email);

    if (!user) {
      throw new UnauthorizedException('Invalid credentials');
    }

    if (!user.is_active) {
      throw new UnauthorizedException('User is inactive');
    }

    const isPasswordValid = await compare(loginDto.password, user.password);

    if (!isPasswordValid) {
      throw new UnauthorizedException('Invalid credentials');
    }

    const resolvedRole = this.usersService.resolveRole(user.role?.name);

    if (loginDto.role && loginDto.role !== resolvedRole) {
      throw new UnauthorizedException('Role mismatch for this account');
    }

    const payload = {
      sub: user.id,
      email: user.email,
      role: resolvedRole,
      role_id: user.role_id,
    };

    const accessToken = await this.jwtService.signAsync(payload, {
      expiresIn: this.accessTokenExpiration,
    });

    const refreshToken = await this.generateRefreshToken(user.id);

    return {
      accessToken,
      refreshToken,
      expiresIn: this.accessTokenExpiration,
    };
  }


  async refresh(refreshToken: string): Promise<AuthResponseDto> {
    const storedToken = await this.validateRefreshToken(refreshToken);

    if (!storedToken) {
      throw new UnauthorizedException('Invalid or expired refresh token');
    }

    const user = await this.usersService.findByEmail(storedToken.user.email);

    if (!user || !user.is_active) {
      throw new UnauthorizedException('User is inactive');
    }

    const resolvedRole = this.usersService.resolveRole(user.role?.name);

    const payload = {
      sub: user.id,
      email: user.email,
      role: resolvedRole,
      role_id: user.role_id,
    };

    const newAccessToken = await this.jwtService.signAsync(payload, {
      expiresIn: this.accessTokenExpiration,
    });

    await this.refreshTokenRepository.update(
      { id: storedToken.id },
      { is_revoked: true },
    );

    const newRefreshToken = await this.generateRefreshToken(user.id);

    return {
      accessToken: newAccessToken,
      refreshToken: newRefreshToken,
      expiresIn: this.accessTokenExpiration,
    };
  }


  private async generateRefreshToken(userId: number): Promise<string> {
    
    const refreshToken = randomBytes(32).toString('hex');
    const tokenHash = await hash(refreshToken, 10);
    const expiresAt = new Date(
      Date.now() + this.refreshTokenExpiration * 1000,
    );

    await this.refreshTokenRepository.save({
      user_id: userId,
      token_hash: tokenHash,
      expires_at: expiresAt,
      is_revoked: false,
    });

    return refreshToken;
  }


  private async validateRefreshToken(
    refreshToken: string,
  ): Promise<RefreshTokenEntity | null> {

    const tokens = await this.refreshTokenRepository
      .createQueryBuilder('rt')
      .leftJoinAndSelect('rt.user', 'user', 'user.id = rt.user_id')
      .leftJoinAndSelect('user.role', 'role', 'role.id = user.role_id')
      .where('rt.is_revoked = false')
      .andWhere('rt.expires_at > :now', { now: new Date() })
      .getMany();

    for (const token of tokens) {
      const isValid = await compare(refreshToken, token.token_hash);
      if (isValid) {
        return token;
      }
    }

    return null;
  }


  async revokeRefreshToken(refreshToken: string): Promise<void> {

    const storedToken = await this.validateRefreshToken(refreshToken);
    
    if (storedToken) {
      await this.refreshTokenRepository.update(
        { id: storedToken.id },
        { is_revoked: true },
      );
    }
  }


  async verifyToken(token: string): Promise<{
    sub: number;
    email: string;
    role: Role;
    role_id: number;
  }> {
    try {
      return await this.jwtService.verifyAsync<{
        sub: number;
        email: string;
        role: Role;
        role_id: number;
      }>(token, {
        secret: process.env.SHARED_JWT_SECRET || 'ecommerce-secret-key',
        issuer: process.env.JWT_ISSUER || 'ecommerce-platform',
        audience: process.env.JWT_AUDIENCE || 'ecommerce-clients',
      });

    } catch {
      throw new UnauthorizedException('Invalid or expired token');
    }
  }

  /**
   * Decode JWT without verification --only for extracting payload after verification
   */
  decodeToken(token: string): any {
    try {
      return this.jwtService.decode(token);

    } catch {
      throw new BadRequestException('Invalid token format');
    }
  }
}
