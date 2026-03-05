import { BadRequestException, Body, Controller, Post } from '@nestjs/common';
import { AuthService } from './auth.service';
import { LoginDto } from './dto/login.dto';
import { RefreshDto } from './dto/refresh.dto';
import { VerifyTokenDto } from './dto/verify-token.dto';
import { AuthResponseDto } from './dto/auth-response.dto';

/**
 * AuthController - Endpoints for authentication
 *
 * Shared with Laravel:
 * - POST /auth/login -> returns { accessToken, refreshToken, expiresIn }
 * - POST /auth/refresh -> returns { accessToken, refreshToken, expiresIn }
 * - POST /auth/verify -> returns decoded token payload
 *
 * Token Format (HS256):
 * { sub: userId, email, role: 'admin'|'customer', role_id, iat, exp, iss, aud }
 */
@Controller('auth')
export class AuthController {
  constructor(private readonly authService: AuthService) {}

  /**
   * POST /auth/login
   * Authenticate user with email and password
   * Returns: { accessToken, refreshToken, expiresIn }
   */
  @Post('login')
  async login(@Body() loginDto: LoginDto): Promise<AuthResponseDto> {
    
    return this.authService.login(loginDto);
  }

  /**
   * POST /auth/refresh
   * Refresh access token using valid refresh token
   * Returns: { accessToken, refreshToken (rotated), expiresIn }
   * Body: { refreshToken }
   */
  @Post('refresh')
  async refresh(@Body() refreshDto: RefreshDto): Promise<AuthResponseDto> {
    return this.authService.refresh(refreshDto.refreshToken);
  }

  /**
   * POST /auth/verify
   * Verify and decode JWT token (without refresh)
   * Returns: token payload { sub, email, role, role_id, iat, exp, iss, aud }
   */
  @Post('verify')
  async verify(@Body() verifyTokenDto: VerifyTokenDto) {
    return this.authService.verifyToken(verifyTokenDto.token);
  }
}
