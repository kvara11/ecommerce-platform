# Ecommerce Platform - Complete Documentation

## Table of Contents
1. [Setup Instructions](#setup-instructions)
2. [API Documentation](#api-documentation)
3. [Design Decisions](#design-decisions)
4. [Architecture Overview](#architecture-overview)

---

## Setup Instructions

### Prerequisites
- Docker & Docker Compose
- PostgreSQL 16 (via Docker)
- Redis 7 (via Docker)
- Node.js 20 (for local development)
- PHP 8.2 with Laravel (for local development)

### 1. Initial Setup from Fresh Clone

After downloading the project for the first time:

```bash
cd ecommerce-platform

# Build all services and start containers
docker compose up -d --build

# Wait for services to be healthy (approx 30-60 seconds)
# Services will be booting during this time - check logs if needed
```

### 2. Database Setup

#### Reset Database & Run Seeds
The seeds will populate sample data for testing:

```bash
# Laravel/Admin Service - Database migration and seeding
docker exec ecommerce_admin_service php artisan migrate:fresh --seed

# This creates:
# - Users (admin user + sample customers)
# - Products (sample products with images and inventory)
# - Categories (product categories)
# - Order Statuses
# - Payment Statuses & Methods
# - Sample Orders (for testing)
```

#### Clear Laravel Cache
After migrations, clear any cached configurations:

```bash
docker exec ecommerce_admin_service php artisan config:clear
docker exec ecommerce_admin_service php artisan cache:clear
```

### 3. Service Access

Once containers are running:

| Service | URL | Port | Purpose |
|---------|-----|------|---------|
| Admin Panel (Laravel + Vue 3) | `http://localhost:8080` | 8080 | Dashboard, Products, Orders, Users, Categories management |
| API Gateway (NestJS) | `http://localhost:3000` | 3000 | REST API for frontend consumption (via nginx proxy) |
| Laravel API | `http://localhost:8080/api/` | 8080 (nginx proxy) | Auth tokens, Token management |
| Redis CLI | via container | 6379 | Cache, Sessions, Queues |
| PostgreSQL | localhost:5433 | 5433 | Primary database |

### 4. Development Workflow

#### Hot Reload Frontend Assets
For Vue/CSS changes in admin-service:

```bash
# Terminal 1: Watch mode for Vite
docker exec -it ecommerce_admin_service npm run devRewrite this README.md to sound more natural and human-written.

Important requirements:

1. Keep all technical information and structure, but improve the tone so it sounds like it was written by a developer documenting their own project, not generated documentation.

2. The tone should be:
   - professional but natural
   - slightly conversational
   - clear and easy to read
   - not overly formal or robotic

3. Do NOT remove important technical sections like:
   - Setup Instructions
   - API Documentation
   - Architecture
   - Design Decisions
   - Docker setup
   - Environment variables

4. Improve readability:
   - shorten overly long explanations
   - remove repetitive phrases
   - simplify overly formal language
   - keep code blocks unchanged

5. Add small human touches where appropriate, for example:
   - short explanations of why certain decisions were made
   - notes about trade-offs or time constraints
   - developer tips for running the project

6. Keep markdown formatting clean and simple.

7. Do NOT make the document too long. Prefer clarity over excessive detail.

8. This project was built as a backend technical assessment in about one week, so the README should reflect that context.

Output the full improved README.md.

# Terminal 2: In another terminal, rebuild on file changes
docker compose up -d --build
```

#### Build Frontend Assets
Deploy static assets (production-ready):

```bash
docker exec ecommerce_admin_service npm run build
```

#### NestJS Development
API-gateway runs in watch mode automatically via `npm run start:dev`:

```bash
# Logs are visible in docker logs
docker logs -f ecommerce_api_gateway
```

#### View Logs
Monitor all services:

```bash
# All services
docker compose logs -f

# Specific service
docker compose logs -f api-gateway
docker compose logs -f admin-service
docker compose logs -f db
docker compose logs -f redis

# Last N lines
docker compose logs --tail=50 api-gateway
```

### 5. Redis Management

Access Redis CLI for cache inspection:

```bash
docker exec -it ecommerce_redis redis-cli

# Commands:
DBSIZE                    # See number of keys
KEYS *                    # List all keys
GET key_name              # Get value
FLUSHDB                   # Clear current database
```

### 6. Useful Docker Commands

```bash
# Start services
docker compose up -d

# Stop services (keeps data)
docker compose down

# Full rebuild
docker compose down && docker compose up -d --build

# Remove all volumes (⚠️ deletes database)
docker compose down -v

# Stop a single service
docker compose stop api-gateway

# Restart a service
docker compose restart admin-service

# Execute command in container
docker exec [container_name] [command]

# Shell access
docker exec -it ecommerce_admin_service sh
docker exec -it ecommerce_api_gateway bash
```

### 7. Environment Variables

#### Admin Service `.env`
```yaml
APP_ENV=local              # Development environment
APP_DEBUG=true             # Enable detailed errors

# Database
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=ecommerce
DB_USERNAME=ecommerce
DB_PASSWORD=ecommerce

# Redis (Sessions, Cache, Queues)
REDIS_HOST=redis
REDIS_PORT=6379
REDIS_CACHE_DB=1           # Database 1 for cache
REDIS_SESSION_DB=0         # Database 0 for sessions

# JWT Shared with API Gateway
JWT_SECRET=hAjYgM4VFuEQfFqyWbXHd2T7BwHpH40MFJ6T5LATvp8
JWT_ALGORITHM=HS256
JWT_ISSUER=ecommerce-platform
JWT_AUDIENCE=ecommerce-clients
JWT_ACCESS_TOKEN_EXPIRES=900         # 15 minutes
JWT_REFRESH_TOKEN_EXPIRES=604800     # 7 days

# NestJS Webhooks Synchronization
SYNC_KEY=ZkItkJHtZxWGDgBpxj7TLqiYCetwBDoLPvL/h116zdo=
NESTJS_SYNC_URL=http://api-gateway:3000/api/webhooks/sync
```

#### API Gateway `.env`
```yaml
# Database
DB_HOST=db
DB_PORT=5432
DB_DATABASE=ecommerce
DB_USERNAME=ecommerce
DB_PASSWORD=ecommerce

# JWT (Shared with Laravel)
JWT_SECRET=hAjYgM4VFuEQfFqyWbXHd2T7BwHpH40MFJ6T5LATvp8
JWT_ALGORITHM=HS256
JWT_ISSUER=ecommerce-platform
JWT_AUDIENCE=ecommerce-clients
JWT_ACCESS_TOKEN_EXPIRES=900
JWT_REFRESH_TOKEN_EXPIRES=604800

# Redis
REDIS_HOST=redis
REDIS_PORT=6379

# Caching
CACHE_TTL_SECONDS=0        # 0 = use Redis default or no cache

# Data Sync Key
SYNC_KEY=ZkItkJHtZxWGDgBpxj7TLqiYCetwBDoLPvL/h116zdo=
```

---

## API Documentation

### Base URLs
- **Admin/Web**: `http://localhost:8080` (Laravel routes)
- **NestJS API**: `http://localhost:8080/service-api/` (proxied via Nginx)
- **Direct NestJS**: `http://localhost:3000` (for direct access)

### Authentication

All API endpoints (except login) require JWT token in header:

```bash
Authorization: Bearer <jwt_token>
```

#### Login (Laravel - POST)
```
POST /api/token/generate
Content-Type: application/json

{
  "email": "admin@example.com",
  "password": "password"
}

Response:
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "refresh_token": "...",
  "expires_in": 900
}
```

#### Refresh Token (Laravel - POST)
```
POST /api/token/refresh
Authorization: Bearer <refresh_token>

Response: New access & refresh tokens
```

#### List Tokens (Laravel - GET)
```
GET /api/tokens
Authorization: Bearer <access_token>
```

#### Revoke All Tokens (Laravel - POST)
```
POST /api/token/revoke-all
Authorization: Bearer <access_token>
```

### NestJS API Endpoints

#### Products

**List Products** (GET)
```
GET /service-api/api/products
Query Parameters:
  - page: number (default: 1)
  - limit: number (default: 20)
  - search: string (optional)
  - category_id: number (optional)
  - min_price: number (optional)
  - max_price: number (optional)

Authorization: Required (ADMIN or CUSTOMER)
```

**Get Product** (GET)
```
GET /service-api/api/products/:id
Authorization: Required
```

**Create Product** (POST)
```
POST /service-api/api/products
Authorization: Required (ADMIN only)
Content-Type: application/json

{
  "category_id": 1,
  "sku": "PROD-001",
  "name": "Product Name",
  "slug": "product-name",
  "description": "Long description",
  "short_description": "Short description",
  "price": 99.99,
  "cost_price": 50.00,
  "is_active": true,
  "meta_title": "SEO Title",
  "meta_description": "SEO Description",
  "meta_keywords": "keyword1, keyword2"
}
```

**Update Product** (PUT)
```
PUT /service-api/api/products/:id
Authorization: Required (ADMIN only)
Content-Type: application/json
(Same fields as POST)
```

**Delete Product** (DELETE)
```
DELETE /service-api/api/products/:id
Authorization: Required (ADMIN only)
```

#### Orders

**List Orders** (GET)
```
GET /service-api/api/orders
Query Parameters:
  - userId: number (optional)
  - page: number (default: 1)
  - limit: number (default: 20)

Authorization: Required
```

**Get Order** (GET)
```
GET /service-api/api/orders/:id
Authorization: Required
```

**Create Order** (POST)
```
POST /service-api/api/orders
Authorization: Required
Content-Type: application/json

{
  "userId": 1,
  "totalAmount": 299.99,
  "items": [
    {
      "productId": 1,
      "quantity": 2
    },
    {
      "productId": 3,
      "quantity": 1
    }
  ]
}

Response:
{
  "id": 1,
  "user_id": 1,
  "total_amount": "299.99",
  "status": "created",
  "items": [
    {
      "id": 1,
      "order_id": 1,
      "product_id": 1,
      "quantity": 2,
      "unit_price": "99.99",
      "subtotal": "199.98",
      ...
    }
  ],
  "created_at": "2026-03-06T12:00:00Z"
}
```

### Laravel Admin Routes (Web)

Protected by `auth:sanctum`, `admin` middleware, and `throttle:admin` rate limit.

```
GET    /dashboard                 - Dashboard home
GET    /users                     - List users
POST   /users                     - Create user
PUT    /users/:id                 - Update user
PUT    /users/toggle-status/:id   - Active/Inactive user
DELETE /users/:id                 - Delete user

GET    /products                  - List products
POST   /products                  - Create product
PUT    /products/:id              - Update product
DELETE /products/:id              - Delete product

GET    /categories                - List categories
POST   /categories                - Create category
PUT    /categories/:id            - Update category
PUT    /categories/toggle-status/:id  - Active/Inactive category
DELETE /categories/:id            - Delete category

GET    /orders                    - List orders
GET    /orders/:id                - View order details
PUT    /orders/:id                - Update order status/notes
```

---

## Design Decisions

### 1. Microservices Architecture

**Strategy**: Two-service architecture with shared database and event-driven sync.

- **Admin Service (Laravel)**: Monolithic backend handling business logic, admin operations, and web UI
- **API Gateway (NestJS)**: High-performance REST API for frontend, inventory management, and order operations

**Rationale**:
- Laravel excels at rapid development with rich ecosystem (Sanctum, Jetstream, Eloquent)
- NestJS provides type-safe, scalable API layer with excellent performance
- Shared database ensures consistency without complex distributed transactions
- Event-driven architecture allows services to stay loosely coupled

### 2. Database & Synchronization

**Strategy**: Single PostgreSQL database with observer pattern sync.

**Laravel Observers**:
- `ProductObserver`: Listens to `save`, `update`, `delete` events on Product model
- Automatically syncs product changes to NestJS cache via HTTP webhook with signature verification
- Keeps Redis in sync for high-performance reads in API Gateway

```php
// When product is saved/updated/deleted in Laravel:
// 1. Observer captures the event
// 2. Calls NestJS webhook: POST /api/webhooks/sync
// 3. Includes SYNC_KEY signature for security
// 4. NestJS validates and updates Redis cache
```

**Future Implementation**:
- Order and Inventory observers can follow the same pattern
- Event sourcing for order state changes
- Async queue jobs for heavy operations

### 3. Caching Strategy (Redis)

**Redis Structure**:
```
DB 0: Session storage (SESSION_DB)
      - Laravel sessions
      - Redis expiration: session timeout

DB 1: Cache layer (CACHE_DB)
      - Product data (for API Gateway reads)
      - User role cache (for guards)
      - Order metadata (optional)
      
Command: SET cache_key value EX ttl
```

**Current Usage**:
- Products: Synced from Laravel when created/updated
- Sessions: Managed by Laravel for web UI
- Queues: Redis-backed queue driver for async jobs

**Scalability**:
- Can extend to cache orders, users, inventory
- Cache-aside pattern allows fallback to database
- TTL ensures eventual consistency

### 4. Authentication & Authorization

**JWT Strategy**: Shared secret between Laravel and NestJS

**Token Flow**:
```
1. User logs in via Laravel (/api/token/generate)
2. Laravel creates JWT with:
   - sub: user ID
   - email: user email
   - role: user role (admin/customer)
   - role_id: numeric role ID
   - iat, exp, iss, aud claims
3. Client stores JWT (access_token & refresh_token)
4. Client includes JWT in Authorization header for all requests
5. Both Laravel and NestJS validate using same JWT_SECRET
```

**Guards Implementation**:

**NestJS JwtAuthGuard**:
- Validates JWT signature using HS256
- Extracts claims and makes available via `@CurrentUser()` decorator
- Throws 401 Unauthorized if invalid/expired

**NestJS RolesGuard**:
- Used with `@Roles(Role.ADMIN)` decorator
- Checks user.role_id against required role
- Throws 403 Forbidden if unauthorized

**Laravel AdminMiddleware**:
- Checks if user role_id === 1 (admin)
- Returns 403 if not admin
- Logs user out for security

### 5. Rate Limiting & Throttling

**Laravel (`throttle:admin`)**:
```php
// Config in boot/app.php
$middleware->throttleApi('global')

// Routes protected with:
'throttle:admin'  // Custom rate limit for admin routes
```

**Bucketing**:
- Admin routes: Strict limits to prevent abuse
- Can extend to: `throttle:60,1` (60 requests per minute)
- Fallback to: `ThrottleRequests::class` for custom logic

**Redis Backend**:
- Laravel throttle uses cache (Redis) for state
- Distributed rate limiting across multiple requests
- Automatic cleanup of expired entries

**Future NestJS Implementation**:
- @nestjs/throttler package
- Memory or Redis backend
- Decorator-based throttling per route

### 6. Service Architecture

#### Docker Compose Structure

```yaml
Services:
├── nginx (Nginx 1.25)          # Reverse proxy & request router
├── admin-service (PHP 8.2)     # Laravel app, Jetstream UI, JWT tokens
├── api-gateway (Node 20)       # NestJS REST API, Product/Order routes
├── db (PostgreSQL 16)          # Single source of truth for data
└── redis (Redis 7)             # Cache, sessions, queues
```

docker-compose.override.yml - for development environment

#### Nginx Routing Rules

```
Port 8080 → Nginx
├── /          → Laravel web (admin-service:9000 PHP-FPM)
├── /api/*     → Laravel API routes
├── /service-api/* → NestJS API Gateway (http://api-gateway:3000)
└── /*.php     → Laravel file handling
```

**Route Example**:
- Request: `POST /service-api/api/products`
- Nginx rewrites to: `POST /api/products` and forwards to `http://api-gateway:3000`
- NestJS receives: `POST /api/products` with proper headers

#### Dockerfile Dependencies

**Admin Service Dockerfile**:
```dockerfile
FROM php:8.2-fpm-alpine
# Extensions: redis, pdo_pgsql, intl, zip, opcache
# Tools: composer, npm, node
RUN composer install --no-dev
RUN npm install && npm run build  # Build Vue assets
EXPOSE 9000  # PHP-FPM port (not HTTP)
```

**API Gateway Dockerfile**:
```dockerfile
FROM node:20-alpine
RUN npm install --legacy-peer-deps
RUN npm run build  # Build TypeScript
EXPOSE 3000  # HTTP port
CMD npm run start:dev
```

**Network Communication**:
- Services communicate via Docker network: `ecommerce_net`
- Service names resolve as hostnames: `db`, `redis`, `admin-service`, `api-gateway`
- No need for hardcoded IPs

### 7. Entity Relationships

**One-to-Many Relationships**:

```typescript
// OrderEntity → OrderItemEntity
@OneToMany(() => OrderItemEntity, item => item.order, { eager: true })
items: OrderItemEntity[]

// OrderItemEntity → OrderEntity
@ManyToOne(() => OrderEntity, order => order.items)
order: OrderEntity  // NOT eager to prevent circular loading
```

**Type Safety**:
- TypeORM metadata must be registered in `app.module.ts`
- Both entity and inverse entity must be in `entities: []` array
- Missing entity registration causes: "Entity metadata not found"

### 8. Event-Driven Updates

**Order Created Event**:
```typescript
// When order is created in NestJS:
publishOrderCreated({
  orderId,
  userId,
  totalAmount,
  items: [{ productId, quantity }, ...]
})

// Event emitted to Redis via ClientProxy
// Admin service can subscribe and:
// - Send order confirmation email
// - Update inventory
// - Publish to Stripe/payment gateway
// - Send SMS notifications
```

**Future Enhancement**:
- RabbitMQ or Apache Kafka for true event streaming
- Event sourcing pattern for order history
- Complex event processing (CEP) for fraud detection

### 9. Security Best Practices

**JWT Signature Verification**:
- HS256 with 64-character secret
- Both services validate signature independently
- Token claims include: sub, email, role, exp, iat, iss, aud

**API Key for Webhooks**:
- SYNC_KEY protects product sync webhook
- Nginx default.conf can add IP whitelisting

**Middleware Stack**:
```
Request → Nginx routing → PHP-FPM/Node → Guards → Controller → Service
                                      ↓
                         JWT validation + Role check
                         Admin middleware (Laravel)
```

**HTTPS in Production**:
- Nginx config supports `proxy_set_header X-Forwarded-Proto`
- Certificate required in production
- All JWT tokens should be transmitted over HTTPS

### 10. Performance Optimizations

**Database Indexing**:
```sql
-- Products table
INDEX (category_id)
INDEX (price)
INDEX (slug, is_active)

-- Orders table
INDEX (user_id)
INDEX (status_id)
INDEX (created_at DESC)
```

**Query Optimization**:
- LazyLoad relations in NestJS (no eager load by default)
- Select specific columns instead of `SELECT *`
- Use pagination (limit + offset) for large datasets

**Redis Caching**:
- Products cached with TTL
- Category list pre-loaded
- User roles cached per session

**Frontend Optimization**:
- Vue 3 with Vite (fast HMR)
- Code splitting per route
- Tailwind CSS purging

---

## Architecture Overview

### Data Flow: Product CRUD

```
Admin Panel (Vue 3)
    ↓ POST /products
Laravel ProductController
    ↓
App\Models\Product->create()
    ↓
ProductObserver->saved()
    ↓
HTTP POST → NestJS /api/webhooks/sync (with SYNC_KEY)
    ↓
Validate signature + Store in Redis cache
    ↓
API Gateway ready to serve product via /api/products/:id
```

### Data Flow: Order Creation (NestJS)

```
REST Client
    ↓ POST /service-api/api/orders
Nginx (rewrite to :3000/api/orders)
    ↓
NestJS OrdersController->createOrder()
    ↓
OrdersService->createOrder()
    ↓
TypeORM transaction:
  1. Create Order entity
  2. Create OrderItem entities (relationships)
  3. Commit (all or nothing)
    ↓
OrderEventsPublisher.publishOrderCreated()
    ↓
Emit to Redis (Redis pub/sub via ClientProxy)
    ↓
Admin service listens and processes async
```

### Technology Stack Summary

| Layer | Technology | Purpose |
|-------|-----------|---------|
| API Gateway | Nginx 1.25 | Request routing, reverse proxy |
| Frontend | Vue 3 + Inertia.js | Admin SPA with server-side rendering |
| Backend (Web) | Laravel 11 + Jetstream | Business logic, admin UI, JWT tokens |
| Backend (API) | NestJS + TypeORM | REST API, type-safe database queries |
| Database | PostgreSQL 16 | Relational data storage |
| Cache | Redis 7 | Sessions, cache, queues, pub/sub |
| Container | Docker + Compose | Orchestration and networking |

### Security Layers

```
Nginx
  ├─ Rate limiting (nginx built-in)
  └─ HTTPS termination (in production)
       ↓
Laravel
  ├─ JWT validation
  ├─ Admin middleware (role check)
  ├─ Throttle middleware (per-route limits)
  └─ CSRF protection (Sanctum)
       ↓
NestJS
  ├─ JWT validation (same secret)
  ├─ JwtAuthGuard (401 Unauthorized)
  ├─ RolesGuard (403 Forbidden)
  └─ SyncKeyGuard (webhook signature verification)

Database
  └─ Row-level security (future: RLS)
```

---

## Deployment Checklist

- [ ] Change `JWT_SECRET` to a strong random value
- [ ] Set `APP_DEBUG=false` in Laravel `.env`
- [ ] Enable HTTPS in Nginx
- [ ] Configure PostgreSQL backup strategy
- [ ] Set up Redis persistence (AOF/RDB)
- [ ] Implement rate limiting thresholds
- [ ] Add monitoring (Prometheus/Grafana)
- [ ] Configure log aggregation (ELK stack)
- [ ] Set up CI/CD pipeline
- [ ] Database schema versioning
- [ ] Implement request/response logging
- [ ] Configure alert thresholds

---

## Troubleshooting

### Database Connection Failed
```bash
# Check PostgreSQL health
docker logs ecommerce_db

# Verify connection from containers
docker exec ecommerce_api_gateway \
  psql -h db -U ecommerce -d ecommerce -c "SELECT 1"
```

### Entity Metadata Not Found
- Check that both `OrderEntity` and `OrderItemEntity` are in `app.module.ts` entities array
- Verify import statements are correct
- Rebuild: `docker compose down && docker compose up -d --build`

### Redis Connection Error
```bash
# Test Redis connectivity
docker exec ecommerce_api_gateway redis-cli -h redis ping
# Should return: PONG
```

### Product Sync Not Working
- Check `SYNC_KEY` matches in both `.env` files
- Verify `NESTJS_SYNC_URL` in Laravel `.env`
- Monitor webhook calls: `docker logs -f ecommerce_api_gateway`

---

**Documentation Last Updated**: March 6, 2026  
**Project Version**: 1.0.0

