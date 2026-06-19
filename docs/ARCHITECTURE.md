# Arquitectura del sistema

## Visión general

Artisan ERP sigue una arquitectura **Monolito Modular** con principios de **Domain-Driven Design (DDD Light)** y **Clean Architecture**. El objetivo es mantener la simplicidad operacional de un monolito mientras se preservan límites claros entre dominios.

```
┌─────────────────────────────────────────────────────────────────┐
│                         Clientes                                 │
│         Navegador (Livewire)  │  API REST (Sanctum)              │
└─────────────────────┬────────┴──────────────┬───────────────────┘
                      │                        │
┌─────────────────────▼────────────────────────▼───────────────────┐
│                      Capa HTTP / Presentación                     │
│   Controllers (Web + API/V1)    │    Livewire Components          │
│   Form Requests  │  Resources   │    Blade Views                  │
└─────────────────────────────────┬────────────────────────────────┘
                                  │
┌─────────────────────────────────▼────────────────────────────────┐
│                      Capa de Aplicación                           │
│        Services  │  Actions  │  Jobs  │  Commands                 │
└─────────────────────────────────┬────────────────────────────────┘
                                  │
┌─────────────────────────────────▼────────────────────────────────┐
│                      Capa de Dominio                              │
│   Models  │  Enums  │  DTOs  │  Domain Events  │  Exceptions      │
└─────────────────────────────────┬────────────────────────────────┘
                                  │
┌─────────────────────────────────▼────────────────────────────────┐
│                   Capa de Infraestructura                         │
│   PostgreSQL  │  Redis  │  Queue  │  Storage  │  Mail             │
└──────────────────────────────────────────────────────────────────┘
```

## Estructura de dominios

Cada dominio vive bajo `app/Domains/{Nombre}/` y contiene:

```
app/Domains/Inventory/
├── Models/
│   ├── Warehouse.php
│   ├── Inventory.php
│   ├── StockMovement.php
│   └── ProductBatch.php
├── Services/
│   └── StockService.php        # Lógica de negocio principal
└── Policies/
    └── InventoryPolicy.php
```

### Dominios del sistema

| Dominio | Responsabilidad |
|---------|----------------|
| `Products` | Catálogo de productos, categorías, atributos |
| `Inventory` | Stock, movimientos, almacenes, kardex |
| `Recipes` | Fórmulas/BOM, ingredientes, costos de receta |
| `Production` | Órdenes de fabricación, consumo de materiales |
| `Purchases` | Proveedores, órdenes de compra, recepciones |
| `Sales` | Clientes, ventas, pagos, cotizaciones |
| `Reports` | KPIs, reportes agregados, vistas materializadas |
| `Users` | Usuarios, roles, permisos, autenticación |

## Principios arquitectónicos

### 1. Regla de dependencia

Los dominios no importan entre sí directamente. La comunicación entre dominios se realiza a través de:

- **Services**: `StockService` puede ser inyectado por `ProductionService`
- **Eventos de dominio**: Para notificaciones asíncronas (futuro)
- **Jobs**: Para procesamiento diferido (`RecalculateProductCosts`)

```php
// Correcto: ProductionService usa StockService
class ProductionService {
    public function __construct(
        private readonly StockService $stock
    ) {}
}

// Incorrecto: Un Model importa otro Model de otro dominio directamente
// en métodos de negocio
```

### 2. Enums como reglas de dominio

Los estados y transiciones están codificados en Enums PHP 8.1+ con lógica integrada:

```php
enum ProductionOrderStatus: string {
    case Draft = 'draft';
    case Planned = 'planned';
    // ...
    
    public function canTransitionTo(self $new): bool {
        return match($this) {
            self::Draft    => in_array($new, [self::Planned, self::Cancelled]),
            self::Planned  => in_array($new, [self::InProgress, self::Cancelled]),
            self::InProgress => in_array($new, [self::Finished, self::Cancelled]),
            default        => false,
        };
    }
}
```

### 3. Services como orquestadores

Los Services coordinan múltiples operaciones y mantienen invariantes de dominio dentro de transacciones:

```php
class SaleService {
    public function confirm(Sale $sale): Sale {
        return DB::transaction(function () use ($sale) {
            // 1. Verificar stock
            // 2. Descontar stock (StockService)
            // 3. Calcular costos COGS
            // 4. Actualizar estado
            // 5. Registrar actividad
        });
    }
}
```

### 4. Modelos con lógica de presentación

