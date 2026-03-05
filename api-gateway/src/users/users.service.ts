import { Injectable } from '@nestjs/common';
import { UserEntity } from './entities/user.entity';
import { UsersRepository } from './users.repository';
import { Role } from '../auth/enums/role.enum';

@Injectable()
export class UsersService {
  
  constructor(private readonly usersRepository: UsersRepository) {}

  findByEmail(email: string): Promise<UserEntity | null> {
    return this.usersRepository.findByEmail(email);
  }

  findById(id: number): Promise<UserEntity | null> {
    return this.usersRepository.findById(id);
  }

  create(payload: Partial<UserEntity>): Promise<UserEntity> {
    return this.usersRepository.createUser(payload);
  }

  resolveRole(roleName: string | null | undefined): Role {
    const normalized = (roleName ?? '').toLowerCase();
    return normalized.includes('admin') ? Role.ADMIN : Role.CUSTOMER;
  }
}
