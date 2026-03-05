import { IsJWT, IsNotEmpty, IsString } from 'class-validator';

export class VerifyTokenDto {
  @IsString()
  @IsNotEmpty()
  @IsJWT()
  token: string;
}
