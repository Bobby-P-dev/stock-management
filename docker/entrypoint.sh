#!/bin/sh
set -e

# Pastikan folder storage dan cache ada
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/storage/app/public \
         /var/www/html/bootstrap/cache

# Atur permission agar bisa ditulis oleh www-data
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Bersihkan bootstrap cache yang mungkin terbawa dan discover packages produksi
rm -f /var/www/html/bootstrap/cache/config.php \
      /var/www/html/bootstrap/cache/routes*.php \
      /var/www/html/bootstrap/cache/services.php \
      /var/www/html/bootstrap/cache/packages.php

php artisan package:discover --ansi || true

# Buat storage link jika belum ada
if [ ! -L /var/www/html/public/storage ]; then
    php artisan storage:link || true
fi

# Jalankan migrasi database otomatis jika RUN_MIGRATIONS=true
if [ "$RUN_MIGRATIONS" = "true" ] || [ "$RUN_MIGRATIONS" = "1" ]; then
    echo "==> Menjalankan migrasi database..."
    php artisan migrate --force || echo "Peringatan: Gagal menjalankan migrasi, pastikan database sudah terhubung."
fi

# Optimasi cache untuk production / staging
if [ "$SKIP_CACHE" != "true" ] && { [ "$APP_ENV" = "production" ] || [ "$APP_ENV" = "staging" ]; }; then
    echo "==> Mengoptimalkan cache Laravel..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

echo "==> Berkah Mandiri Inventory siap dijalankan."
exec "$@"
