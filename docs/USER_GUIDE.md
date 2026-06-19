# Guía de usuario

## Primeros pasos

### Acceso al sistema

1. Abrir el navegador y navegar a la URL del sistema (p. ej. `http://localhost:8080`)
2. Ingresar email y contraseña
3. Al iniciar sesión por primera vez, revisar la configuración de la empresa en **Configuración → Empresa**

### Navegación general

El sistema tiene una barra lateral con los módulos principales:

- **Dashboard** — KPIs y resumen del negocio
- **Productos** — Catálogo de productos y materias primas
- **Inventario** — Stock actual y movimientos
- **Producción** — Órdenes de fabricación
- **Compras** — Proveedores y órdenes de compra
- **Ventas** — Clientes y ventas
- **Reportes** — Análisis y reportes
- **Administración** — Usuarios, roles y configuración (solo Administrador)

---

## Módulo: Productos

### Crear un producto

1. Ir a **Productos → Catálogo**
2. Hacer clic en **Nuevo producto**
3. Completar los campos:
   - **SKU**: se genera automáticamente (puede editarse)
   - **Nombre**: nombre descriptivo del producto
   - **Tipo**: seleccionar según corresponda
     - *Materia prima*: ingredientes y materiales base
     - *Producto terminado*: lo que se vende
     - *Semiterminado*: producto intermedio
     - *Empaque*: envases, etiquetas, cajas
     - *Insumo*: herramientas, productos de limpieza
   - **Unidad de medida**: und, kg, lt, g, ml, mt
   - **Precio de venta**: precio al cliente
   - **Costo estándar**: costo de referencia
   - **Stock mínimo**: cantidad para alertas
   - **Punto de reorden**: cuándo pedir más
4. Hacer clic en **Guardar**

### Tipos de productos

| Tipo | Descripción | Tiene receta | Afecta ventas |
|------|-------------|:---:|:---:|
| Materia prima | Ingredientes base | No | No |
| Producto terminado | Producto final | Sí | Sí |
| Semiterminado | Paso intermedio | Sí | Opcional |
| Empaque | Envases y presentación | No | No |
| Insumo | Materiales de apoyo | No | No |

### Ver el kardex de un producto

El kardex muestra el historial completo de entradas y salidas de un producto.

1. En la lista de productos, hacer clic en el ícono de kardex (📊)
2. Seleccionar el almacén y rango de fechas
3. La tabla muestra:
   - Fecha y tipo de movimiento
   - Cantidad entrada / salida
   - Saldo acumulado
   - Costo unitario y costo promedio

---

## Módulo: Recetas (BOM)

Las recetas definen cómo fabricar un producto terminado a partir de materias primas.

### Crear una receta

1. Ir al producto terminado correspondiente
2. Hacer clic en la pestaña **Recetas** → **Nueva receta**
3. Configurar:
   - **Cantidad de rendimiento**: cuántas unidades produce una corrida de la receta
   - **Ingredientes**: agregar cada materia prima con su cantidad y % de merma
   - **Costos adicionales**: mano de obra, gastos indirectos
4. Hacer clic en **Calcular costos** para ver el costo unitario
5. **Guardar y establecer como predeterminada**

### Porcentaje de merma

El campo % merma indica cuánto se pierde en el proceso:
- Si una receta usa 100g de cera con 5% de merma, se consumirán **105g** en producción
- El sistema calcula automáticamente la `net_quantity = cantidad × (1 + merma/100)`

### Recalculo de costos

Cuando cambia el precio de una materia prima, el sistema recalcula automáticamente:
1. El costo de todas las recetas que usan esa materia prima
2. El costo estándar de los productos terminados afectados
3. Se registra en el historial de costos

---

## Módulo: Producción

### Ciclo de vida de una orden de producción

```
BORRADOR → PLANIFICADA → EN PROGRESO → FINALIZADA
                    ↓
                CANCELADA
```

### Crear una orden de producción

1. Ir a **Producción → Órdenes de Fabricación**
2. Hacer clic en **Nueva orden**
3. Completar:
   - **Producto a fabricar**: seleccionar el producto terminado
   - **Receta**: se carga la predeterminada (puede cambiarse)
   - **Cantidad planificada**: unidades a producir
   - **Fechas**: inicio y fin planificados
   - **Almacén**: dónde ingresar el producto terminado
4. Guardar → queda en estado **BORRADOR**

### Planificar la orden

Al pasar a **PLANIFICADA**:
- El sistema calcula los costos estimados
- Muestra la disponibilidad de materias primas
- Reserva los materiales (opcional según configuración)

### Iniciar la producción

Al pasar a **EN PROGRESO**:
- ⚠️ **Se descuentan automáticamente las materias primas del inventario**
- Si no hay stock suficiente, el sistema muestra un error
- Registra el movimiento tipo "Consumo de producción" en el kardex

### Registrar producción

Durante la producción, registrar las unidades fabricadas:
1. Hacer clic en **Registrar producción**
2. Ingresar:
   - **Unidades producidas**: las que pasaron control de calidad
   - **Unidades rechazadas**: descarte
3. Se puede registrar parcialmente varias veces

### Finalizar la orden

Al pasar a **FINALIZADA**:
- ⚠️ **Ingresa el producto terminado al inventario**
- Calcula el costo real vs estimado
- Actualiza el costo promedio del producto terminado
- Cierra la orden

### Cancelar una orden

