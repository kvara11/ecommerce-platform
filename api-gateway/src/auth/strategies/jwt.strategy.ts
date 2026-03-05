import { Injectable } from '@nestjs/common';
import { PassportStrategy } from '@nestjs/passport';
import { ExtractJwt, Strategy } from 'passport-jwt';
import { Role } from '../enums/role.enum';


// JwtStrategy - Validates JWT tokens using HS256 algorithm
 

@Injectable()
export class JwtStrategy extends PassportStrategy(Strategy) {
  
  constructor() {
  
    super({
      jwtFromRequest: ExtractJwt.fromAuthHeaderAsBearerToken(),
      ignoreExpiration: false,
      secretOrKey: process.env.SHARED_JWT_SECRET || 'ecommerce-secret-key',
      issuer: process.env.JWT_ISSUER || 'ecommerce-platform',
      audience: process.env.JWT_AUDIENCE || 'ecommerce-clients',
    });
  }


  validate(payload: {
    sub: number;
    email: string;
    role: Role;
    role_id: number;
    iat: number;
    exp: number;
  }) {
    return payload;
  }
}

