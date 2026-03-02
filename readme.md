# Ecommerce Platform - Admin Service

## Useful Commands

### 🏗️ Rebuild Frontend Assets (Vite)
To apply changes made to Vue or CSS files:
```bash
docker exec ecommerce_admin_service npm run build
```

### ⚡ Vite Development Mode (Hot Reload)
To see changes instantly while coding:
```bash
docker exec -it ecommerce_admin_service npm run dev
```

### 🔄 Database Migrations
To reset the database and re-seed:
```bash
docker exec ecommerce_admin_service php artisan migrate:fresh --seed
```

### 🧹 Clear Laravel Cache
```bash
docker exec ecommerce_admin_service php artisan config:clear
docker exec ecommerce_admin_service php artisan cache:clear
```

### 📊 Redis CLI
```bash
docker exec -it ecommerce_redis redis-cli
```

---

**Features:**
- Laravel Jetstream with Inertia.js (Vue 3)
- Redis for sessions, cache, and queues
- PostgreSQL for primary database
- Nginx as reverse proxy
