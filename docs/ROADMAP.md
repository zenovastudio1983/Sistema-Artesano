# Roadmap

## Estado actual — v1.0 (este release)

### Módulos completados

- ✅ Gestión de productos y categorías
- ✅ Inventario multinivel (múltiples almacenes), kardex, costo promedio ponderado
- ✅ Recetas / BOM con estructura de ingredientes y cálculo de costos en cascada
- ✅ Órdenes de producción con máquina de estados y consumo/ingreso automático de stock
- ✅ Compras con OC y recepción parcial de mercadería
- ✅ Ventas con cotizaciones, confirmación, facturación y pagos
- ✅ Reportes básicos: ventas, compras, inventario, producción, rentabilidad
- ✅ Autenticación y autorización con roles/permisos
- ✅ API REST completa con Sanctum
- ✅ Docker/Docker Compose para despliegue rápido
- ✅ Test suite (Unit + Feature + Integration)

---

## v1.1 — Mejoras de usabilidad

**Estimado: 1 mes**

### Notificaciones y alertas

- [ ] Notificaciones en tiempo real con Laravel Echo + Pusher/Soketi
- [ ] Email automático cuando un producto llega al punto de reorden
- [ ] Email al proveedor al confirmar una OC (usando Mailpit en desarrollo)
- [ ] Alertas de vencimiento de lotes (cuando `track_batches = true`)

### Mejoras de inventario

- [ ] Conteo de inventario físico asistido (scan de barcode)
- [ ] Trazabilidad completa de lotes desde compra hasta venta
- [ ] Soporte para unidades de medida con conversión (kg ↔ g, lt ↔ ml)

### UI / UX

- [ ] Tema oscuro (dark mode)
- [ ] Vistas de lista y tarjetas intercambiables
- [ ] Filtros avanzados guardados por usuario
- [ ] Drag-and-drop en orden de items de venta/compra

---

## v1.2 — Módulos adicionales

**Estimado: 2 meses**

### Módulo de Calidad

- [ ] Registro de control de calidad por lote de producción
- [ ] Checklist de inspección configurable
- [ ] Historial de rechazos por producto y línea de producción
- [ ] Indicadores de calidad (% rechazo, DPMO)

### Módulo de Mantenimiento

- [ ] Registro de equipos de producción
- [ ] Plan de mantenimiento preventivo
- [ ] Registro de mantenimientos correctivos
- [ ] Alerta de mantenimiento vencido

### Módulo de RR.HH. Básico

- [ ] Registro de empleados
- [ ] Asignación de empleados a órdenes de producción
- [ ] Tiempo trabajado por orden (para calcular costo de mano de obra real)

---

## v1.3 — Integraciones

**Estimado: 3 meses**

### Facturación electrónica (Perú)

- [ ] Integración con SUNAT vía SOAP/REST
- [ ] Generación de XML firmado (factura, boleta, nota de crédito)
- [ ] Envío automático al OSE
- [ ] QR code en documentos impresos

### Integraciones de e-commerce

- [ ] Sincronización de catálogo con WooCommerce
- [ ] Sync de pedidos de WooCommerce → Ventas ERP
- [ ] Actualización de stock en tiempo real

### Contabilidad

- [ ] Exportación de asientos contables en formato CSV/XML
- [ ] Integración con Contaplus / SIIGO / Alegra (API)
- [ ] Plan de cuentas básico configurable

---

## v2.0 — Arquitectura escalable

**Estimado: 6 meses**

### Multiempresa (Multi-tenant)

- [ ] Soporte para múltiples empresas en una misma instalación
- [ ] Aislamiento de datos por tenant (schema-per-tenant en PostgreSQL)
- [ ] Panel de superadministración para gestión de tenants

### App móvil

- [ ] App Android/iOS con React Native para operadores de almacén
- [ ] Escaneo de códigos de barras / QR
- [ ] Registro de movimientos de stock offline-first con sync
- [ ] Acceso al dashboard y alertas desde el móvil

### Analítica avanzada

- [ ] Forecasting de demanda con series temporales simples
- [ ] ABC analysis automático de productos
- [ ] Dashboard de BI con filtros dinámicos
- [ ] Exportación a Power BI / Tableau via API

### Módulo de Planificación de Producción (MPS)

- [ ] Plan maestro de producción mensual
- [ ] Cálculo de necesidades de materiales (MRP básico)
- [ ] Capacidad de producción por estación de trabajo
- [ ] Gantt interactivo de órdenes de producción

---

## Backlog sin prioridad

- Integración con balanzas electrónicas (para productos pesados)
- Módulo de punto de venta (POS) táctil para tienda física
- Gestión de proyectos para producciones por encargo
- App para clientes: portal de seguimiento de pedidos
- Integración con Mercado Libre / Amazon para ventas online
- Módulo de presupuestos y proyección financiera
- API webhooks para integraciones externas

---

## Contribuir al roadmap

¿Tienes una funcionalidad prioritaria para tu negocio? 

1. Abre un issue en GitHub con el tag `[feature-request]`
2. Describe el caso de uso y el valor de negocio
3. Las funcionalidades más votadas suben en la lista

Las contribuciones de código son bienvenidas — ver [CONTRIBUTING.md](CONTRIBUTING.md).
