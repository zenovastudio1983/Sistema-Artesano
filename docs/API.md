# API REST — Documentación

Base URL: `http://localhost:8080/api/v1`

## Autenticación

La API usa **Laravel Sanctum** con tokens Bearer.

### Obtener token

```http
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "admin@artisanerp.local",
  "password": "Admin@ERP2024!"
}
```

**Respuesta:**
```json
{
  "token": "1|abc123...",
  "user": {
    "id": 1,
    "name": "Administrador",
    "email": "admin@artisanerp.local",
    "roles": ["Administrador"]
  },
  "expires_at": "2025-01-06T00:00:00Z"
}
```

Incluir en todas las peticiones autenticadas:
```
Authorization: Bearer {token}
```

### Cerrar sesión

```http
POST /api/v1/auth/logout
Authorization: Bearer {token}
```

### Perfil del usuario autenticado

```http
GET /api/v1/auth/me
Authorization: Bearer {token}
```

---

## Productos

### Listar productos

```http
GET /api/v1/products?search=vela&type=finished_product&status=active&page=1&per_page=25
```

**Parámetros:**
| Param | Tipo | Descripción |
|-------|------|-------------|
| `search` | string | Busca en nombre, SKU, descripción |
| `type` | string | `raw_material`, `finished_product`, `semi_finished`, `packaging`, `supply` |
| `status` | string | `active`, `inactive`, `discontinued` |
| `category_id` | integer | Filtrar por categoría |
| `low_stock` | boolean | Solo productos con stock bajo |
| `page` | integer | |
| `per_page` | integer | Máx. 100 |

**Respuesta:**
```json
{
  "data": [...],
  "meta": { "current_page": 1, "total": 45, "per_page": 25 }
}
```

### Crear producto

```http
POST /api/v1/products
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Vela Aromática Lavanda",
  "type": "finished_product",
  "unit": "und",
  "cost": 8.50,
  "price": 25.00,
  "min_stock": 10,
  "reorder_point": 20
}
```

**Respuesta 201:**
```json
{
  "message": "Producto creado.",
  "data": {
    "id": 42,
    "sku": "PT-0042",
    "name": "Vela Aromática Lavanda",
    ...
  }
}
```

### Ver producto

```http
GET /api/v1/products/{id}
```

Incluye: `category`, `inventories` (stock por almacén), `defaultRecipe`

### Actualizar producto

```http
PUT /api/v1/products/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "price": 27.50,
  "min_stock": 15
}
```

### Eliminar producto

```http
DELETE /api/v1/products/{id}
Authorization: Bearer {token}
```

**Error 422** si tiene stock en inventario.

### Stock del producto

```http
GET /api/v1/products/{id}/inventory
```

### Kardex del producto

```http
GET /api/v1/products/{id}/kardex?warehouse_id=1&from=2024-01-01&to=2024-12-31
```

### Recetas del producto

```http
GET /api/v1/products/{id}/recipes
```

---

## Inventario

### Estado del inventario

```http
GET /api/v1/inventory?warehouse_id=1&status=low&search=cera
```

Consulta la vista `v_inventory_status`.

| Param | Descripción |
|-------|-------------|
| `warehouse_id` | Filtrar por almacén |
| `status` | `out_of_stock`, `critical`, `low`, `ok` |
| `search` | Buscar por nombre/SKU |

### Detalle de inventario

```http
GET /api/v1/inventory/{product_id}/{warehouse_id}
```

### Ajustar inventario

```http
POST /api/v1/inventory/{product_id}/adjust
Authorization: Bearer {token}
Content-Type: application/json

{
  "warehouse_id": 1,
  "new_quantity": 150.00,
  "notes": "Conteo físico mensual",
  "unit_cost": 12.50
}
```

### Transferir entre almacenes

```http
POST /api/v1/inventory/{product_id}/transfer
Authorization: Bearer {token}
Content-Type: application/json

{
  "from_warehouse_id": 1,
  "to_warehouse_id": 2,
  "quantity": 50.00,
  "notes": "Traslado para producción"
}
```

### Productos con stock bajo

```http
GET /api/v1/inventory/low-stock
```

---

## Órdenes de Producción

### Listar órdenes

```http
GET /api/v1/production-orders?status=in_progress&page=1
```

