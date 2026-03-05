import {
  CallHandler,
  ExecutionContext,
  Injectable,
  NestInterceptor,
  UnauthorizedException,
} from '@nestjs/common';
import { Observable, throwError, from } from 'rxjs';
import { catchError, tap, switchMap } from 'rxjs/operators';
import { AuthService } from '../../auth/auth.service';

//  AutoRefreshJwtInterceptor Automatically refreshes expired access tokens using refresh tokens.

@Injectable()
export class AutoRefreshJwtInterceptor implements NestInterceptor {
  constructor(private readonly authService: AuthService) { }

  intercept(context: ExecutionContext, next: CallHandler): Observable<any> {
    const request = context.switchToHttp().getRequest();
    const response = context.switchToHttp().getResponse();

    return next.handle().pipe(
      catchError((err) => {
        // Check if error is due to expired token
        if (err.name === 'TokenExpiredError' && err.message.includes('expired')) {
          // Try to get refresh token from header or cookie
          const refreshToken =
            request.headers['x-refresh-token'] ||
            request.cookies?.['x-refresh-token'];

          if (!refreshToken) {
            return throwError(
              () =>
                new UnauthorizedException(
                  'Access token expired and no refresh token provided',
                ),
            );
          }

          // Attempt to refresh token
          return from(this.authService.refresh(refreshToken)).pipe(
            tap((newAuthResponse) => {
              response.setHeader('x-access-token', newAuthResponse.accessToken);
              const decoded = this.authService.decodeToken(
                newAuthResponse.accessToken,
              );
              request.user = decoded;
            }),
            switchMap(() => next.handle()),
            catchError((refreshErr) =>
              throwError(
                () =>
                  new UnauthorizedException(
                    'Invalid or expired refresh token: ' + refreshErr.message,
                  ),
              ),
            ),
          );
        }

        return throwError(() => err);
      }),
    );
  }
}
