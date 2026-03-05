import { Body, Controller, Post } from '@nestjs/common';
import { AuthService } from './auth.service';
import { LoginDto } from './dto/login.dto';
import { RefreshDto } from './dto/refresh.dto';
import { VerifyTokenDto } from './dto/verify-token.dto';
import { AuthResponseDto } from './dto/auth-response.dto';


@Controller('auth')
export class AuthController {
  constructor(private readonly authService: AuthService) {}


  @Post('login')
  async login(@Body() loginDto: LoginDto): Promise<AuthResponseDto> {
    
    return this.authService.login(loginDto);
  }


  @Post('refresh')
  async refresh(@Body() refreshDto: RefreshDto): Promise<AuthResponseDto> {
    return this.authService.refresh(refreshDto.refreshToken);
  }


  @Post('verify')
  async verify(@Body() verifyTokenDto: VerifyTokenDto) {
    return this.authService.verifyToken(verifyTokenDto.token);
  }
}
