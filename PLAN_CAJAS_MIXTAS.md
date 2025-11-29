# Plan de Implementación: Cajas Mixtas de Envases

## Estado: ✅ IMPLEMENTADO (2025-11-29)

## Restricción Principal
> **Las cajas mixtas NO pueden mezclar formatos diferentes.**
> Solo pueden contener envases de **diferentes recetas/tipos de cerveza** pero del **mismo formato** (ej: todas Lata 473ml).

---

## Análisis del Sistema Actual

### Flujo de Trazabilidad Actual
```
Batch (id_recetas)
    → BatchActivo/Barril
        → BatchDeEnvases (id_recetas, id_formatos_de_envases)
            → Envase (id_batches_de_envases)
                → CajaDeEnvases (id_productos)
                    → Producto (id_recetas, id_formatos_de_envases)
```

### Dependencias con `id_recetas`

| Entidad | Campo | Uso | Impacto Mixtas |
|---------|-------|-----|----------------|
| `Producto` | `id_recetas` | Vincula producto a receta específica | **CRÍTICO**: Una caja mixta NO tiene receta única |
| `Producto` | `id_formatos_de_envases` | Formato de envase del producto | ✓ OK: Se mantiene (mismo formato) |
| `BatchDeEnvases` | `id_recetas` | Receta del batch original | ✓ OK: Se mantiene para trazabilidad |
| `Envase` | (vía BatchDeEnvases) | Hereda receta del batch | ✓ OK: Cada envase mantiene su trazabilidad |
| `CajaDeEnvases` | `id_productos` | Producto asociado a la caja | **CRÍTICO**: Debe permitir productos mixtos |

### Validaciones Actuales en `ajax_crearCajaDeEnvases.php`
```php
// Línea 59-62: Esta validación SE MANTIENE (mismo formato obligatorio)
if($batch->id_formatos_de_envases != $producto->id_formatos_de_envases) {
  // ERROR: formatos diferentes - ✓ CORRECTO, no se permite
}

// Línea 64-67: Esta validación SE MANTIENE (mismo tipo Lata/Botella)
if($batch->tipo != $producto->tipo_envase) {
  // ERROR: tipos diferentes - ✓ CORRECTO, no se permite
}
```

---

## Solución Propuesta: Productos Mixtos (Solo Múltiples Recetas)

### Concepto
Crear un nuevo tipo de producto "Mixto" que:
1. **No tiene `id_recetas`** (valor 0 = "Sin receta específica" = múltiples cervezas)
2. **SÍ tiene `id_formatos_de_envases`** (formato fijo, ej: Lata 473ml)
3. **SÍ tiene `tipo_envase`** (Lata o Botella, no ambos)
4. Define `cantidad_de_envases` (cuántos envases caben en la caja)
5. Permite mezclar envases de **diferentes batches/recetas** del **mismo formato**

### Ejemplo
```
Producto: "Pack Mixto 24 Latas 473ml"
- es_mixto: 1
- id_recetas: 0 (no especifica receta)
- id_formatos_de_envases: 5 (Lata 473ml)
- tipo_envase: "Lata"
- cantidad_de_envases: 24
```

