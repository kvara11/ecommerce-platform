import { Injectable, CanActivate, ExecutionContext, UnauthorizedException } from '@nestjs/common';
import { ConfigService } from '@nestjs/config';
import { Request } from 'express';


@Injectable()
export class SyncKeyGuard implements CanActivate {
    
  constructor(private configService: ConfigService) {}

  canActivate(context: ExecutionContext): boolean {
    const request = context.switchToHttp().getRequest<Request>();
    const syncKeyHeader = request.headers['x-sync-key'] as string;
    const expectedSyncKey = this.configService.get<string>('SYNC_KEY');

    if (!expectedSyncKey) {
      throw new UnauthorizedException('Sync authentication is not configured');
    }

    if (!syncKeyHeader || syncKeyHeader !== expectedSyncKey) {
      throw new UnauthorizedException('Invalid sync key');
    }

    return true;
  }
}