Si se cancela una orden **EN PROGRESO**:
- Los materiales consumidos se devuelven al inventario
- Se registra el movimiento de ajuste positivo

---

## Módulo: Compras

### Proceso de compra

```
Crear OC → Enviar al proveedor → Recibir mercadería → (Completada)
```

### Crear una orden de compra

1. Ir a **Compras → Órdenes de Compra** → **Nueva OC**
2. Seleccionar proveedor y almacén de destino
3. Agregar productos con cantidad y precio unitario
4. Aplicar descuentos si corresponde
5. Guardar

### Recibir mercadería

Al recibir físicamente los productos:
1. Abrir la OC correspondiente
2. Hacer clic en **Registrar recepción**
3. Ingresar las cantidades recibidas (puede ser parcial)
4. Confirmar el costo unitario real (puede diferir de la OC)

Al confirmar la recepción:
- ✅ El stock se actualiza automáticamente
- ✅ El costo promedio ponderado se recalcula
- ✅ Se registra el movimiento en el kardex
- Si se recibió todo, la OC pasa a **RECIBIDA**; si fue parcial, a **PARCIALMENTE RECIBIDA**

---

## Módulo: Ventas

### Proceso de venta

```
Cotización → Confirmar → Facturar → Registrar pagos → Pagada
```

### Crear una venta / cotización

1. Ir a **Ventas → Ventas** → **Nueva venta**
2. Seleccionar cliente (opcional para ventas al contado)
3. Agregar productos con cantidad y precio
4. Aplicar descuento general si corresponde
5. Guardar → estado **COTIZACIÓN**

### Confirmar la venta

Al confirmar:
- ⚠️ **Se descuenta el stock automáticamente**
- Si no hay stock suficiente, el sistema muestra un error con la cantidad disponible
- Se calcula el costo de ventas (COGS) y el margen bruto

### Registrar pagos

1. Abrir la venta
2. Hacer clic en **Registrar pago**
3. Ingresar monto, método (efectivo/transferencia/tarjeta) y referencia
4. Se pueden registrar pagos parciales
5. Al acumular el total, la venta pasa a **PAGADA**

---

## Módulo: Inventario

### Ver el estado del inventario

Ir a **Inventario → Estado del inventario** para ver todos los productos con:
- Stock disponible (total - reservado)
- Estado: 🟢 OK / 🟡 Bajo / 🔴 Crítico / ⚫ Sin stock
- Valor total por producto

### Ajustar inventario

Para corregir diferencias después de un conteo físico:
1. Ir a **Inventario → Ajustes**
2. Seleccionar producto y almacén
3. Ingresar la cantidad real contada
4. El sistema calcula la diferencia y registra el ajuste

### Transferir entre almacenes

Para mover stock de un almacén a otro:
1. Ir a **Inventario → Transferencias**
2. Seleccionar producto, almacén origen y destino
3. Ingresar cantidad a transferir
4. El sistema registra dos movimientos: salida del origen y entrada al destino

---

## Módulo: Reportes

### Dashboard

El dashboard muestra en tiempo real:
- **Ventas del mes**: total en S/. y comparativa con mes anterior
- **Margen bruto**: rentabilidad del período
- **Producción activa**: órdenes en progreso
- **Alertas de stock**: productos en nivel crítico

### Reportes disponibles

| Reporte | Descripción |
|---------|-------------|
| Ventas | Por período, cliente, producto o vendedor |
| Compras | Por período y proveedor |
| Inventario | Valorización actual y rotación |
| Producción | Costos estimados vs reales, eficiencia |
| Rentabilidad | Margen por producto y período |

### Exportar reportes

Todos los reportes pueden exportarse:
- **Excel (.xlsx)**: para análisis adicional
- **PDF**: para imprimir o archivar

---

## Gestión de usuarios (solo Administrador)

### Roles del sistema

| Rol | Acceso |
|-----|--------|
| Administrador | Acceso total, configuración |
| Supervisor | Visualizar todo, aprobar operaciones |
| Producción | Órdenes de producción, inventario |
| Compras | Proveedores, órdenes de compra |
| Ventas | Clientes, cotizaciones, ventas |

### Crear usuario

1. Ir a **Administración → Usuarios** → **Nuevo usuario**
2. Ingresar nombre, email y contraseña temporal
3. Asignar rol
4. El usuario recibirá un email con sus credenciales

### Auditoría

Ir a **Administración → Auditoría** para ver:
- Todas las acciones realizadas por todos los usuarios
- Quién modificó qué y cuándo
- Valores anteriores y nuevos

---

## Preguntas frecuentes

**¿Por qué el costo promedio cambió al recibir una compra?**
El sistema usa costo promedio ponderado: al ingresar nueva mercadería con un precio diferente, el costo promedio se recalcula considerando el stock existente y el nuevo stock.

**¿Puedo editar una venta confirmada?**
No directamente. Debes cancelarla y crear una nueva. Esto garantiza la integridad del inventario y la auditoría.

**¿Qué pasa si cancelo una orden de producción que ya inició?**
Los materiales consumidos se devuelven automáticamente al inventario con un ajuste positivo, y el sistema registra la devolución en el kardex.

**¿Puedo tener múltiples recetas para un producto?**
Sí. Puedes crear varias versiones de receta y activar la que corresponda. El historial de versiones queda guardado.

**¿Cómo configuro los impuestos?**
En **Configuración → Empresa** se define la tasa de IGV general. Cada producto puede tener su propia tasa en el campo "% Impuesto".