Una caja de este producto puede contener:
- 8 latas de IPA (Batch #101)
- 8 latas de Amber (Batch #102)
- 8 latas de Pale Ale (Batch #103)

### Ventajas
- Mantiene la estructura de productos existente
- **Mantiene validación de formato** (seguridad)
- Permite rastrear ventas de cajas mixtas como producto
- La trazabilidad a nivel de envase individual se MANTIENE
- Compatible con el sistema de despachos ya implementado

---

## Plan de Implementación

### FASE 1: Modelo de Datos

#### 1.1 Migración SQL
**Archivo:** `db/migrations/004_productos_mixtos.sql`

```sql
-- Agregar campo es_mixto a productos
ALTER TABLE productos
ADD COLUMN es_mixto TINYINT(1) NOT NULL DEFAULT 0
COMMENT '1 = producto mixto que acepta múltiples recetas (mismo formato)'
AFTER tipo_envase;

-- Índice para consultas
ALTER TABLE productos
ADD INDEX idx_es_mixto (es_mixto);
```

**Impacto:** Ningún dato existente se modifica.

#### 1.2 Modificar `Producto.php`
**Archivo:** `php/classes/Producto.php`

Cambios:
- Agregar propiedad `$es_mixto = 0`
- Agregar método `esMixto()` para verificar

```php
public $es_mixto = 0;

public function esMixto() {
  return $this->es_mixto == 1;
}
```

**Impacto:** Ninguno en código existente.

---

### FASE 2: Creación de Productos Mixtos

#### 2.1 Modificar `detalle-productos.php`
**Archivo:** `templates/detalle-productos.php`

Cambios:
- Agregar checkbox/toggle "Es Producto Mixto"
- Cuando `es_mixto = 1`:
  - Ocultar selector de Receta (no aplica para mixtos)
  - **Mantener** selector de Formato (obligatorio, mismo formato)
  - **Mantener** selector de Tipo Envase (Lata o Botella)
  - Mostrar: Nombre, Tipo Envase, Formato, Cantidad de Envases

UI propuesta:
```
[x] Es Producto Mixto (puede contener múltiples tipos de cerveza)

Tipo de Envase: [Lata ▼]              ← SE MANTIENE (obligatorio)
Formato:        [473ml ▼]             ← SE MANTIENE (obligatorio)
Envases por Caja: [24]

Receta: [--- No aplica para mixtos ---]  ← OCULTO/DESHABILITADO
```

**Impacto:** Solo cambios de UI, no afecta productos existentes.

#### 2.2 Modificar `nuevo-productos.php`
**Archivo:** `templates/nuevo-productos.php`

Mismos cambios que `detalle-productos.php`.

**Impacto:** Solo cambios de UI.

---

### FASE 3: Creación de Cajas Mixtas

#### 3.1 Modificar `ajax_crearCajaDeEnvases.php`
**Archivo:** `ajax/ajax_crearCajaDeEnvases.php`

**NO se requieren cambios en validación.** Las validaciones actuales son correctas:
- ✓ Validar mismo formato (`id_formatos_de_envases`)
- ✓ Validar mismo tipo (`tipo_envase`)

La única diferencia es que los productos mixtos tienen `id_recetas = 0`, pero eso **no se valida** en este archivo.

```php
// Validaciones EXISTENTES - SE MANTIENEN SIN CAMBIOS
if($batch->id_formatos_de_envases != $producto->id_formatos_de_envases) {
  // ERROR - ✓ Correcto, mixtas deben tener mismo formato
}
if($batch->tipo != $producto->tipo_envase) {
  // ERROR - ✓ Correcto, mixtas deben ser todas Lata o todas Botella
}
```

**Impacto:** Ningún cambio requerido. Los productos mixtos funcionan automáticamente porque:
- Tienen formato definido → validación de formato funciona
- Tienen tipo definido → validación de tipo funciona
- Solo difieren en `id_recetas = 0` que no se valida aquí

#### 3.2 Modificar `inventario-de-productos.php` - Modal Crear Cajas
**Archivo:** `templates/inventario-de-productos.php`

Cambios:
1. En Step 1: Agregar indicador visual "[MIXTO]" para productos mixtos
2. En Step 2: Para productos mixtos, mostrar columna adicional "Receta" para identificar origen

**El filtrado por formato sigue funcionando igual** porque los productos mixtos SÍ tienen formato definido.

UI propuesta para Step 2 (producto mixto):
```
| Batch | Receta      | Disponibles | Cantidad a usar |
|-------|-------------|-------------|-----------------|
| #123  | IPA         | 100         | [8]             |
| #124  | Amber       | 50          | [8]             |
| #125  | Pale Ale    | 80          | [8]             |
                                      Total: 24/24 ✓
```

**Nota:** Todos los batches mostrados son del mismo formato (ej: Lata 473ml) porque el producto mixto define ese formato.

**Impacto:** Cambios menores de UI para productos mixtos.

---

### FASE 4: Visualización de Cajas Mixtas

#### 4.1 Modificar `CajaDeEnvases.php`
**Archivo:** `php/classes/CajaDeEnvases.php`

Agregar método para obtener resumen de recetas:
```php
public function getResumenRecetas() {
  $envases = $this->getEnvases();
  $resumen = array();
  foreach($envases as $envase) {
    $batch = $envase->getBatchDeEnvases();
    if($batch) {
      $receta = $batch->getReceta();
      $key = $receta ? $receta->nombre : 'Sin receta';
      if(!isset($resumen[$key])) {
        $resumen[$key] = 0;
      }
      $resumen[$key]++;
    }
  }
  return $resumen;
}

public function esMixta() {
  $producto = $this->getProducto();
  return $producto ? $producto->esMixto() : false;
}
```

**Impacto:** Solo agrega funcionalidad, no modifica existente.

#### 4.2 Modificar `nuevo-despachos.php`
**Archivo:** `templates/nuevo-despachos.php`

Cambios:
- En el select de cajas, mostrar indicador "[MIXTA]" si es caja mixta
- En el info panel, mostrar desglose de recetas

```javascript
// Al seleccionar caja mixta, mostrar:
// "Contenido: IPA x12, Amber x8, Pale x4"
```

**Impacto:** Solo cambios de visualización.

#### 4.3 Modificar `central-despacho.php`
**Archivo:** `templates/central-despacho.php`

Cambios:
- Mostrar badge "[MIXTA]" para cajas mixtas
- Opcionalmente: tooltip con desglose de contenido

**Impacto:** Solo cambios de visualización.

#### 4.4 Modificar `repartidor.php`
**Archivo:** `templates/repartidor.php`

Cambios similares a central-despacho.

**Impacto:** Solo cambios de visualización.

---

### FASE 5: Reportes y Trazabilidad

#### 5.1 Considerar agregar vista de detalle de caja
**Nuevo archivo opcional:** `templates/detalle-caja-envases.php`

Muestra:
- Información de la caja
- Lista de envases con su receta/batch de origen
- Historial de estados

**Impacto:** Funcionalidad nueva, opcional.

---

## Resumen de Archivos a Modificar

| Archivo | Tipo de Cambio | Riesgo |
|---------|----------------|--------|
| `db/migrations/004_productos_mixtos.sql` | **NUEVO** | Bajo |
| `php/classes/Producto.php` | Agregar campo y método | Bajo |
| `php/classes/CajaDeEnvases.php` | Agregar métodos | Bajo |
| `templates/detalle-productos.php` | UI para productos mixtos | Medio |
| `templates/nuevo-productos.php` | UI para productos mixtos | Medio |
| `ajax/ajax_crearCajaDeEnvases.php` | **Sin cambios** | - |
| `templates/inventario-de-productos.php` | UI modal crear cajas | Bajo |
| `templates/nuevo-despachos.php` | Visualización | Bajo |
| `templates/central-despacho.php` | Visualización | Bajo |
| `templates/repartidor.php` | Visualización | Bajo |

---

## Consideraciones de Trazabilidad

### ¿Se Pierde Trazabilidad?
**NO.** La trazabilidad a nivel de envase individual se MANTIENE:

```
Envase #456
  └── BatchDeEnvases #123 (id_recetas = 5, Receta: "IPA")
        └── Batch #45
              └── BatchActivo (Fermentador BD-01)
```

Cada envase sigue sabiendo de qué batch/receta proviene, independientemente de que esté en una caja mixta.

### ¿Qué Cambia?
Solo la relación Producto ↔ Receta:
- **Antes:** Producto → 1 Receta
- **Después:** Producto Mixto → 0 Recetas (id_recetas=0), pero cada envase SÍ tiene su receta

### ¿Qué Se Mantiene Igual?
- **Formato obligatorio:** Producto Mixto → 1 Formato (todas las latas del mismo tamaño)
- **Tipo obligatorio:** Producto Mixto → 1 Tipo (todas Lata O todas Botella)

---

## Orden de Implementación Recomendado

1. **Migración SQL** - Agregar campo `es_mixto`
2. **Producto.php** - Agregar propiedad y método
3. **CajaDeEnvases.php** - Agregar métodos de soporte
4. **detalle-productos.php / nuevo-productos.php** - UI para crear productos mixtos
5. **inventario-de-productos.php** - UI para crear cajas mixtas (mostrar receta)
6. **Vistas de despacho** - Visualización de cajas mixtas

**Nota:** `ajax_crearCajaDeEnvases.php` NO requiere cambios.

---

## Preguntas Resueltas

1. **¿Puede una caja mixta mezclar Latas y Botellas?**
   - **NO** - Debe ser todo Lata o todo Botella

2. **¿Puede una caja mixta mezclar formatos (ej: 473ml y 355ml)?**
   - **NO** - Todas deben ser del mismo formato

3. **¿Los productos mixtos tienen precio fijo o se calcula por contenido?**
   - **Fijo** - Se define en el producto igual que los no-mixtos

---

## Estimación de Esfuerzo

| Fase | Descripción | Complejidad |
|------|-------------|-------------|
| 1 | Modelo de datos | Baja |
| 2 | UI productos | Media |
| 3 | Lógica creación cajas | **Baja** (sin cambios) |
| 4 | Visualización | Baja |
| 5 | Reportes (opcional) | Media |

**Esfuerzo total estimado:** Bajo-Medio (la restricción de mismo formato simplifica mucho)

---

## IMPLEMENTACIÓN COMPLETADA

### Migraciones SQL Ejecutadas

| Archivo | Descripción | Estado |
|---------|-------------|--------|
| `db/migrations/004_productos_mixtos.sql` | Campo `es_mixto` en productos | ✅ |
| `db/migrations/005_fix_cajas_envases.sql` | Renombrar `cantidad_latas` → `cantidad_envases` | ✅ |

### Archivos Modificados

#### Clases PHP
| Archivo | Cambios | Estado |
|---------|---------|--------|
| `php/classes/Producto.php` | `$es_mixto`, `esMixto()`, `getProductosMixtos()` | ✅ |
| `php/classes/CajaDeEnvases.php` | `esMixta()`, `getResumenRecetas()`, `getContenidoResumen()` | ✅ |
| `php/classes/Despacho.php` | `deleteSpecifics()` - revertir CajaEnvases a "En planta" | ✅ |

#### Templates
| Archivo | Cambios | Estado |
|---------|---------|--------|
| `templates/detalle-productos.php` | Checkbox "Es Producto Mixto", ocultar receta | ✅ |
| `templates/nuevo-productos.php` | Checkbox "Es Producto Mixto", ocultar receta | ✅ |
| `templates/inventario-de-productos.php` | Columna Receta en modal, botón revertir caja | ✅ |
| `templates/nuevo-despachos.php` | Badge MIXTO, resumen contenido | ✅ |
| `templates/central-despacho.php` | Badge MIXTO, resumen contenido | ✅ |
| `templates/repartidor.php` | Badge MIXTO, resumen contenido | ✅ |

#### AJAX
| Archivo | Cambios | Estado |
|---------|---------|--------|
| `ajax/ajax_guardarDespacho.php` | Asignar `id_productos` desde CajaDeEnvases | ✅ |
| `ajax/ajax_eliminarCajaDeEnvases.php` | **NUEVO** - Revertir/eliminar cajas | ✅ |
| `ajax/ajax_crearCajaDeEnvases.php` | Debug mejorado (removido en producción) | ✅ |

#### Facturación
| Archivo | Cambios | Estado |
|---------|---------|--------|
| `php/libredte.php` | Descripción correcta para productos de envases | ✅ |

---

## Flujo de Trazabilidad Completo

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                        FLUJO DE TRAZABILIDAD                                │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  PRODUCCIÓN                                                                 │
│  ──────────                                                                 │
│  Batch (Receta: IPA)                                                        │
│    └── BatchActivo (Fermentador/Barril)                                     │
│          └── BatchDeEnvases (id_recetas=15, Lata 269ml)                     │
│                └── Envase #1 (id_batches_de_envases=6)                      │
│                └── Envase #2 (id_batches_de_envases=6)                      │
│                └── ...180 envases                                           │
│                                                                             │
│  Batch (Receta: AMBAR)                                                      │
│    └── BatchActivo (Fermentador/Barril)                                     │
│          └── BatchDeEnvases (id_recetas=11, Lata 269ml)                     │
│                └── Envase #181 (id_batches_de_envases=4)                    │
│                └── ...50 envases                                            │
│                                                                             │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  EMPAQUE (Producto Mixto)                                                   │
│  ────────────────────────                                                   │
│  Producto: "Pack Mixto Latas 269ml x3"                                      │
│    - es_mixto: 1                                                            │
│    - id_recetas: 0 (no específica)                                          │
│    - id_formatos_de_envases: 4 (Lata 269ml)                                 │
│    - cantidad_de_envases: 3                                                 │
│                                                                             │
│  CajaDeEnvases (CAJA-251129-XXXX)                                           │
│    - id_productos: 12                                                       │
│    - estado: "En planta" → "En despacho" → "Entregada"                      │
│    └── Envase #1 (IPA, Batch #12)                                           │
│    └── Envase #181 (AMBAR, Batch #18)                                       │
│    └── Envase #2 (IPA, Batch #12)                                           │
│                                                                             │
│  getContenidoResumen(): "BLACK IPA x2, AMBAR x1"                            │
│                                                                             │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  DESPACHO                                                                   │
│  ────────                                                                   │
│  Despacho #123                                                              │
│    └── DespachoProducto                                                     │
│          - tipo: "CajaEnvases"                                              │
│          - id_productos: 12 ← CRÍTICO para facturación                      │
│          - id_cajas_de_envases: 5                                           │
│                                                                             │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ENTREGA                                                                    │
│  ───────                                                                    │
│  Entrega #456 (Cliente: Bar XYZ)                                            │
│    └── EntregaProducto                                                      │
│          - tipo: "CajaEnvases"                                              │
│          - id_productos: 12                                                 │
│          - id_cajas_de_envases: 5                                           │
│          - monto: $X (precio del producto)                                  │
│                                                                             │
│  CajaDeEnvases.estado = "Entregada"                                         │
│                                                                             │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  FACTURACIÓN (LibreDTE)                                                     │
│  ──────────────────────                                                     │
│  Producto.tipo == "Caja" && cantidad_de_envases > 0                         │
│    → Descripción: "Lata x3 Pack Mixto Latas 269ml"                          │
│                                                                             │
│  Se itera sobre producto.productos_items para generar detalles              │
│  con IVA + ILA según configuración                                          │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## Flujo de Estados de CajaDeEnvases

```
┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│   En planta  │────▶│  En despacho │────▶│  Entregada   │
└──────────────┘     └──────────────┘     └──────────────┘
       ▲                    │
       │                    │
       │    Eliminar        │    Eliminar
       │    Despacho        │    Despacho
       │                    │    (no permitido
       └────────────────────┘     si entregada)

       ▲
       │  Revertir Caja
       │  (botón en inventario)
       │
       └── Libera envases al estado "Envasado"
           CajaDeEnvases.estado = "eliminado"
```

---

## Correcciones Críticas Realizadas

### 1. Asignación de `id_productos` en Despacho
**Problema:** Al crear un despacho con CajaEnvases, no se asignaba `id_productos`.
**Solución:** `ajax/ajax_guardarDespacho.php` ahora obtiene `id_productos` desde la CajaDeEnvases.

```php
if($producto['tipo'] == "CajaEnvases" && isset($producto['id_cajas_de_envases'])) {
  $dp->id_cajas_de_envases = $producto['id_cajas_de_envases'];
  $caja_temp = new CajaDeEnvases($producto['id_cajas_de_envases']);
  if($caja_temp->id_productos > 0) {
    $dp->id_productos = $caja_temp->id_productos;  // ← CRÍTICO
  }
}
```

### 2. Revertir CajaEnvases al Eliminar Despacho
**Problema:** Al eliminar despacho, las cajas no volvían a "En planta".
**Solución:** `php/classes/Despacho.php` → `deleteSpecifics()` ahora revierte cajas.

```php
if($dp->tipo=="CajaEnvases" && $dp->id_cajas_de_envases > 0) {
  $caja = new CajaDeEnvases($dp->id_cajas_de_envases);
  if($caja->id) {
    $caja->estado = "En planta";
    $caja->save();
  }
}
```

### 3. Campo `cantidad_envases` en tabla
**Problema:** La tabla tenía `cantidad_latas` pero la clase usaba `cantidad_envases`.
**Solución:** Migración `005_fix_cajas_envases.sql` renombra el campo.

### 4. Descripción en Factura
**Problema:** Descripción mostraba "Caja  Nombre" (vacío para envases).
**Solución:** `php/libredte.php` genera descripción según tipo de producto.

```php
if($producto->tipo == "Caja" && $producto->cantidad_de_envases > 0) {
  $descripcion = $producto->tipo_envase . ' x' . $producto->cantidad_de_envases . ' ' . $producto->nombre;
} else {
  $descripcion = $producto->tipo . ' ' . $producto->cantidad . ' ' . $producto->nombre;
}
```

---

## Requisitos para Facturación

Para que una CajaEnvases se facture correctamente:

1. ✅ El `Producto` debe tener `productos_items` configurados
2. ✅ El `DespachoProducto` debe tener `id_productos` (corregido)
3. ✅ El cliente debe tener `emite_factura = 1`
4. ✅ La descripción se genera automáticamente según tipo

---

*Documento generado: 2025-11-29*
*Última actualización: 2025-11-29*