### Crear orden

```http
POST /api/v1/production-orders
Authorization: Bearer {token}
Content-Type: application/json

{
  "product_id": 15,
  "recipe_id": 3,
  "warehouse_id": 1,
  "planned_quantity": 100,
  "planned_start": "2024-02-01",
  "planned_end": "2024-02-03",
  "notes": "Lote de febrero"
}
```

**Respuesta 201:**
```json
{
  "message": "Orden creada.",
  "data": {
    "id": 8,
    "order_number": "OP-2024-0008",
    "status": "draft",
    ...
  }
}
```

### Ver orden

```http
GET /api/v1/production-orders/{id}
```

Incluye: `product`, `recipe`, `materials`, `logs`

### Planificar orden

```http
POST /api/v1/production-orders/{id}/plan
Authorization: Bearer {token}
```

Transición: `draft` → `planned`. Calcula costos estimados.

### Iniciar producción

```http
POST /api/v1/production-orders/{id}/start
Authorization: Bearer {token}

{
  "notes": "Iniciando producción turno mañana"
}
```

Transición: `planned` → `in_progress`. **Descuenta automáticamente las materias primas del stock.**

### Registrar producción

```http
POST /api/v1/production-orders/{id}/register-production
Authorization: Bearer {token}
Content-Type: application/json

{
  "produced_quantity": 95,
  "rejected_quantity": 5,
  "notes": "5 unidades defectuosas descartadas"
}
```

### Finalizar orden

```http
POST /api/v1/production-orders/{id}/finish
Authorization: Bearer {token}
```

Transición: `in_progress` → `finished`. **Ingresa el producto terminado al stock.**

### Cancelar orden

```http
POST /api/v1/production-orders/{id}/cancel
Authorization: Bearer {token}
Content-Type: application/json

{
  "reason": "Falta de insumos"
}
```

Si estaba `in_progress`, devuelve los materiales al inventario.

---

## Órdenes de Compra

### Listar órdenes

```http
GET /api/v1/purchase-orders?status=pending&supplier_id=2
```

### Crear orden

```http
POST /api/v1/purchase-orders
Authorization: Bearer {token}
Content-Type: application/json

{
  "supplier_id": 3,
  "warehouse_id": 1,
  "expected_date": "2024-02-15",
  "notes": "Pedido mensual",
  "items": [
    {
      "product_id": 1,
      "quantity": 50,
      "unit_price": 15.00,
      "discount_percent": 5
    }
  ]
}
```

### Enviar al proveedor

```http
POST /api/v1/purchase-orders/{id}/send
Authorization: Bearer {token}
```

Transición: `draft` → `sent`

### Recibir mercadería

```http
POST /api/v1/purchase-orders/{id}/receive
Authorization: Bearer {token}
Content-Type: application/json

{
  "received_at": "2024-02-16",
  "notes": "Mercadería en buen estado",
  "items": [
    {
      "purchase_order_item_id": 5,
      "received_quantity": 45,
      "unit_cost": 15.00
    }
  ]
}
```

**Actualiza automáticamente el stock y el costo promedio ponderado.**

### Cancelar orden

```http
POST /api/v1/purchase-orders/{id}/cancel
Authorization: Bearer {token}

{ "reason": "Proveedor no disponible" }
```

---

## Ventas

### Listar ventas

```http
GET /api/v1/sales?status=confirmed&customer_id=1&from=2024-01-01&to=2024-01-31
```

### Crear venta / cotización

```http
POST /api/v1/sales
Authorization: Bearer {token}
Content-Type: application/json

{
  "customer_id": 2,
  "sale_date": "2024-01-15",
  "due_date": "2024-01-30",
  "notes": "Pedido especial",
  "items": [
    {
      "product_id": 15,
      "quantity": 20,
      "unit_price": 25.00,
      "discount_percent": 10
    }
  ]
}
```

### Confirmar venta

```http
POST /api/v1/sales/{id}/confirm
Authorization: Bearer {token}
```

Transición: `quotation` → `confirmed`. **Descuenta stock.**

**Error 422** si no hay stock suficiente.

### Facturar

