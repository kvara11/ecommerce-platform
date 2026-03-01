#!/bin/sh
set -e

echo "🚀 Starting admin-service..."

# PHP deps
if [ ! -d "vendor" ]; then
  echo "📦 Installing composer dependencies..."
  composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Frontend deps
if [ ! -d "node_modules" ]; then
  echo "📦 Installing npm dependencies..."
  npm install
fi

# Vite build (only if manifest missing)
if [ ! -f "public/build/manifest.json" ]; then
  echo "🎨 Building Vite assets..."
  npm run build
fi

echo "✅ Ready. Starting PHP-FPM..."
exec php-fpm