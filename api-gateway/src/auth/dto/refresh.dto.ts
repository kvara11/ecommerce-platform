import { IsNotEmpty, IsString } from 'class-validator';

/**
 * RefreshDto - DTO for refresh token endpoint
 * Contains the refresh token issued by POST /auth/login
 */
export class RefreshDto {
  @IsNotEmpty()
  @IsString()
  refreshToken: string;
}
