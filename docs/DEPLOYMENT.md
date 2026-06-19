# Guía de despliegue

## Desarrollo local

### Requisitos

- Docker Desktop 4.x o Docker Engine 24+ + Docker Compose 2.x
- Git 2.x
- 4 GB RAM disponibles para Docker

### Inicio rápido

```bash
git clone https://github.com/tu-usuario/artisan-erp.git
cd artisan-erp
cp .env.example .env

docker compose up -d
docker compose exec app composer install --no-interaction
docker compose exec app npm install
docker compose exec app npm run build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:link
```

Accede en **http://localhost:8080**

### Servicios disponibles en desarrollo

| Servicio | URL | Descripción |
|---------|-----|-------------|
| App | http://localhost:8080 | Aplicación web |
| Mailpit | http://localhost:8025 | Buzón de correos de prueba |
| pgAdmin | http://localhost:5050 | Administrador PostgreSQL |
| PostgreSQL | localhost:5433 | Conexión directa a la BD |
| Redis | localhost:6380 | Conexión directa a Redis |

Para levantar los servicios de desarrollo adicionales:
```bash
docker compose --profile dev up -d
```

### Hot reload con Vite

```bash
# En otra terminal o en background
docker compose exec app npm run dev
```

### Comandos útiles en desarrollo

```bash
# Logs de la aplicación
docker compose logs -f app

# Consola de Laravel
docker compose exec app php artisan tinker

# Limpiar caches
docker compose exec app php artisan optimize:clear

# Re-ejecutar migraciones con datos de prueba
docker compose exec app php artisan migrate:fresh --seed

# Ejecutar tests
docker compose exec app php artisan test
docker compose exec app php artisan test --testsuite=Integration
```

---

## Producción

### Infraestructura recomendada

```
                    ┌──────────┐
Internet ──────────►│  Nginx   │ (reverse proxy, SSL termination)
                    │ (host)   │
                    └────┬─────┘
                         │
                    ┌────▼─────────────────────────────────┐
                    │           Docker Compose              │
                    │  ┌────────┐  ┌─────────────────────┐ │
                    │  │  app   │  │   queue / scheduler  │ │
                    │  │(fpm)   │  │                      │ │
                    │  └───┬────┘  └──────────────────────┘ │
                    │  ┌───▼────────────────────────────┐   │
                    │  │        postgres + redis         │   │
                    │  └────────────────────────────────┘   │
                    └──────────────────────────────────────-┘
```

### Variables de entorno de producción

Editar `.env` con los siguientes valores críticos:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://erp.mi-empresa.com

# Base de datos — usar contraseñas seguras
DB_PASSWORD=contraseña_muy_segura_aqui

# Redis
REDIS_PASSWORD=redis_password_aqui

# Email de producción (ejemplo con SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mi-empresa.com
MAIL_PORT=587
MAIL_USERNAME=erp@mi-empresa.com
MAIL_PASSWORD=mail_password
MAIL_FROM_ADDRESS=erp@mi-empresa.com

# Session — usar database en producción
SESSION_DRIVER=database

# Queue
QUEUE_CONNECTION=redis
```

### Despliegue en servidor Linux

```bash
# 1. Clonar en el servidor
git clone https://github.com/tu-usuario/artisan-erp.git /var/www/artisan-erp
cd /var/www/artisan-erp

# 2. Configurar entorno
cp .env.example .env
nano .env   # Editar con valores de producción

# 3. Levantar con target de producción
docker compose -f docker-compose.yml up -d --build

# 4. Instalar dependencias sin dev
docker compose exec app composer install --no-dev --optimize-autoloader

# 5. Compilar assets para producción
docker compose exec app npm ci
docker compose exec app npm run build

# 6. Setup inicial
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --force --seed
docker compose exec app php artisan storage:link
docker compose exec app php artisan optimize
```

### Nginx externo (reverse proxy con SSL)

```nginx
server {
    listen 443 ssl http2;
    server_name erp.mi-empresa.com;

    ssl_certificate     /etc/letsencrypt/live/erp.mi-empresa.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/erp.mi-empresa.com/privkey.pem;

    # Seguridad SSL
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    location / {
        proxy_pass         http://127.0.0.1:8080;
        proxy_set_header   Host $host;
        proxy_set_header   X-Real-IP $remote_addr;
        proxy_set_header   X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header   X-Forwarded-Proto $scheme;
        proxy_read_timeout 300;
        proxy_connect_timeout 60;
        client_max_body_size 50M;
    }
}