```http
POST /api/v1/sales/{id}/invoice
Authorization: Bearer {token}
Content-Type: application/json

{
  "invoice_number": "F001-00045",
  "invoiced_at": "2024-01-15"
}
```

### Registrar pago

```http
POST /api/v1/sales/{id}/payment
Authorization: Bearer {token}
Content-Type: application/json

{
  "amount": 450.00,
  "method": "transfer",
  "reference": "TRF-2024-001",
  "paid_at": "2024-01-20",
  "notes": "Transferencia bancaria"
}
```

Cuando el total pagado >= total, cambia a `paid`.

### Cancelar venta

```http
POST /api/v1/sales/{id}/cancel
Authorization: Bearer {token}

{ "reason": "Cliente canceló pedido" }
```

Devuelve stock si estaba `confirmed` o `invoiced`.

---

## Reportes

### KPIs del dashboard

```http
GET /api/v1/reports/dashboard
Authorization: Bearer {token}
```

**Respuesta:**
```json
{
  "data": {
    "sales_month": 45230.00,
    "sales_count": 38,
    "gross_profit": 18500.00,
    "gross_margin": 40.9,
    "production_orders_active": 3,
    "low_stock_count": 7,
    "pending_purchases": 12500.00,
    "inventory_value": 89340.00,
    "top_products": [...],
    "cached_at": "2024-01-15T10:30:00Z"
  }
}
```

### Reporte de ventas

```http
GET /api/v1/reports/sales?from=2024-01-01&to=2024-01-31&group_by=week&customer_id=2
```

### Reporte de compras

```http
GET /api/v1/reports/purchases?from=2024-01-01&to=2024-01-31&supplier_id=3
```

### Reporte de inventario

```http
GET /api/v1/reports/inventory?warehouse_id=1
```

### Reporte de costos de producción

```http
GET /api/v1/reports/production?from=2024-01-01&to=2024-01-31
```

### Reporte de rentabilidad

```http
GET /api/v1/reports/profitability?from=2024-01-01&to=2024-12-31&group_by=month
```

---

## Categorías

```http
GET    /api/v1/categories              # Árbol completo
POST   /api/v1/categories              # Crear
GET    /api/v1/categories/{id}         # Ver (incluye hijos y productos)
PUT    /api/v1/categories/{id}         # Actualizar
DELETE /api/v1/categories/{id}         # Eliminar (falla si tiene productos)
```

## Proveedores

```http
GET    /api/v1/suppliers?search=muebles&active_only=true
POST   /api/v1/suppliers
GET    /api/v1/suppliers/{id}          # Incluye últimas 10 OC
PUT    /api/v1/suppliers/{id}
DELETE /api/v1/suppliers/{id}          # Falla si tiene OC activas
```

## Clientes

```http
GET    /api/v1/customers?search=juan
POST   /api/v1/customers
GET    /api/v1/customers/{id}          # Incluye últimas 10 ventas y crédito disponible
PUT    /api/v1/customers/{id}
DELETE /api/v1/customers/{id}          # Falla si tiene ventas confirmadas
```

## Almacenes

```http
GET    /api/v1/warehouses
POST   /api/v1/warehouses
GET    /api/v1/warehouses/{id}
PUT    /api/v1/warehouses/{id}
```

---

## Códigos de error

| Código | Significado |
|--------|-------------|
| 400 | Bad Request — parámetros inválidos |
| 401 | Unauthenticated — token faltante o inválido |
| 403 | Forbidden — sin permiso para esta acción |
| 404 | Not Found — recurso no existe |
| 422 | Unprocessable Entity — validación fallida o regla de negocio |
| 429 | Too Many Requests — rate limiting |
| 500 | Internal Server Error |

### Formato de error de validación (422)

```json
{
  "message": "Los datos proporcionados no son válidos.",
  "errors": {
    "email": ["El campo email es obligatorio."],
    "quantity": ["La cantidad debe ser mayor a 0."]
  }
}
```

### Formato de error de negocio (422)

```json
{
  "message": "Stock insuficiente. Disponible: 15.00 kg, requerido: 50.00 kg."
}
```

---

## Rate Limiting

- **Autenticación**: 5 intentos por minuto por IP
- **API general**: 120 requests por minuto por token
- **Reportes**: 10 requests por minuto (caché de 5 min recomendado)
