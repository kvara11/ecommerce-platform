import { Injectable } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';
import { UserEntity } from './entities/user.entity';

@Injectable()
export class UsersRepository {
  constructor(
    @InjectRepository(UserEntity)
    private readonly repository: Repository<UserEntity>,
  ) {}

  findByEmail(email: string): Promise<UserEntity | null> {
    return this.repository.findOne({
      where: { email },
      relations: ['role'],
    });
  }

  findById(id: number): Promise<UserEntity | null> {
    return this.repository.findOne({
      where: { id },
      relations: ['role'],
    });
  }

  createUser(payload: Partial<UserEntity>): Promise<UserEntity> {
    const user = this.repository.create(payload);
    return this.repository.save(user);
  }
}
