#!/bin/sh
set -e

echo "==> Artisan ERP — iniciando deploy..."

# Railway provee DATABASE_URL en formato postgresql://user:pass@host:port/db
# Lo parseamos con PHP para extraer los componentes que Laravel necesita
if [ ! -z "$DATABASE_URL" ]; then
    echo "==> Configurando DB desde DATABASE_URL..."
    eval $(php -r "
        \$url = parse_url(getenv('DATABASE_URL'));
        echo 'export DB_HOST=' . escapeshellarg(\$url['host']) . PHP_EOL;
        echo 'export DB_PORT=' . escapeshellarg(\$url['port'] ?? '5432') . PHP_EOL;
        echo 'export DB_DATABASE=' . escapeshellarg(ltrim(\$url['path'], '/')) . PHP_EOL;
        echo 'export DB_USERNAME=' . escapeshellarg(\$url['user']) . PHP_EOL;
        echo 'export DB_PASSWORD=' . escapeshellarg(\$url['pass']) . PHP_EOL;
    ")
    echo "   Host: $DB_HOST | Puerto: $DB_PORT | DB: $DB_DATABASE"
fi

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
    file_put_contents('php://stderr', \$e->getMessage() . PHP_EOL);
    exit(1);
}
"; do
    echo "   DB no disponible, reintentando en 3s..."
    sleep 3
done
echo "   DB lista."

# Migraciones
echo "==> Ejecutando migraciones..."
php artisan migrate --force

# Seeders solo si no hay usuarios (primer deploy)
USER_COUNT=$(php -r "
    require 'vendor/autoload.php';
    \$app = require 'bootstrap/app.php';
    \$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    echo \Illuminate\Support\Facades\DB::table('users')->count();
" 2>/dev/null || echo "0")
if [ "$USER_COUNT" = "0" ]; then
    echo "==> Ejecutando seeders (primer deploy)..."
    php artisan db:seed --force
    echo "   Seeders completados."
else
    echo "==> Seeders omitidos ($USER_COUNT usuarios ya existen)."
fi

# Storage link
echo "==> Configurando storage link..."
php artisan storage:link --force || true

# Cachear para producción
echo "==> Cacheando configuración..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "==> Listo. Iniciando servicios..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
