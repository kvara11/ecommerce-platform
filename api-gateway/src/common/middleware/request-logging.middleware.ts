import { Injectable, NestMiddleware, Logger } from '@nestjs/common';
import { Request, Response, NextFunction } from 'express';

@Injectable()
export class RequestLoggingMiddleware implements NestMiddleware {
  private readonly logger = new Logger('HTTP');

  use(req: Request, res: Response, next: NextFunction) {
    const startTime = Date.now();

    res.on('finish', () => {
      const durationMs = Date.now() - startTime;
      const userId = (req.user as any)?.userId ?? (req.user as any)?.sub ?? '-';
      const log = `[REQ] ${req.method} ${req.originalUrl} ${res.statusCode} ${durationMs}ms user=${userId}`;
      this.logger.log(log);
    });

    next();
  }
}
