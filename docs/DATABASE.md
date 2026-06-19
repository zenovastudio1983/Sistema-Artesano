# Modelo de base de datos

## Diagrama de entidades

```
warehouses ──────────────────┐
                             │
categories ─── products ─────┼──── inventory ──── stock_movements
                │             │         │
                └─ (self-ref) │    product_batches
                              │
         recipes ─────────────┤
              │               │
    recipe_ingredients        │
    recipe_costs              │
                              │
production_orders ────────────┤
    │                         │
    production_order_materials│
    production_order_logs     │
                              │
suppliers ─── purchase_orders─┤
                   │          │
          purchase_order_items│
          purchase_receipts   │
          purchase_receipt_items
                              │
customers ─── sales ──────────┘
                  │
            sale_items
            sale_payments
```

## Tablas principales

### `warehouses` — Almacenes

| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | bigint PK | |
| code | varchar(20) UNIQUE | Código interno |
| name | varchar(100) | Nombre del almacén |
| description | text | |
| location | varchar(200) | Dirección física |
| is_default | boolean | Almacén por defecto |
| is_active | boolean | |
| deleted_at | timestamp | Soft delete |

---

### `categories` — Categorías de productos

| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | bigint PK | |
| parent_id | bigint FK nullable | Auto-referencial para subcategorías |
| name | varchar(100) | |
| slug | varchar(120) UNIQUE | URL-friendly |
| description | text | |
| color | varchar(7) | Hex color `#RRGGBB` |
| icon | varchar(50) | Nombre de ícono |
| sort_order | integer | Orden visual |
| is_active | boolean | |

**Índice:** `(parent_id, sort_order)`

---

### `products` — Catálogo de productos

| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | bigint PK | |
| sku | varchar(50) UNIQUE | Código interno |
| barcode | varchar(50) | Código de barras |
| name | varchar(200) | |
| description | text | |
| category_id | bigint FK | |
| type | varchar(30) | Enum: `raw_material`, `finished_product`, `semi_finished`, `packaging`, `supply` |
| unit | varchar(20) | Unidad de medida (und, kg, lt…) |
| cost | decimal(14,4) | Costo estándar manual |
| standard_cost | decimal(14,4) | Costo calculado por receta |
| average_cost | decimal(14,4) | Costo promedio ponderado actual |
| last_purchase_cost | decimal(14,4) | Último precio de compra |
| price | decimal(14,4) | Precio de venta |
| tax_rate | decimal(5,2) | % impuesto |
| min_stock | decimal(14,4) | Stock mínimo (alerta) |
| reorder_point | decimal(14,4) | Punto de reorden |
| max_stock | decimal(14,4) | Stock máximo |
| weight | decimal(10,4) | Kg |
| volume | decimal(10,4) | Litros |
| track_batches | boolean | Requiere lotes |
| status | varchar(20) | Enum: `active`, `inactive`, `discontinued` |

**Índices:**
- GIN sobre `(name, sku, description)` para full-text search
- `(type, status)`
- `(category_id)`

---

### `inventory` — Stock por almacén

| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | bigint PK | |
| product_id | bigint FK | |
| warehouse_id | bigint FK | |
| quantity | decimal(14,4) | Stock total |
| reserved_quantity | decimal(14,4) | Reservado para ventas/producción |
| available_quantity | decimal(14,4) **STORED** | `quantity - reserved_quantity` |
| average_cost | decimal(14,4) | Costo promedio vigente |
| last_movement_at | timestamp | |

**Restricción UNIQUE:** `(product_id, warehouse_id)`

---

### `stock_movements` — Kardex / movimientos de stock

| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | bigint PK | |
| product_id | bigint FK | |
| warehouse_id | bigint FK | |
| type | varchar(30) | Enum MovementType |
| quantity | decimal(14,4) | Positivo=entrada, negativo=salida |
| unit_cost | decimal(14,4) | Costo unitario del movimiento |
| total_cost | decimal(14,4) | `quantity * unit_cost` |
| balance_quantity | decimal(14,4) | Stock acumulado después del movimiento |
| balance_cost | decimal(14,4) | Costo promedio después |
| moveable_type | varchar(100) | Polimórfico: clase origen |
| moveable_id | bigint | Polimórfico: ID origen |
| batch_id | bigint FK nullable | Lote asociado |
| notes | text | |
| created_by | bigint FK | |
| created_at | timestamp | |

**Índices:** `(product_id, warehouse_id, created_at)`, `(moveable_type, moveable_id)`

**Tipos de movimiento (MovementType):**

| Valor | Dirección | Descripción |
|-------|-----------|-------------|
| `purchase_entry` | + | Recepción de compra |
| `sale_exit` | - | Salida por venta |
| `production_entry` | + | Entrada de producción |
| `production_consumption` | - | Consumo en producción |
| `positive_adjustment` | + | Ajuste de inventario + |
| `negative_adjustment` | - | Ajuste de inventario - |
| `transfer_in` | + | Traslado de entrada |
| `transfer_out` | - | Traslado de salida |
| `initial_stock` | + | Stock inicial |
| `return_to_supplier` | - | Devolución a proveedor |
| `customer_return` | + | Devolución de cliente |

---

### `recipes` — Recetas / BOM

| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | bigint PK | |
| product_id | bigint FK | Producto que se fabrica |
| version | integer | Versión de la receta |
| is_default | boolean | Receta activa |
| yield_quantity | decimal(14,4) | Unidades producidas por lote |
| yield_unit | varchar(20) | Unidad |
| material_cost | decimal(14,4) | Costo materiales por unidad |
| labor_cost | decimal(14,4) | Costo mano de obra |
| overhead_cost | decimal(14,4) | Gastos indirectos |
| total_cost | decimal(14,4) | Costo total |
| unit_cost | decimal(14,4) | `total_cost / yield_quantity` |
| notes | text | |

---

### `recipe_ingredients` — Ingredientes de receta

| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | bigint PK | |
| recipe_id | bigint FK | |
| product_id | bigint FK | Materia prima |
| quantity | decimal(14,6) | Cantidad requerida |
| scrap_percentage | decimal(5,2) | % merma |
| net_quantity | decimal(14,6) **STORED** | `quantity * (1 + scrap_percentage/100)` |
| unit | varchar(20) | |
| unit_cost | decimal(14,4) | Costo unitario al momento de cálculo |
| total_cost | decimal(14,4) | `net_quantity * unit_cost` |
| sort_order | integer | |

---

### `production_orders` — Órdenes de producción

| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | bigint PK | |
| order_number | varchar(30) UNIQUE | `OP-YYYY-NNNN` |
| product_id | bigint FK | Producto a fabricar |
| recipe_id | bigint FK nullable | |
| warehouse_id | bigint FK | Almacén destino |
| status | varchar(20) | Enum ProductionOrderStatus |
| planned_quantity | decimal(14,4) | |
| produced_quantity | decimal(14,4) | |
| rejected_quantity | decimal(14,4) | |
| estimated_material_cost | decimal(14,4) | |
| estimated_labor_cost | decimal(14,4) | |
| estimated_overhead_cost | decimal(14,4) | |
| estimated_total_cost | decimal(14,4) | |
| actual_material_cost | decimal(14,4) | |
| actual_total_cost | decimal(14,4) | |
| planned_start | date | |
| planned_end | date | |
| started_at | timestamp | |
| finished_at | timestamp | |
| notes | text | |

**Estados:** `draft` → `planned` → `in_progress` → `finished` / `cancelled`

---

### `purchase_orders` — Órdenes de compra

| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | bigint PK | |
| order_number | varchar(30) UNIQUE | `OC-YYYY-NNNN` |
| supplier_id | bigint FK | |
| warehouse_id | bigint FK | |
| status | varchar(20) | Enum PurchaseOrderStatus |
| subtotal | decimal(14,4) | |
| tax_amount | decimal(14,4) | |
| total | decimal(14,4) | |
| notes | text | |
| expected_date | date | |
| approved_by | bigint FK nullable | |
| approved_at | timestamp | |

---

### `sales` — Ventas / Cotizaciones

| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | bigint PK | |
| sale_number | varchar(30) UNIQUE | `VT-YYYY-NNNN` |
| customer_id | bigint FK nullable | |
| status | varchar(20) | Enum SaleStatus |
| subtotal | decimal(14,4) | |
| discount_amount | decimal(14,4) | |
| tax_amount | decimal(14,4) | |
| total | decimal(14,4) | |
| cost_of_goods | decimal(14,4) | Costo de ventas |
| gross_profit | decimal(14,4) **STORED** | `total - cost_of_goods` |
| notes | text | |
| sale_date | date | |
| due_date | date | |
| invoice_number | varchar(50) | |

**Estados:** `quotation` → `confirmed` → `invoiced` → `paid` / `cancelled`

---

## Vistas PostgreSQL

### `v_inventory_status`

Vista que combina `inventory`, `products` y `warehouses` para reportes:

```sql
SELECT
    i.id,
    p.sku,
    p.name AS product_name,
    p.type AS product_type,
    p.unit,
    w.name AS warehouse_name,
    i.quantity,
    i.reserved_quantity,
    i.available_quantity,
    i.average_cost,
    (i.quantity * i.average_cost) AS total_value,
    p.min_stock,
    p.reorder_point,
    CASE
        WHEN i.available_quantity <= 0 THEN 'out_of_stock'
        WHEN i.available_quantity <= p.min_stock THEN 'critical'
        WHEN i.available_quantity <= p.reorder_point THEN 'low'
        ELSE 'ok'
    END AS stock_status
FROM inventory i
JOIN products p ON p.id = i.product_id
JOIN warehouses w ON w.id = i.warehouse_id
WHERE p.status = 'active'
```

### `v_dashboard_kpis`

Vista con métricas del mes actual para el dashboard:

```sql
SELECT
    (SELECT COUNT(*) FROM sales WHERE status != 'cancelled' AND DATE_TRUNC('month', sale_date) = ...) AS total_sales_count,
    (SELECT SUM(total) FROM sales WHERE ...) AS total_sales_amount,
    (SELECT SUM(gross_profit) FROM sales WHERE ...) AS total_gross_profit,
    ...
```

## Secuencias PostgreSQL

| Secuencia | Uso | Formato resultado |
|-----------|-----|-------------------|
| `purchase_order_seq` | Órdenes de compra | `OC-2024-0001` |
| `production_order_seq` | Órdenes de producción | `OP-2024-0001` |
| `sale_seq` | Ventas | `VT-2024-0001` |
| `quotation_seq` | Cotizaciones | `COT-2024-0001` |
| `purchase_receipt_seq` | Recepciones | `REC-2024-0001` |
| `supplier_code_seq` | Código proveedor | `PROV-0001` |
| `customer_code_seq` | Código cliente | `CLI-0001` |
| `product_sku_seq` | SKU automático | `MP-0001` |

## Convenciones

- **Timestamps**: Todas las tablas tienen `created_at` y `updated_at` gestionados por Eloquent
- **Soft delete**: Solo en `warehouses`, `products`, `suppliers`, `customers`
- **Decimales**: `decimal(14,4)` para cantidades y costos; `decimal(14,2)` para precios al público
- **Strings de estado**: `varchar(20)` mapeados a BackedEnum PHP
- **Foreign keys**: Nombradas como `{tabla_singular}_id`, con índice automático en Laravel
- **Booleans**: PostgreSQL `boolean` nativo, con default explícito
