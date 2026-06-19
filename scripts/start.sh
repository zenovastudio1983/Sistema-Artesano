#!/bin/sh
set -e

echo "==> Artisan ERP — iniciando deploy..."

# Esperar a que la base de datos esté disponible
echo "==> Esperando conexión a la base de datos..."
until php -r "
try {
    \$pdo = new PDO(
        'pgsql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: '5432') . ';dbname=' . getenv('DB_DATABASE'),
        getenv('DB_USERNAME'),
        getenv('DB_PASSWORD'),
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo 'ok';
} catch (Exception \$e) {
    exit(1);
}
" 2>/dev/null; do
    echo "   DB no disponible, reintentando en 3s..."
    sleep 3
done
echo "   DB lista."

# Ejecutar migraciones (--force evita la confirmación interactiva en producción)
echo "==> Ejecutando migraciones..."
php artisan migrate --force

# Enlace de almacenamiento público
echo "==> Configurando storage link..."
php artisan storage:link --force || true

# Cachear config, rutas y vistas para máximo rendimiento
echo "==> Cacheando configuración..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "==> Listo. Iniciando servicios..."

# Supervisord gestiona nginx + php-fpm
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
