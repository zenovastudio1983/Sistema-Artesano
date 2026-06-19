# Guía para contribuir

## Configuración del entorno de desarrollo

### Requisitos

- PHP 8.4+
- Node.js 20+
- Docker Desktop
- Git con GPG signing configurado (recomendado)

### Setup inicial

```bash
git clone https://github.com/tu-usuario/artisan-erp.git
cd artisan-erp
cp .env.example .env

docker compose up -d
docker compose exec app composer install
docker compose exec app npm install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

---

## Estándares de código

### PHP / Laravel

- **PSR-12** para estilo de código
- **PHP 8.4+**: usar typed properties, readonly, match, enums, named arguments
- **Strict types**: declarar `declare(strict_types=1)` en todos los archivos PHP
- **Type hints completos**: todos los parámetros y retornos deben estar tipados
- **No comentarios obvios**: comentar solo el "por qué", no el "qué"

```php
<?php

declare(strict_types=1);

namespace App\Domains\Inventory\Services;

use App\Domains\Inventory\Models\Inventory;
use App\Domains\Products\Models\Product;
use App\Support\Enums\MovementType;

class StockService
{
    // Correcto: explica la fórmula de negocio
    // Formula: (stock_actual * costo_actual + nueva_cantidad * nuevo_costo) / stock_total
    private function calculateNewAverageCost(
        float $currentQuantity,
        float $currentCost,
        float $newQuantity,
        float $newUnitCost,
    ): float {
        $totalQuantity = $currentQuantity + $newQuantity;
        if ($totalQuantity <= 0) {
            return $newUnitCost;
        }
        return ($currentQuantity * $currentCost + $newQuantity * $newUnitCost) / $totalQuantity;
    }
}
```

### Naming conventions

| Elemento | Convención | Ejemplo |
|----------|-----------|---------|
| Clases | PascalCase | `StockService` |
| Métodos | camelCase | `recordMovement()` |
| Variables | camelCase | `$unitCost` |
| Constantes | SCREAMING_SNAKE | `MAX_STOCK_LIMIT` |
| Tablas BD | snake_case plural | `stock_movements` |
| Columnas BD | snake_case | `unit_cost` |
| Rutas API | kebab-case | `/purchase-orders` |
| Archivos Blade | kebab-case | `product-index.blade.php` |

### Estructura de un Service

```php
class ProductionService
{
    public function __construct(
        private readonly StockService $stock,
    ) {}

    // Método público: punto de entrada limpio con transacción
    public function create(array $data): ProductionOrder
    {
        return DB::transaction(function () use ($data) {
            $order = ProductionOrder::create([...]);
            $this->attachMaterials($order, $data['materials'] ?? []);
            activity()->on($order)->log('created');
            return $order;
        });
    }

    // Métodos privados: lógica interna
    private function attachMaterials(ProductionOrder $order, array $materials): void
    {
        // ...
    }
}
```

---

## Testing

Todos los cambios deben incluir tests. La cobertura mínima es **80%**.

### Tipos de tests

| Tipo | Carpeta | Uso |
|------|---------|-----|
| Unit | `tests/Unit/` | Lógica de servicios aislada |
| Feature | `tests/Feature/` | Endpoints HTTP/API |
| Integration | `tests/Integration/` | Flujos completos de negocio |

### Escribir un test con PestPHP

```php
<?php

use App\Domains\Inventory\Services\StockService;
use App\Support\Enums\MovementType;