Los Models de Eloquent pueden tener accessors de presentación pero no lógica de negocio compleja:

```php
// Accessor OK: derivado de datos del modelo
public function getMarginAttribute(): float {
    return $this->price > 0 ? (($this->price - $this->cost) / $this->price) * 100 : 0;
}

// No OK en el Model: llamadas a otros servicios, transacciones
```

## Flujo de datos

### Flujo web (Livewire)

```
Usuario → Livewire Component → Service → Model → DB
                            ↓
                      StockService
                      RecipeService
                         etc.
```

### Flujo API

```
HTTP Request → Sanctum Auth → Controller → Service → Response JSON
                           ↓
                     Validación
                     Autorización
```

### Flujo de colas

```
Service → dispatch(RecalculateProductCosts::class)
              ↓
         Queue Worker (Redis)
              ↓
         RecalculateProductCosts::handle()
              ↓
         RecipeService::recalculateCosts()
              ↓
         dispatch(RecalculateProductCosts) para productos padres
```

## Caching

| Dato | TTL | Clave |
|------|-----|-------|
| KPIs dashboard | 5 min | `kpis:dashboard` |
| Costo de producto | 30 min | `product:cost:{id}` |
| Reportes generados | 1 hora | `report:{tipo}:{hash}` |
| Alertas de stock | 10 min | `stock:alerts` |

Redis se usa con tags para poder invalidar grupos relacionados:

```php
Cache::tags(['kpis', 'dashboard'])->remember('kpis:main', 300, fn() => ...);
Cache::tags(['kpis'])->flush(); // Invalida todos los KPIs
```

## Seguridad

### Autenticación

- **Web**: Sesiones Laravel + cookie `remember_me`
- **API**: Laravel Sanctum (Bearer tokens), expiración 30 días
- **Rate limiting**: 5 intentos fallidos → bloqueo temporal

### Autorización

Spatie Permission con 5 roles predefinidos y 40+ permisos granulares:

```
Administrador → todos los permisos
Supervisor    → view, create, edit (sin delete ni settings)
Ventas        → clientes, ventas, productos (solo lectura)
Compras       → proveedores, compras, productos (solo lectura)
Producción    → producción, inventario, recetas, productos (solo lectura)
```

### Auditoría

Spatie ActivityLog registra automáticamente:
- Quién realizó la acción
- Qué modelo fue afectado
- Valores anteriores y nuevos
- IP y timestamp

## PostgreSQL features utilizadas

| Feature | Uso |
|---------|-----|
| `storedAs` (columnas generadas) | `available_quantity`, `gross_profit`, `net_quantity` |
| Índices GIN + `pg_trgm` | Búsqueda full-text en productos |
| Vistas (`CREATE VIEW`) | `v_inventory_status`, `v_dashboard_kpis` |
| Secuencias (`CREATE SEQUENCE`) | Numeración de órdenes |
| Funciones PL/pgSQL | `generate_order_number()` |
| Extensiones | `uuid-ossp`, `pg_trgm`, `unaccent`, `btree_gin` |

## Testing

```
tests/
├── Unit/           # Tests de servicios y lógica pura
│   ├── Domains/
│   │   ├── StockServiceTest.php
│   │   └── RecipeServiceTest.php
├── Feature/        # Tests de endpoints API
│   ├── Api/
│   │   ├── AuthApiTest.php
│   │   └── ProductApiTest.php
└── Integration/    # Flujos completos de negocio
    ├── ProductionFlowTest.php
    └── SaleFlowTest.php
```

Cobertura mínima exigida: **80%**

## Decisiones de diseño

### ¿Por qué Monolito Modular en lugar de Microservicios?

Para una pequeña fábrica artesanal, los microservicios añaden complejidad operacional sin beneficio real. El monolito modular ofrece:
- Despliegue simple con `docker compose up`
- Sin latencia de red entre servicios
- Transacciones ACID simples
- Un único repositorio y pipeline CI/CD

### ¿Por qué costo promedio ponderado?

Es el método más apropiado para producción artesanal donde los lotes son variables y los precios de materias primas fluctúan. FIFO sería más preciso pero require tracking de lotes en cada movimiento.

### ¿Por qué Livewire en lugar de SPA?

Livewire reduce la complejidad del stack eliminando la necesidad de una API separada para la UI. Para un ERP con muchos formularios y tablas de datos, el modelo reactivo de Livewire es más productivo que mantener estado en el frontend.