server {
    listen 80;
    server_name erp.mi-empresa.com;
    return 301 https://$host$request_uri;
}
```

### Proceso de actualización (zero-downtime)

```bash
cd /var/www/artisan-erp

# 1. Activar modo mantenimiento
docker compose exec app php artisan down --render="livewire.maintenance"

# 2. Obtener último código
git pull origin main

# 3. Actualizar dependencias si cambiaron
docker compose exec app composer install --no-dev --optimize-autoloader

# 4. Ejecutar migraciones
docker compose exec app php artisan migrate --force

# 5. Compilar assets nuevos
docker compose exec app npm ci && npm run build

# 6. Limpiar y regenerar caches
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan optimize

# 7. Reiniciar queue workers
docker compose restart queue

# 8. Reactivar
docker compose exec app php artisan up
```

---

## Backups

### Backup de PostgreSQL

```bash
# Backup completo
docker compose exec postgres pg_dump \
  -U artisan_user artisan_erp \
  | gzip > backups/artisan_erp_$(date +%Y%m%d_%H%M%S).sql.gz

# Restaurar
gunzip -c backups/artisan_erp_20240115_120000.sql.gz \
  | docker compose exec -T postgres psql -U artisan_user artisan_erp
```

### Backup automático con cron (en el host)

```cron
# Backup diario a las 3am, retener 30 días
0 3 * * * /var/www/artisan-erp/scripts/backup.sh >> /var/log/artisan-erp-backup.log 2>&1
```

Contenido de `scripts/backup.sh`:

```bash
#!/bin/bash
set -e
BACKUP_DIR="/var/backups/artisan-erp"
RETAIN_DAYS=30
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p "$BACKUP_DIR"

# BD
cd /var/www/artisan-erp
docker compose exec -T postgres pg_dump \
  -U artisan_user artisan_erp | gzip > "$BACKUP_DIR/db_$DATE.sql.gz"

# Storage (imágenes/adjuntos)
tar -czf "$BACKUP_DIR/storage_$DATE.tar.gz" \
  -C /var/www/artisan-erp storage/app/public

# Limpiar backups antiguos
find "$BACKUP_DIR" -name "*.gz" -mtime +$RETAIN_DAYS -delete

echo "Backup completado: $DATE"
```

### Backup de Redis

Redis está configurado con AOF + RDB. El volumen Docker persiste los datos automáticamente. Para backup manual:

```bash
docker compose exec redis redis-cli BGSAVE
docker cp artisan-erp_redis_1:/data/dump.rdb ./backups/redis_$(date +%Y%m%d).rdb
```

---

## Monitoreo

### Health check

La aplicación expone `/up` como endpoint de health check:

```bash
curl http://localhost:8080/up
# Responde 200 OK si la app está corriendo
```

### Logs

```bash
# Logs de la aplicación
docker compose logs -f app
tail -f storage/logs/laravel.log

# Logs de Nginx
docker compose logs -f nginx

# Logs del worker de colas
docker compose logs -f queue
```

### Métricas de PHP-FPM

```bash
docker compose exec app php-fpm -t   # Test de configuración
```

### Alertas de stock bajo

El sistema envía alertas automáticas. Configurar en `.env`:

```dotenv
MAIL_FROM_ADDRESS=erp@mi-empresa.com
ERP_ALERT_EMAIL=gerencia@mi-empresa.com
```

---

## Troubleshooting

### La app no inicia

```bash
# Ver todos los logs
docker compose logs

# Verificar estado de contenedores
docker compose ps

# Reiniciar servicio específico
docker compose restart app
```

### Error 500 en producción

```bash
# Ver logs de Laravel
docker compose exec app cat storage/logs/laravel.log | tail -100

# Verificar permisos de storage
docker compose exec app php artisan storage:link
docker compose exec app chmod -R 775 storage bootstrap/cache
```

### Migraciones fallidas

```bash
# Ver estado de migraciones
docker compose exec app php artisan migrate:status

# Revertir última migración
docker compose exec app php artisan migrate:rollback

# En desarrollo: reset completo
docker compose exec app php artisan migrate:fresh --seed
```

### Queue worker no procesa jobs

```bash
# Ver jobs pendientes
docker compose exec app php artisan queue:monitor

# Reiniciar worker
docker compose restart queue

# Procesar manualmente (debug)
docker compose exec app php artisan queue:work --once -v
```

### Problemas de permisos en Windows/Mac

```bash
# Dentro del contenedor
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
docker compose exec app chmod -R 775 storage bootstrap/cache
```
