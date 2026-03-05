import { IsIn, IsNotEmpty, IsOptional, IsString } from 'class-validator';
import { Role } from '../enums/role.enum';
import { IsEmail } from 'class-validator';

export class LoginDto {
  @IsEmail()
  @IsNotEmpty()
  email: string;

  @IsString()
  @IsNotEmpty()
  password: string;

  @IsOptional()
  @IsString()
  @IsIn([Role.ADMIN, Role.CUSTOMER])
  role?: Role;
}