uses(Tests\TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('StockService', function () {
    beforeEach(function () {
        $this->service = app(StockService::class);
        $this->warehouse = createWarehouse();
        $this->product = createProduct(['average_cost' => 10.00]);
    });

    it('records an entry movement', function () {
        $movement = $this->service->recordMovement(
            product: $this->product,
            warehouse: $this->warehouse,
            quantity: 100,
            type: MovementType::InitialStock,
            unitCost: 10.00,
        );

        expect($movement->quantity)->toBe(100.0)
            ->and($movement->type)->toBe(MovementType::InitialStock);
    });

    it('throws when insufficient stock', function () {
        $this->service->recordMovement($this->product, $this->warehouse, 10, MovementType::InitialStock, 10.00);

        expect(fn() => $this->service->recordMovement(
            $this->product, $this->warehouse, 50, MovementType::SaleExit
        ))->toThrow(DomainException::class, 'Stock insuficiente');
    });
});
```

### Ejecutar tests

```bash
# Todos los tests
docker compose exec app php artisan test

# Con verbose output
docker compose exec app php artisan test -v

# Suite específica
docker compose exec app php artisan test --testsuite=Unit

# Un archivo
docker compose exec app php artisan test tests/Unit/Domains/StockServiceTest.php

# Con cobertura
docker compose exec app php artisan test --coverage --min=80
```

---

## Flujo de trabajo Git

### Branches

```
main          # Producción — solo merges de release/
develop       # Desarrollo — integración de features
feature/      # Nuevas funcionalidades
fix/          # Correcciones de bugs
refactor/     # Refactorizaciones
```

### Convención de commits

Usar **Conventional Commits**:

```
feat(ventas): agregar cálculo de comisiones por vendedor
fix(inventario): corregir cálculo de costo promedio en transferencias
refactor(recetas): simplificar RecipeService::recalculateCosts
test(producción): agregar tests para cancelación con devolución de stock
docs(api): documentar endpoints de reportes
chore(deps): actualizar Laravel a 12.x
```

**Formato:**
```
tipo(módulo): descripción corta en imperativo

[Cuerpo opcional: qué cambió y por qué]

[Referencias: Closes #123]
```

### Pull Request

1. Crear branch desde `develop`
2. Implementar el cambio con tests
3. Verificar que todos los tests pasen: `php artisan test`
4. Verificar estilo: `composer run lint`
5. Crear PR hacia `develop` con:
   - Descripción del cambio
   - Tipo: feat/fix/refactor/etc.
   - Tests agregados o modificados
   - Capturas de pantalla si hay cambios visuales

---

## Estructura de un nuevo dominio

Para agregar un nuevo módulo, seguir esta estructura:

```
app/Domains/NuevoModulo/
├── Models/
│   └── NuevoModelo.php
├── Services/
│   └── NuevoService.php
└── Policies/
    └── NuevoModeloPolicy.php
```

Además:
- Migración en `database/migrations/`
- Seeder si aplica en `database/seeders/`
- Factory en `database/factories/`
- Livewire component en `app/Http/Livewire/NuevoModulo/`
- Vistas en `resources/views/livewire/nuevo-modulo/`
- Controller API en `app/Http/Controllers/Api/V1/NuevoModeloController.php`
- Ruta en `routes/api.php` y `routes/web.php`
- Tests en `tests/Unit/`, `tests/Feature/`, `tests/Integration/`

---

## Linting y análisis estático

```bash
# PHP-CS-Fixer (estilo)
composer run lint

# PHPStan (análisis estático, nivel 6)
composer run analyse

# Pint (Laravel Pint para estilo)
composer run pint
```

### `composer.json` scripts

```json
{
  "scripts": {
    "test": "php artisan test",
    "lint": "vendor/bin/pint",
    "analyse": "vendor/bin/phpstan analyse --level=6",
    "check": ["@lint", "@analyse", "@test"]
  }
}
```

---

## Reportar bugs

Al reportar un bug, incluir:

1. **Descripción**: qué ocurre vs qué se esperaba
2. **Pasos para reproducir**: descripción numerada
3. **Entorno**: versión de PHP, sistema operativo, navegador
4. **Logs**: extracto relevante de `storage/logs/laravel.log`
5. **Capturas** (si aplica)

Usar la plantilla de issue en GitHub.

---

## Licencia

Al contribuir, aceptas que tu código se distribuirá bajo la licencia MIT del proyecto.
