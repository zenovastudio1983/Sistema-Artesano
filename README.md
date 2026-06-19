# Artisan ERP

Sistema ERP vertical para fabricantes artesanales — velas, jabones, cosméticos artesanales, alimentos y talleres de producción manual.

![PHP](https://img.shields.io/badge/PHP-8.4+-777BB4?style=flat&logo=php)
![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=flat&logo=laravel)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-17-336791?style=flat&logo=postgresql)
![License](https://img.shields.io/badge/license-MIT-green?style=flat)

## Características principales

- **Inventario** con kardex completo, valorización por costo promedio ponderado y alertas de stock mínimo
- **Producción** con órdenes de fabricación, consumo automático de materias primas y seguimiento de costos
- **Recetas / BOM** con estructura multinivel, porcentaje de merma y recalculo en cascada de costos
- **Compras** con órdenes de compra, recepciones parciales y actualización automática de costos
- **Ventas** con cotizaciones, facturas, pagos y análisis de margen por producto
- **Reportes** con KPIs en tiempo real, análisis de rentabilidad y exportación a Excel/PDF
- **API REST** completa con autenticación Sanctum
- **Multi-rol** con Spatie Permission (Administrador, Producción, Compras, Ventas, Supervisor)

## Stack tecnológico

| Capa | Tecnología |
|------|-----------|
| Backend | PHP 8.4+, Laravel 12, Livewire 3 |
| Frontend | AlpineJS, TailwindCSS, Chart.js |
| Base de datos | PostgreSQL 17 |
| Caché / Colas | Redis 7 |
| Servidor web | Nginx + PHP-FPM |
| Contenedores | Docker + Docker Compose |
| Testing | PestPHP 3 |

## Inicio rápido

### Prerequisitos

- Docker Desktop (Windows/Mac) o Docker Engine + Compose (Linux)
- Git

### Instalación

```bash
# 1. Clonar el repositorio
git clone https://github.com/tu-usuario/artisan-erp.git
cd artisan-erp

# 2. Copiar variables de entorno
cp .env.example .env

# 3. Levantar contenedores
docker compose up -d

# 4. Instalar dependencias PHP
docker compose exec app composer install

# 5. Instalar dependencias Node y compilar assets
docker compose exec app npm install
docker compose exec app npm run build

# 6. Generar clave de aplicación
docker compose exec app php artisan key:generate

# 7. Ejecutar migraciones y seeders
docker compose exec app php artisan migrate --seed

# 8. Crear enlace simbólico para storage
docker compose exec app php artisan storage:link
```

Accede en: **http://localhost:8080**

**Credenciales por defecto:**
- Email: `admin@artisanerp.local`
- Contraseña: `Admin@ERP2024!`

### Usuarios demo adicionales

| Rol | Email | Contraseña |
|-----|-------|-----------|
| Producción | produccion@artisanerp.local | Demo@ERP2024! |
| Compras | compras@artisanerp.local | Demo@ERP2024! |
| Ventas | ventas@artisanerp.local | Demo@ERP2024! |
| Supervisor | supervisor@artisanerp.local | Demo@ERP2024! |

## Ejecutar tests

```bash
# Todos los tests
docker compose exec app php artisan test

# Suite específica
docker compose exec app php artisan test --testsuite=Unit
docker compose exec app php artisan test --testsuite=Feature
docker compose exec app php artisan test --testsuite=Integration

# Con cobertura (requiere Xdebug)
docker compose exec app php artisan test --coverage --min=80
```

## Estructura del proyecto

```
artisan-erp/
├── app/
│   ├── Domains/              # Módulos de dominio (DDD Light)
│   │   ├── Inventory/
│   │   ├── Products/
│   │   ├── Production/
│   │   ├── Purchases/
│   │   ├── Recipes/
│   │   ├── Reports/
│   │   ├── Sales/
│   │   └── Users/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/V1/       # API REST
│   │   │   └── Web/
│   │   └── Livewire/         # Componentes Livewire 3
│   ├── Jobs/                 # Cola de trabajos
│   ├── Providers/
│   └── Support/
│       └── Enums/            # Enums tipados PHP 8.1+
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── docker/                   # Configuración Docker
├── docs/                     # Documentación técnica
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
│       └── livewire/         # Vistas Blade
├── routes/
│   ├── api.php
│   ├── web.php
│   └── auth.php
└── tests/
    ├── Feature/
    ├── Integration/
    └── Unit/
```

## Documentación

- [Arquitectura](docs/ARCHITECTURE.md)
- [Base de datos](docs/DATABASE.md)
- [API REST](docs/API.md)
- [Despliegue](docs/DEPLOYMENT.md)
- [Guía de usuario](docs/USER_GUIDE.md)
- [Contribuir](docs/CONTRIBUTING.md)
- [Roadmap](docs/ROADMAP.md)

## Licencia

MIT License — ver [LICENSE](LICENSE)
