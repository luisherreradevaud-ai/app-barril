# Analisis de Trazabilidad: Sistema de Envases
## Cerveza Cocholgue - ERP Cerveceria Artesanal

**Fecha:** 29 de Noviembre, 2025
**Version:** 2.0
**Alcance:** Actualizacion del flujo de trazabilidad incorporando el Sistema de Envases (Latas/Botellas)

---

## Tabla de Contenidos

1. [Resumen de Cambios](#1-resumen-de-cambios)
2. [Nuevo Modelo de Datos](#2-nuevo-modelo-de-datos)
3. [Flujo de Trazabilidad Actualizado](#3-flujo-de-trazabilidad-actualizado)
4. [Analisis del Sistema de Despachos Actual](#4-analisis-del-sistema-de-despachos-actual)
5. [Integracion Requerida: Envases + Despachos](#5-integracion-requerida-envases--despachos)
6. [Plan de Implementacion](#6-plan-de-implementacion)

---

## 1. Resumen de Cambios

### 1.1 Nuevas Entidades Implementadas

| Entidad | Tabla | Descripcion |
|---------|-------|-------------|
| `FormatoDeEnvases` | `formatos_de_envases` | Define formatos de latas/botellas (350ml, 473ml, 500ml, etc.) |
| `BatchDeEnvases` | `batches_de_envases` | Registra cada proceso de envasado desde fermentador/barril |
| `Envase` | `envases` | Cada lata/botella individual con trazabilidad completa |
| `CajaDeEnvases` | `cajas_de_envases` | Agrupa envases en cajas para venta/despacho |

### 1.2 Modificaciones a Entidades Existentes

**Producto** - Nuevos campos:
- `id_formatos_de_envases`: Formato de envase asociado
- `cantidad_de_envases`: Cantidad de envases por caja
- `tipo_envase`: 'Lata' o 'Botella'

---

## 2. Nuevo Modelo de Datos

### 2.1 Diagrama de Entidades

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    SISTEMA DE ENVASES - MODELO DE DATOS                  │
└─────────────────────────────────────────────────────────────────────────┘

FormatoDeEnvases                 Producto (tipo=Caja)
┌──────────────────┐            ┌──────────────────────┐
│ id               │            │ id                   │
│ nombre           │◄───────────│ id_formatos_envases  │
│ tipo (Lata/Bot.) │            │ cantidad_de_envases  │
│ volumen_ml       │            │ tipo_envase          │
│ estado           │            │ nombre               │
└──────────────────┘            └──────────────────────┘
        │                                  │
        │                                  │
        ▼                                  ▼
BatchDeEnvases                    CajaDeEnvases
┌──────────────────┐            ┌──────────────────────┐
│ id               │            │ id                   │
│ tipo (Lata/Bot.) │            │ codigo               │
│ id_batches       │───┐        │ id_productos         │
│ id_activos       │   │        │ cantidad_envases     │
│ id_barriles      │   │        │ estado               │
│ id_batches_activos│  │        └──────────────────────┘
│ id_formatos_env. │   │                  │
│ cantidad_envases │   │                  │
│ volumen_origen_ml│   │                  │
│ rendimiento_ml   │   │                  │
│ estado           │   │                  │
└──────────────────┘   │                  │
        │              │                  │
        │              │                  │
        ▼              │                  ▼
      Envase           │         ┌────────────────┐
┌──────────────────┐   │         │                │
│ id               │   │         │                │
│ id_formatos_env. │   │         ▼                │
│ volumen_ml       │   │    ┌─────────┐           │
│ id_batches_env.  │───┼───►│ Batch   │           │
│ id_batches       │───┘    └─────────┘           │
│ id_barriles      │              ▲               │
│ id_activos       │              │               │
│ id_cajas_envases │──────────────┼───────────────┘
│ estado           │              │
└──────────────────┘              │
                                  │
                            Trazabilidad hacia
                            origen de cerveza
```

### 2.2 Campos de Trazabilidad por Entidad

#### FormatoDeEnvases
```php
public $id;
public $nombre;           // "Lata 350ml", "Botella 500ml"
public $tipo;             // "Lata" | "Botella"
public $volumen_ml;       // 350, 473, 500, etc.
public $estado;           // "activo" | "inactivo"
```

#### BatchDeEnvases
```php
public $id;
public $tipo;                    // "Lata" | "Botella"
public $id_batches;              // Batch de cerveza origen
public $id_activos;              // Fermentador origen (si aplica)
public $id_barriles;             // Barril origen (si aplica)
public $id_batches_activos;      // BatchActivo origen
public $id_formatos_de_envases;  // Formato utilizado
public $id_recetas;              // Receta del batch
public $cantidad_de_envases;     // Total envases creados
public $volumen_origen_ml;       // Volumen disponible antes de envasar
public $rendimiento_ml;          // Volumen efectivamente envasado
public $merma_ml;                // Volumen perdido
public $id_usuarios;             // Usuario que envaso
public $estado;                  // "Cargado en planta" | "Sin envases"
```

#### Envase
```php
public $id;
public $id_formatos_de_envases;  // Formato del envase
public $volumen_ml;              // Volumen del envase
public $id_batches_de_envases;   // Batch de envasado
public $id_batches;              // Batch cerveza origen
public $id_barriles;             // Barril origen (si aplica)
public $id_activos;              // Fermentador origen (si aplica)
public $id_cajas_de_envases;     // Caja asignada (0 si disponible)
public $estado;                  // "Envasado" | "En caja"
```

#### CajaDeEnvases
```php
public $id;
public $codigo;            // "CAJA-251129-AB12"
public $id_productos;      // Producto asociado
public $cantidad_envases;  // Cantidad de envases en la caja
public $id_usuarios;       // Usuario que creo la caja
public $estado;            // "En planta" | "En despacho" | "Entregada"
```

---

## 3. Flujo de Trazabilidad Actualizado

### 3.1 Flujo Completo: Batch → Envase → Caja → Despacho → Entrega

```
┌─────────────────────────────────────────────────────────────────────────┐
│                     FLUJO DE TRAZABILIDAD ENVASES                        │
└─────────────────────────────────────────────────────────────────────────┘

[PRODUCCION]
    Batch #123 (500L, IPA)
        ↓ crea
    BatchActivo (Fermentacion → Maduracion)
        ↓ vincula
    Activo "Fermentador BD-01" (500L)
        ↓

[ENVASADO] ◄── NUEVO SISTEMA
    Modal "Envasar" (inventario-de-productos.php)
        ↓ selecciona origen
    Fermentador BD-01 con 200L disponibles
        ↓ agrega lineas
    Linea 1: 100x Lata 473ml = 47.3L
    Linea 2: 50x Botella 500ml = 25L
        ↓ guarda
    BatchDeEnvases #1 (Lata)
        - id_batches: 123
        - id_activos: BD-01
        - cantidad_de_envases: 100
        - rendimiento_ml: 47300
        ↓ crea
    100x Envase (Lata 473ml)
        - id_batches_de_envases: 1
        - id_batches: 123
        - estado: "Envasado"

    BatchDeEnvases #2 (Botella)
        - id_batches: 123
        - id_activos: BD-01
        - cantidad_de_envases: 50
        - rendimiento_ml: 25000
        ↓ crea
    50x Envase (Botella 500ml)
        - id_batches_de_envases: 2
        - id_batches: 123
        - estado: "Envasado"
        ↓

[EMPAQUE] ◄── NUEVO SISTEMA
    Modal "Crear Caja" (inventario-de-productos.php)
        ↓ selecciona producto
    Producto "Pack 24 Latas IPA"
        - id_formatos_de_envases: 1 (Lata 473ml)
        - cantidad_de_envases: 24
        ↓ asigna envases de batches
    24 envases del BatchDeEnvases #1
        ↓ guarda
    CajaDeEnvases "CAJA-251129-AB12"
        - id_productos: Pack 24 Latas IPA
        - cantidad_envases: 24
        - estado: "En planta"
        ↓ actualiza envases
    24x Envase
        - id_cajas_de_envases: CAJA-251129-AB12
        - estado: "En caja"
        ↓

[DISTRIBUCION] ◄── REQUIERE INTEGRACION
    Despacho #45
        ↓ agrega
    DespachoProducto
        - tipo: 'Caja'
        - id_cajas_de_envases: CAJA-251129-AB12  ◄── NUEVO CAMPO REQUERIDO
        - cantidad: 1
        ↓ cambia estado
    CajaDeEnvases "CAJA-251129-AB12"
        - estado: "En despacho"
        ↓

[ENTREGA] ◄── REQUIERE INTEGRACION
    Entrega #78
        - id_clientes: Restaurant XYZ
        - receptor_nombre: "Juan Perez"
        ↓ crea
    EntregaProducto
        - id_entregas: 78
        - id_cajas_de_envases: CAJA-251129-AB12  ◄── NUEVO CAMPO REQUERIDO
        ↓ actualiza
    CajaDeEnvases "CAJA-251129-AB12"
        - estado: "Entregada"

[TRAZABILIDAD COMPLETA]
Envase individual →
    CajaDeEnvases "CAJA-251129-AB12" →
    BatchDeEnvases #1 (100 latas, 47.3L) →
    BatchActivo (Fermentador BD-01, Maduracion) →
    Batch #123 (Receta: IPA, 500L, 01/11/2025)
```

---

## 4. Analisis del Sistema de Despachos Actual

### 4.1 Estado Actual de `nuevo-despachos.php`

**Tipos de productos soportados:**
1. **Barril** - Vinculado via `id_barriles`
2. **Caja** - Sin vinculacion a `CajaDeEnvases` (solo texto)
3. **CO2** - Barril de CO2
4. **Vasos** - Sin trazabilidad

**Problema identificado:**
```javascript
// nuevo-despachos.php:391-402
$(document).on('click','#agregar-cajas-aceptar',function() {
  productos_lista.push({
    'tipo': 'Caja',
    'cantidad': $('#tipos_caja_select').val(),     // Solo texto "24", "Tripack"
    'tipos_cerveza': $('#tipos_caja_cerveza_select option:selected').text(),
    'codigo': '',                                   // SIN CODIGO DE CAJA
    'id_productos': $('#tipos_caja_cerveza_select').val(),
    'id_barriles': '0',                            // SIN id_cajas_de_envases
    'clasificacion': 'Cerveza'
  });
  renderTable();
});
```

**Gaps criticos:**
- No hay campo `id_cajas_de_envases` en `DespachoProducto`
- No hay vinculacion con el sistema de envases
- Las cajas se agregan como texto sin trazabilidad
- No se puede rastrear que envases especificos fueron a que cliente

### 4.2 Estado Actual de `repartidor.php`

**Flujo actual:**
1. Seleccionar cliente
2. Actualizar estado de barriles existentes
3. Seleccionar productos del despacho (checkboxes)
4. Crear entrega

**Problema:**
- Solo maneja `barriles_estado` para actualizar estados
- No hay manejo de `CajaDeEnvases`
- EntregaProducto no tiene campo para cajas de envases

### 4.3 Estado Actual de `DespachoProducto`

```php
// Campos actuales (inferidos del uso)
public $id_despachos;
public $tipo;           // 'Barril' | 'Caja' | 'Vasos'
public $cantidad;
public $tipos_cerveza;
public $codigo;
public $id_barriles;    // Solo para barriles
public $id_productos;
public $clasificacion;
```

**Campo faltante:**
- `id_cajas_de_envases` - Para vincular con el sistema de envases

---

## 5. Integracion Requerida: Envases + Despachos

### 5.1 Modificaciones a la Base de Datos

```sql
-- 1. Agregar campo a despachos_productos
ALTER TABLE despachos_productos
ADD COLUMN id_cajas_de_envases INT(11) NOT NULL DEFAULT 0
COMMENT 'Caja de envases asociada (si tipo=Caja)';

-- 2. Agregar campo a entregas_productos
ALTER TABLE entregas_productos
ADD COLUMN id_cajas_de_envases INT(11) NOT NULL DEFAULT 0
COMMENT 'Caja de envases entregada';

-- 3. Agregar indices
ALTER TABLE despachos_productos
ADD INDEX idx_id_cajas_de_envases (id_cajas_de_envases);

ALTER TABLE entregas_productos
ADD INDEX idx_id_cajas_de_envases (id_cajas_de_envases);
```

### 5.2 Modificaciones a Clases PHP

#### DespachoProducto.php
```php
// Agregar campo
public $id_cajas_de_envases = 0;

// Agregar metodo
public function getCajaDeEnvases() {
    if($this->id_cajas_de_envases > 0) {
        return new CajaDeEnvases($this->id_cajas_de_envases);
    }
    return null;
}
```

#### EntregaProducto.php
```php
// Agregar campo
public $id_cajas_de_envases = 0;

// Agregar metodo
public function getCajaDeEnvases() {
    if($this->id_cajas_de_envases > 0) {
        return new CajaDeEnvases($this->id_cajas_de_envases);
    }
    return null;
}
```

### 5.3 Modificaciones a `nuevo-despachos.php`

#### PHP - Cargar cajas disponibles
```php
// Agregar al inicio
$cajas_en_planta = CajaDeEnvases::getCajasEnPlanta();
$cajas_latas = CajaDeEnvases::getCajasEnPlantaByTipo('Lata');
$cajas_botellas = CajaDeEnvases::getCajasEnPlantaByTipo('Botella');
```

#### HTML - Nuevo modal para agregar cajas de envases
```html
<div class="modal" tabindex="-1" role="dialog" id="cajasEnvasesModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Caja de Envases</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-6 mb-3">Tipo de Envase:</div>
                    <div class="col-6 mb-3">
                        <select class="form-control" id="tipo_envase_select">
                            <option value="Lata">Lata</option>
                            <option value="Botella">Botella</option>
                        </select>
                    </div>
                    <div class="col-6 mb-3">Caja:</div>
                    <div class="col-6 mb-3">
                        <select class="form-control" id="caja_envases_select">
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="agregar-caja-envases-aceptar" data-bs-dismiss="modal">Agregar</button>
            </div>
        </div>
    </div>
</div>
```

#### JavaScript - Logica para cajas de envases
```javascript
var cajas_latas = <?= json_encode($cajas_latas, JSON_PRETTY_PRINT); ?>;
var cajas_botellas = <?= json_encode($cajas_botellas, JSON_PRETTY_PRINT); ?>;

function armarCajasEnvasesSelect() {
    var tipo = $('#tipo_envase_select').val();
    var cajas = tipo == 'Lata' ? cajas_latas : cajas_botellas;

    $('#caja_envases_select').empty();
    cajas.forEach(function(caja) {
        var producto = // obtener producto
        var html = '<option value="' + caja.id + '" data-producto="' + producto.nombre + '">';
        html += caja.codigo + ' - ' + producto.nombre + ' (' + caja.cantidad_envases + ' uds)';
        html += '</option>';
        $('#caja_envases_select').append(html);
    });
}

$(document).on('click', '#agregar-caja-envases-aceptar', function() {
    var caja_id = $('#caja_envases_select').val();
    var caja_text = $('#caja_envases_select option:selected').text();
    var producto_nombre = $('#caja_envases_select option:selected').data('producto');

    productos_lista.push({
        'tipo': 'CajaEnvases',
        'cantidad': 1,
        'tipos_cerveza': producto_nombre,
        'codigo': caja_text.split(' - ')[0],
        'id_cajas_de_envases': caja_id,
        'id_productos': 0,
        'id_barriles': 0,
        'clasificacion': 'Cerveza'
    });
    renderTable();
});
```

### 5.4 Modificaciones a `ajax_guardarDespacho.php`

```php
// Al procesar productos del despacho
foreach($_POST['despacho'] as $producto) {
    $dp = new DespachoProducto();
    $dp->id_despachos = $despacho->id;
    $dp->tipo = $producto['tipo'];
    $dp->cantidad = $producto['cantidad'];
    $dp->tipos_cerveza = $producto['tipos_cerveza'];
    $dp->codigo = $producto['codigo'];
    $dp->id_barriles = isset($producto['id_barriles']) ? $producto['id_barriles'] : 0;
    $dp->id_productos = isset($producto['id_productos']) ? $producto['id_productos'] : 0;
    $dp->clasificacion = $producto['clasificacion'];

    // NUEVO: Manejar cajas de envases
    if($producto['tipo'] == 'CajaEnvases' && isset($producto['id_cajas_de_envases'])) {
        $dp->id_cajas_de_envases = $producto['id_cajas_de_envases'];

        // Cambiar estado de la caja
        $caja = new CajaDeEnvases($producto['id_cajas_de_envases']);
        $caja->estado = "En despacho";
        $caja->save();
    }

    $dp->save();

    // Cambiar estado del barril si aplica
    if($producto['tipo'] == 'Barril' && $producto['id_barriles'] > 0) {
        $barril = new Barril($producto['id_barriles']);
        $barril->registrarCambioDeEstado("En despacho");
    }
}
```

### 5.5 Modificaciones a `repartidor.php`

#### PHP - Agregar datos de cajas en despacho
```php
// Cargar cajas en despacho del repartidor
$cajas_en_despacho = array();
foreach($despachos as $despacho) {
    $productos = DespachoProducto::getAll("WHERE id_despachos='" . $despacho->id . "'");
    foreach($productos as $dp) {
        if($dp->id_cajas_de_envases > 0) {
            $caja = new CajaDeEnvases($dp->id_cajas_de_envases);
            $cajas_en_despacho[] = $caja;
        }
    }
}
```

### 5.6 Modificaciones a `ajax_guardarEntrega.php`

```php
// Al crear EntregaProducto
foreach($ids_despachos_productos as $id_dp) {
    $dp = new DespachoProducto($id_dp);

    $ep = new EntregaProducto();
    $ep->id_entregas = $entrega->id;
    $ep->id_despachos_productos = $id_dp;
    $ep->tipo = $dp->tipo;
    $ep->cantidad = $dp->cantidad;
    $ep->codigo = $dp->codigo;
    $ep->id_barriles = $dp->id_barriles;

    // NUEVO: Manejar cajas de envases
    if($dp->id_cajas_de_envases > 0) {
        $ep->id_cajas_de_envases = $dp->id_cajas_de_envases;

        // Cambiar estado de la caja
        $caja = new CajaDeEnvases($dp->id_cajas_de_envases);
        $caja->estado = "Entregada";
        $caja->save();
    }

    $ep->save();
}
```

---

## 6. Plan de Implementacion

### 6.1 Orden de Tareas

| # | Tarea | Archivo(s) | Prioridad |
|---|-------|------------|-----------|
| 1 | Crear migracion SQL | `db/migrations/003_despachos_envases.sql` | Alta |
| 2 | Modificar DespachoProducto.php | `php/classes/DespachoProducto.php` | Alta |
| 3 | Modificar EntregaProducto.php | `php/classes/EntregaProducto.php` | Alta |
| 4 | Actualizar nuevo-despachos.php | `templates/nuevo-despachos.php` | Alta |
| 5 | Actualizar ajax_guardarDespacho.php | `ajax/ajax_guardarDespacho.php` | Alta |
| 6 | Actualizar repartidor.php | `templates/repartidor.php` | Media |
| 7 | Actualizar ajax_guardarEntrega.php | `ajax/ajax_guardarEntrega.php` | Media |
| 8 | Actualizar central-despacho.php | `templates/central-despacho.php` | Baja |

### 6.2 SQL de Migracion

```sql
-- =============================================
-- Migracion: Integracion Envases con Despachos
-- Fecha: 2025-11-29
-- =============================================

-- 1. Agregar campo a despachos_productos
ALTER TABLE despachos_productos
ADD COLUMN id_cajas_de_envases INT(11) NOT NULL DEFAULT 0
COMMENT 'Caja de envases asociada (si tipo=CajaEnvases)'
AFTER id_productos;

-- 2. Agregar campo a entregas_productos
ALTER TABLE entregas_productos
ADD COLUMN id_cajas_de_envases INT(11) NOT NULL DEFAULT 0
COMMENT 'Caja de envases entregada'
AFTER id_barriles;

-- 3. Agregar indices
ALTER TABLE despachos_productos
ADD INDEX idx_id_cajas_de_envases (id_cajas_de_envases);

ALTER TABLE entregas_productos
ADD INDEX idx_id_cajas_de_envases (id_cajas_de_envases);
```

### 6.3 Flujo de Estados de CajaDeEnvases

```
"En planta" → (agregar a despacho) → "En despacho" → (entregar) → "Entregada"
```

### 6.4 Trazabilidad Completa Resultante

Con la integracion completa, se podra rastrear:

```
Cliente "Restaurant XYZ"
    ↓ recibio
Entrega #78 (15:30 hrs, 29/11/2025)
    ↓ contiene
EntregaProducto
    ↓ incluye
CajaDeEnvases "CAJA-251129-AB12"
    ↓ contiene
24x Envase (Lata 473ml)
    ↓ provienen de
BatchDeEnvases #1 (100 latas envasadas)
    ↓ extraido de
BatchActivo (Fermentador BD-01, 200L)
    ↓ pertenece a
Batch #123 (IPA, 500L, Receta Premium)
    ↓ usa
Insumos: Malta Pale, Lupulo Cascade, Levadura US-05
```

---

## Resumen

El sistema de envases esta **completamente implementado** para:
- Envasado (fermentador/barril → latas/botellas)
- Empaque (envases → cajas)

**Pendiente de implementar:**
- Integracion con sistema de despachos (`nuevo-despachos.php`)
- Integracion con sistema de entregas (`repartidor.php`)
- Campos `id_cajas_de_envases` en `DespachoProducto` y `EntregaProducto`

La implementacion de la integracion permitira trazabilidad completa desde el insumo hasta el cliente final para productos envasados.
