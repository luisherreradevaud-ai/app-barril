# Análisis Completo de Trazabilidad del Sistema Barril.cl

## Índice
1. [Resumen Ejecutivo](#resumen-ejecutivo)
2. [Arquitectura de Datos](#arquitectura-de-datos)
3. [Flujo de Producción - Barriles](#flujo-de-producción---barriles)
4. [Flujo de Producción - Envases](#flujo-de-producción---envases)
5. [Flujo de Despacho](#flujo-de-despacho)
6. [Flujo de Entrega](#flujo-de-entrega)
7. [Flujo de Facturación](#flujo-de-facturación)
8. [Diagramas de Estados](#diagramas-de-estados)
9. [Relaciones entre Entidades](#relaciones-entre-entidades)
10. [Puntos Críticos de Trazabilidad](#puntos-críticos-de-trazabilidad)
11. [Archivos Clave por Proceso](#archivos-clave-por-proceso)
12. [Interfaz de Inventario de Productos](#interfaz-de-inventario-de-productos)

---

## Resumen Ejecutivo

El sistema Barril.cl gestiona dos líneas de producción principales:

1. **Línea de Barriles**: Producción tradicional en barriles (20L, 30L, 50L)
2. **Línea de Envases**: Producción de latas y botellas (cajas de envases)

Ambas líneas comparten el proceso de elaboración de cerveza (Batch) pero divergen en el envasado final y la distribución.

### Cadena de Trazabilidad Completa

```
RECETA → BATCH → FERMENTACIÓN → PRODUCTO FINAL
                                      ↓
                    ┌─────────────────┴─────────────────┐
                    ↓                                   ↓
               BARRIL                        BATCH DE ENVASES
                    ↓                                   ↓
                    ↓                               ENVASE (individual)
                    ↓                                   ↓
                    ↓                          CAJA DE ENVASES
                    ↓                                   ↓
                    └──────────────┬────────────────────┘
                                   ↓
                            DESPACHO PRODUCTO
                                   ↓
                            ENTREGA PRODUCTO
                                   ↓
                            DTE (FACTURA)
```

---

## Arquitectura de Datos

### Clases Principales y sus Tablas

| Clase | Tabla | Descripción |
|-------|-------|-------------|
| `Receta` | `recetas` | Receta de cerveza con ingredientes |
| `Batch` | `batches` | Proceso de cocción de cerveza |
| `BatchActivo` | `batches_activos` | Asignación batch→fermentador |
| `BatchDeEnvases` | `batches_de_envases` | Registro de envasado (latas/botellas) |
| `Barril` | `barriles` | Barril físico con trazabilidad |
| `Envase` | `envases` | Envase individual (lata/botella) |
| `CajaDeEnvases` | `cajas_de_envases` | Caja con múltiples envases |
| `Producto` | `productos` | Producto comercial (con items de facturación) |
| `ProductoItem` | `productos_items` | Items de factura por producto (IVA/ILA) |
| `FormatoDeEnvases` | `formatos_de_envases` | Formato (473ml, 355ml, etc.) |
| `Despacho` | `despachos` | Despacho asignado a repartidor |
| `DespachoProducto` | `despachos_productos` | Productos en despacho |
| `Entrega` | `entregas` | Entrega al cliente |
| `EntregaProducto` | `entregas_productos` | Productos entregados |
| `DTE` | `dte` | Documento tributario electrónico |
| `Cliente` | `clientes` | Cliente con datos tributarios |

### Campos de Trazabilidad por Entidad

#### Batch (Cocción)
```php
class Batch {
    public $id;                      // ID único
    public $batch_date;              // Fecha de cocción
    public $id_recetas;              // → Receta
    public $batch_nombre;            // Nombre descriptivo
    public $batch_litros;            // Volumen total
    public $fermentacion_id_activos; // → Activo (fermentador)
    // ... más campos de proceso
}
```

#### BatchActivo (Fermentador)
```php
class BatchActivo {
    public $id;
    public $id_batches;    // → Batch
    public $id_activos;    // → Activo (fermentador físico)
    public $estado;
    public $litraje;
}
```

#### BatchDeEnvases (Envasado)
```php
class BatchDeEnvases {
    public $id;
    public $tipo;                    // 'Lata' | 'Botella'
    public $id_batches;              // → Batch de cerveza
    public $id_activos;              // → Activo origen
    public $id_barriles;             // → Barril origen (si aplica)
    public $id_batches_activos;      // → BatchActivo
    public $id_formatos_de_envases;  // → FormatoDeEnvases
    public $id_recetas;              // → Receta
    public $cantidad_de_envases;     // Total envasado
    public $volumen_origen_ml;       // Volumen usado
    public $rendimiento_ml;          // Volumen efectivo
    public $merma_ml;                // Pérdida
    public $id_usuarios;             // → Usuario que envasó
    public $estado;                  // Estado del batch
}
```

#### Envase (Individual)
```php
class Envase {
    public $id;
    public $id_formatos_de_envases;   // → FormatoDeEnvases
    public $volumen_ml;               // Volumen del envase
    public $id_batches_de_envases;    // → BatchDeEnvases
    public $id_batches;               // → Batch de cerveza
    public $id_barriles;              // → Barril origen
    public $id_activos;               // → Activo origen
    public $id_cajas_de_envases;      // → CajaDeEnvases (0 si libre)
    public $estado;                   // 'Envasado' | 'En caja'
}
```

#### CajaDeEnvases
```php
class CajaDeEnvases {
    public $id;
    public $codigo;             // Código único (CAJA-YYMMDD-XXXX)
    public $id_productos;       // → Producto comercial
    public $cantidad_envases;   // Total de envases
    public $id_usuarios;        // → Usuario que armó
    public $estado;             // 'En planta' | 'En despacho' | 'Entregada'
}
```

#### Barril
```php
class Barril {
    public $id;
    public $tipo_barril;        // '20L' | '30L' | '50L'
    public $codigo;             // Código único
    public $estado;             // Estado actual
    public $id_clientes;        // → Cliente (si está en terreno)
    public $id_batches;         // → Batch
    public $id_activos;         // → Activo
    public $id_batches_activos; // → BatchActivo
    public $clasificacion;      // Tipo de cerveza
    public $litraje;            // Capacidad
    public $litros_cargados;    // Contenido actual
}
```

#### Producto
```php
class Producto {
    public $id;
    public $nombre;                   // Nombre comercial
    public $id_recetas;               // → Receta (NULL para mixtos)
    public $tipo;                     // 'Barril' | 'Caja'
    public $cantidad;                 // Cantidad (litros para barril)
    public $monto;                    // Precio neto
    public $id_formatos_de_envases;   // → FormatoDeEnvases
    public $cantidad_de_envases;      // Envases por caja
    public $tipo_envase;              // 'Lata' | 'Botella'
    public $es_mixto;                 // 1 = acepta múltiples recetas
    public $productos_items;          // Items para facturación
}
```

#### ProductoItem (Facturación)
```php
class ProductoItem {
    public $id;
    public $nombre;        // Nombre del item en factura
    public $id_productos;  // → Producto
    public $monto_bruto;   // Monto bruto
    public $impuesto;      // 'IVA' | 'IVA + ILA'
}
```

---

## Flujo de Producción - Barriles

### 1. Creación del Batch

**Archivo**: `templates/batch.php`, `ajax/ajax_guardarBatch.php`
**Clase**: `Batch.php`

```
1. Seleccionar Receta
2. Ingresar datos de cocción
3. Asignar Fermentador (BatchActivo)
4. Descontar insumos de bodega
5. Crear registro Batch
```

**Relaciones creadas**:
- `Batch.id_recetas` → `Receta.id`
- `BatchActivo.id_batches` → `Batch.id`
- `BatchActivo.id_activos` → `Activo.id`
- `BatchInsumo.id_batches` → `Batch.id`

### 2. Proceso de Fermentación

**Clase**: `BatchActivo.php`, `BatchLupulizacion.php`, `BatchEnfriado.php`

El batch pasa por etapas registradas en la base de datos:
- Lupulización (opcional, dry-hop)
- Enfriado
- Traspasos entre fermentadores

### 3. Llenado de Barril

**Archivo**: `templates/barriles.php`
**Clase**: `Barril.php`

```
1. Seleccionar Barril (En planta)
2. Asignar Batch/Fermentador
3. Registrar litros cargados
4. Cambiar estado a "En sala de frio"
```

**Actualización**:
```php
$barril->id_batches = $batch->id;
$barril->estado = "En sala de frio";
$barril->litros_cargados = $litros;
$barril->registrarCambioDeEstado();
$barril->save();
```

### Estados del Barril
```
En planta → En sala de frio → En despacho → En terreno → [Devuelto a planta]
                                                       → [Pinchado]
                                                       → [Perdido]
```

---

## Flujo de Producción - Envases

### 1. Envasado (Creación de BatchDeEnvases + Envases)

**Archivo**: `ajax/ajax_envasar.php`
**Clases**: `BatchDeEnvases.php`, `Envase.php`, `FormatoDeEnvases.php`

**Input**:
```javascript
{
    origen_tipo: 'fermentador' | 'barril',
    id_batches_activos: 123,  // Si origen es fermentador
    id_barriles: 456,         // Si origen es barril
    id_batches: 789,
    id_recetas: 1,
    lineas: [
        { tipo: 'Lata', id_formatos_de_envases: 4, cantidad: 100, volumen_ml: 473 },
        { tipo: 'Botella', id_formatos_de_envases: 5, cantidad: 50, volumen_ml: 330 }
    ]
}
```

**Proceso**:
```php
// 1. Crear BatchDeEnvases por cada línea
$batchDeEnvases = new BatchDeEnvases();
$batchDeEnvases->tipo = 'Lata';
$batchDeEnvases->id_batches = $id_batches;
$batchDeEnvases->id_activos = $id_activos;
$batchDeEnvases->id_formatos_de_envases = $id_formato;
$batchDeEnvases->id_recetas = $id_recetas;
$batchDeEnvases->cantidad_de_envases = 100;
$batchDeEnvases->save();

// 2. Crear cada Envase individual
for($i = 0; $i < $cantidad; $i++) {
    $envase = new Envase();
    $envase->id_batches_de_envases = $batchDeEnvases->id;
    $envase->id_batches = $id_batches;
    $envase->id_formatos_de_envases = $id_formato;
    $envase->estado = "Envasado";
    $envase->save();
}

// 3. Vaciar origen (fermentador o barril)
```

**Trazabilidad creada**:
```
Receta → Batch → BatchActivo → BatchDeEnvases → Envase (x N)
                    ↓
                id_activos → Activo (fermentador físico)
```

### 2. Creación de Caja de Envases

**Archivo**: `ajax/ajax_crearCajaDeEnvases.php`
**Clases**: `CajaDeEnvases.php`, `Envase.php`, `Producto.php`

**Input**:
```javascript
{
    id_productos: 5,  // Producto tipo Caja (ej: "Pack 24 Latas IPA")
    asignaciones: {
        "123": 12,    // 12 envases del BatchDeEnvases #123
        "456": 12     // 12 envases del BatchDeEnvases #456 (caja mixta)
    }
}
```

**Proceso**:
```php
// 1. Validar producto
$producto = new Producto($id_productos);
// producto.tipo_envase = 'Lata'
// producto.cantidad_de_envases = 24
// producto.id_formatos_de_envases = 4 (473ml)

// 2. Crear caja
$caja = new CajaDeEnvases();
$caja->generarCodigo();  // "CAJA-251129-A1B2"
$caja->id_productos = $id_productos;
$caja->cantidad_envases = 24;
$caja->estado = "En planta";
$caja->save();

// 3. Asignar envases a la caja
foreach($asignaciones as $id_batch => $cantidad) {
    $envases = Envase::getDisponiblesPorBatch($id_batch, $cantidad);
    foreach($envases as $envase) {
        $envase->id_cajas_de_envases = $caja->id;
        $envase->estado = "En caja";
        $envase->save();
    }
}
```

**Trazabilidad resultante**:
```
Envase.id_cajas_de_envases → CajaDeEnvases.id
CajaDeEnvases.id_productos → Producto.id
```

### Estados del Envase
```
Envasado → En caja → [Liberado vuelve a Envasado]
```

### Estados de la Caja de Envases
```
En planta → En despacho → Entregada
          → eliminado (revertida)
```

---

## Flujo de Despacho

### Creación de Despacho

**Archivo**: `ajax/ajax_guardarDespacho.php`
**Clases**: `Despacho.php`, `DespachoProducto.php`

**Input** (desde UI de administrador):
```javascript
{
    id_usuarios_repartidor: 10,
    despacho: [
        {
            tipo: 'Barril',
            id_barriles: 50,
            cantidad: 1,
            tipos_cerveza: 'IPA',
            codigo: 'B-001'
        },
        {
            tipo: 'CajaEnvases',
            id_cajas_de_envases: 25,
            id_productos: 5,  // CRÍTICO: debe obtenerse de la caja
            cantidad: 1,
            codigo: 'CAJA-251129-A1B2'
        }
    ]
}
```

**Proceso**:
```php
// 1. Crear Despacho
$despacho = new Despacho();
$despacho->id_usuarios_repartidor = $_POST['id_usuarios_repartidor'];
$despacho->estado = "En despacho";
$despacho->save();

// 2. Crear DespachoProducto para cada item
foreach($_POST['despacho'] as $producto) {
    $dp = new DespachoProducto();
    $dp->id_despachos = $despacho->id;
    $dp->tipo = $producto['tipo'];  // 'Barril' | 'CajaEnvases'

    if($producto['tipo'] == "CajaEnvases") {
        $dp->id_cajas_de_envases = $producto['id_cajas_de_envases'];

        // CRÍTICO: Obtener id_productos desde la caja
        $caja_temp = new CajaDeEnvases($producto['id_cajas_de_envases']);
        $dp->id_productos = $caja_temp->id_productos;

        // Cambiar estado de caja
        $caja_temp->estado = "En despacho";
        $caja_temp->save();
    }

    if($producto['tipo'] == "Barril") {
        $dp->id_barriles = $producto['id_barriles'];

        // Cambiar estado de barril
        $barril = new Barril($producto['id_barriles']);
        $barril->estado = "En despacho";
        $barril->registrarCambioDeEstado();
        $barril->save();
    }

    $dp->save();
}
```

**Relaciones creadas**:
```
DespachoProducto.id_despachos → Despacho.id
DespachoProducto.id_barriles → Barril.id (si es barril)
DespachoProducto.id_cajas_de_envases → CajaDeEnvases.id (si es caja)
DespachoProducto.id_productos → Producto.id (CRÍTICO para facturación)
```

### Eliminación de Despacho (Revertir)

**Archivo**: `php/classes/Despacho.php` → `deleteSpecifics()`

```php
public function deleteSpecifics($values) {
    $despachos_productos = DespachoProducto::getAll("WHERE id_despachos='".$this->id."'");

    foreach($despachos_productos as $dp) {
        // Revertir Barril
        if($dp->tipo == "Barril") {
            $barril = new Barril($dp->id_barriles);
            $barril->estado = "En planta";
            $barril->id_clientes = 0;
            $barril->id_batches = 0;
            $barril->registrarCambioDeEstado();
            $barril->save();
        }

        // Revertir CajaDeEnvases
        if($dp->tipo == "CajaEnvases" && $dp->id_cajas_de_envases > 0) {
            $caja = new CajaDeEnvases($dp->id_cajas_de_envases);
            $caja->estado = "En planta";
            $caja->save();
        }

        $dp->delete();
    }
}
```

---

## Flujo de Entrega

### Proceso de Entrega (Repartidor)

**Archivo**: `templates/repartidor.php`, `ajax/ajax_guardarEntrega.php`
**Clases**: `Entrega.php`, `EntregaProducto.php`

### UI del Repartidor

1. **Seleccionar Cliente** (destino de la entrega)
2. **Actualizar Estado de Barriles** (del cliente, si tiene barriles pendientes)
3. **Seleccionar productos a entregar** (checkboxes de DespachoProducto)
4. **Confirmar entrega** con nombre de receptor

### Procesamiento de Entrega

**Input**:
```javascript
{
    ids_despachos_productos: [1, 2, 3],
    ids_cajas_envases: [25],
    id_clientes: 100,
    id_usuarios_repartidor: 10,
    cantidad_vasos: 0,
    receptor_nombre: "Juan Pérez",
    rand_int: 123456789,  // Prevención de duplicados
    barriles_estado: {
        "50": "Devuelto a planta",
        "51": "En terreno"
    }
}
```

**Proceso**:
```php
// 1. Procesar barriles devueltos (del cliente)
foreach($_POST['barriles_estado'] as $id_barriles => $estado) {
    $barril = new Barril($id_barriles);
    if($estado === "Devuelto a planta") {
        $barril->estado = "En planta";
        $barril->id_clientes = 0;
        $barril->id_batches = 0;
        $barril->litros_cargados = 0;
    } else {
        $barril->estado = $estado;  // "En terreno", "Pinchado", "Perdido"
    }
    $barril->registrarCambioDeEstado();
    $barril->save();
}

// 2. Crear Entrega
$entrega = new Entrega();
$entrega->id_clientes = $_POST['id_clientes'];
$entrega->id_usuarios_repartidor = $_POST['id_usuarios_repartidor'];
$entrega->estado = "Entregada";
$entrega->receptor_nombre = $_POST['receptor_nombre'];
$entrega->save();

// 3. Convertir DespachoProducto → EntregaProducto
$monto_total = 0;
foreach($_POST['ids_despachos_productos'] as $id_dp) {
    $dp = new DespachoProducto($id_dp);

    // Obtener precio del producto para este cliente
    $producto = new Producto($dp->id_productos);
    $precio = $producto->getClienteProductoPrecio($cliente->id);

    // Crear EntregaProducto
    $ep = new EntregaProducto();
    $ep->setPropertiesNoId($dp);  // Copiar campos
    $ep->id_entregas = $entrega->id;
    $ep->estado = "Entregada";
    $ep->monto = $precio;
    $ep->save();

    // Eliminar DespachoProducto
    $dp->delete();

    // Actualizar estado según tipo
    if($ep->tipo == "Barril") {
        $barril = new Barril($ep->id_barriles);
        $barril->estado = "En terreno";
        $barril->id_clientes = $entrega->id_clientes;
        $barril->registrarCambioDeEstado();
        $barril->save();
    }

    if($ep->tipo == "CajaEnvases" && $ep->id_cajas_de_envases > 0) {
        $caja = new CajaDeEnvases($ep->id_cajas_de_envases);
        $caja->estado = "Entregada";
        $caja->save();
    }

    $monto_total += $precio;
}

$entrega->monto = $monto_total;
$entrega->save();
```

**Relaciones creadas**:
```
EntregaProducto.id_entregas → Entrega.id
EntregaProducto.id_productos → Producto.id
EntregaProducto.id_barriles → Barril.id (si es barril)
EntregaProducto.id_cajas_de_envases → CajaDeEnvases.id (si es caja)
Entrega.id_clientes → Cliente.id
```

---

## Flujo de Facturación

### Generación de DTE (LibreDTE)

**Archivo**: `php/libredte.php`, `ajax/ajax_guardarEntrega.php`
**Clases**: `DTE.php`, `ProductoItem.php`

### Condición para Facturar

```php
if($cliente->emite_factura == 1) {
    // Generar factura electrónica
}
```

### Construcción del Payload

**Función**: `dataEntrada2json($cliente, $entrega, $entrega_productos)`

```php
function dataEntrada2json($cliente, $entrega, $entrega_productos) {

    // 1. Agrupar productos iguales
    $entrega_productos_2 = array();
    foreach($entrega_productos as $ep) {
        // Si ya existe, incrementar QtyItem
        // Si no existe, agregar con QtyItem = 1
    }

    // 2. Generar detalles de factura
    $detalles = array();
    foreach($entrega_productos_2 as $ep) {
        $producto = new Producto($ep->id_productos);
        $precio = $producto->getClienteProductoPrecio($cliente->id);

        // Generar descripción según tipo
        if($producto->tipo == "Caja" && $producto->cantidad_de_envases > 0) {
            // Producto de envases: "Lata x24 Pack IPA"
            $descripcion = $producto->tipo_envase . ' x' .
                           $producto->cantidad_de_envases . ' ' .
                           $producto->nombre;
        } else {
            // Producto barril: "Barril 30L IPA"
            $descripcion = $producto->tipo . ' ' .
                           $producto->cantidad . ' ' .
                           $producto->nombre;
        }

        // 3. Procesar items de facturación (IVA/ILA)
        foreach($producto->productos_items as $pi) {
            if($pi->impuesto == "IVA + ILA") {
                // Item con ILA (Impuesto a Licores)
                $detalles[] = '{
                    "IndExe": false,
                    "NmbItem": "'.$pi->nombre.'",
                    "DscItem": "'.$descripcion.'",
                    "QtyItem": "'.$ep->QtyItem.'",
                    "PrcItem": "'.$pi->monto_bruto.'",
                    "CodImpAdic": "26"
                }';
            } else {
                // Item solo con IVA
                $detalles[] = '{
                    "IndExe": false,
                    "NmbItem": "'.$pi->nombre.'",
                    "QtyItem": "'.$ep->QtyItem.'",
                    "PrcItem": "'.$pi->monto_bruto.'"
                }';
            }
        }
    }

    // 4. Construir JSON final
    return '{
        "Encabezado": {
            "IdDoc": { "TipoDTE": 33 },
            "Emisor": { "RUTEmisor": "'.$rut_emisor.'" },
            "Receptor": {
                "RUTRecep": "'.$cliente->RUT.'",
                "RznSocRecep": "'.$cliente->RznSoc.'",
                "GiroRecep": "'.$cliente->Giro.'",
                "DirRecep": "'.$cliente->Dir.'",
                "CmnaRecep": "'.$cliente->Cmna.'"
            }
        },
        "Detalle": ['.$detalle.']
    }';
}
```

### Proceso de Emisión

```php
// 1. Generar payload
$data = dataEntrada2json($cliente, $entrega, $entrega_productos);

// 2. Emitir DTE (normalizar)
$body = LIBREDTE_emison($data);

// 3. Generar DTE (firmar y enviar al SII)
$response_string = LIBREDTE_generar($body);

// 4. Guardar DTE
$dte = new DTE();
$dte->setProperties(json_decode($response_string));
$dte->id_entregas = $entrega->id;
$dte->save();

// 5. Actualizar entrega con folio
$entrega->factura = $dte->folio;
$entrega->save();

// 6. Enviar correo
LIBREDTE_enviarCorreo($dte->folio, $cliente->email);
```

### Requisitos para Facturación Correcta

1. **Producto debe tener `id_productos` válido** en EntregaProducto
2. **Producto debe tener `productos_items`** configurados
3. **Cliente debe tener datos tributarios** (RUT, RznSoc, Giro, Dir, Cmna)
4. **Cliente debe tener `emite_factura = 1`**

### Estructura de ProductoItem

```php
// Ejemplo para un Pack de 24 Latas IPA
// Total: $29,990

ProductoItem #1:
- nombre: "Pack Cerveza"
- monto_bruto: 21490  // Precio base
- impuesto: "IVA + ILA"

ProductoItem #2:
- nombre: "Envase"
- monto_bruto: 8500   // Costo envase
- impuesto: "IVA"
```

---

## Diagramas de Estados

### Barril

```
┌─────────────┐
│  En planta  │ ◄──────────────────────────────────────┐
└──────┬──────┘                                        │
       │ Llenado                                       │
       ↓                                               │
┌──────────────────┐                                   │
│  En sala de frio │                                   │
└────────┬─────────┘                                   │
         │ Despacho                                    │
         ↓                                             │
┌─────────────────┐                                    │
│   En despacho   │                                    │
└────────┬────────┘                                    │
         │ Entrega                                     │
         ↓                                             │
┌─────────────────┐     Devuelto a planta              │
│   En terreno    │ ───────────────────────────────────┘
└────────┬────────┘
         │
         ├──→ Pinchado
         │
         └──→ Perdido
```

### CajaDeEnvases

```
┌─────────────┐
│  En planta  │ ◄────────────────────┐
└──────┬──────┘                      │
       │                             │
       ├───→ eliminado               │ Revertir Despacho
       │     (liberarEnvases)        │
       │                             │
       │ Crear Despacho              │
       ↓                             │
┌─────────────────┐                  │
│   En despacho   │ ─────────────────┘
└────────┬────────┘
         │ Entrega
         ↓
┌─────────────────┐
│   Entregada     │
└─────────────────┘
```

### Envase

```
┌─────────────┐
│  Envasado   │ ◄──────────────┐
└──────┬──────┘                │
       │                       │
       │ Asignar a caja        │ Liberar de caja
       ↓                       │
┌─────────────┐                │
│   En caja   │ ───────────────┘
└─────────────┘
```

---

## Relaciones entre Entidades

### Modelo Entidad-Relación Simplificado

```
                              ┌─────────────┐
                              │   Receta    │
                              └──────┬──────┘
                                     │
                                     │ 1:N
                                     ↓
┌───────────┐    1:N     ┌───────────────────┐     1:N      ┌────────────────┐
│  Activo   │ ◄──────────│      Batch        │ ────────────→│   BatchInsumo  │
└─────┬─────┘            └─────────┬─────────┘              └────────────────┘
      │                            │
      │ 1:N                        │ 1:N
      ↓                            ↓
┌─────────────────┐        ┌───────────────────┐
│   BatchActivo   │ ◄──────│  (fermentación)   │
└─────────────────┘        └─────────┬─────────┘
                                     │
                    ┌────────────────┼────────────────┐
                    ↓                                 ↓
            ┌───────────────┐               ┌─────────────────────┐
            │    Barril     │               │   BatchDeEnvases    │
            └───────┬───────┘               └──────────┬──────────┘
                    │                                  │
                    │                                  │ 1:N
                    │                                  ↓
                    │                         ┌───────────────┐
                    │                         │    Envase     │
                    │                         └───────┬───────┘
                    │                                 │ N:1
                    │                                 ↓
                    │                         ┌─────────────────┐
                    │                         │  CajaDeEnvases  │
                    │                         └────────┬────────┘
                    │                                  │
                    └────────────────┬─────────────────┘
                                     ↓
                            ┌─────────────────────┐
                            │   DespachoProducto  │
                            └──────────┬──────────┘
                                       │ N:1
                                       ↓
                              ┌─────────────────┐
                              │    Despacho     │
                              └────────┬────────┘
                                       │
                                       ↓
                            ┌─────────────────────┐
                            │   EntregaProducto   │
                            └──────────┬──────────┘
                                       │ N:1
                                       ↓
                              ┌─────────────────┐     1:1      ┌───────┐
                              │    Entrega      │ ────────────→│  DTE  │
                              └────────┬────────┘              └───────┘
                                       │ N:1
                                       ↓
                              ┌─────────────────┐
                              │    Cliente      │
                              └─────────────────┘
```

### Relaciones Clave para Trazabilidad

| Desde | Campo | Hacia | Propósito |
|-------|-------|-------|-----------|
| `Batch` | `id_recetas` | `Receta` | Origen de la cerveza |
| `BatchActivo` | `id_batches` | `Batch` | Batch en fermentador |
| `BatchActivo` | `id_activos` | `Activo` | Fermentador físico |
| `BatchDeEnvases` | `id_batches` | `Batch` | Batch de cerveza origen |
| `BatchDeEnvases` | `id_recetas` | `Receta` | Receta directa |
| `BatchDeEnvases` | `id_formatos_de_envases` | `FormatoDeEnvases` | Tipo de envase |
| `Envase` | `id_batches_de_envases` | `BatchDeEnvases` | Lote de envasado |
| `Envase` | `id_cajas_de_envases` | `CajaDeEnvases` | Caja contenedora |
| `CajaDeEnvases` | `id_productos` | `Producto` | Producto comercial |
| `Barril` | `id_batches` | `Batch` | Batch de cerveza |
| `DespachoProducto` | `id_productos` | `Producto` | **CRÍTICO para facturación** |
| `DespachoProducto` | `id_barriles` | `Barril` | Barril despachado |
| `DespachoProducto` | `id_cajas_de_envases` | `CajaDeEnvases` | Caja despachada |
| `EntregaProducto` | `id_productos` | `Producto` | **CRÍTICO para facturación** |
| `DTE` | `id_entregas` | `Entrega` | Factura de entrega |

---

## Puntos Críticos de Trazabilidad

### 1. Asignación de `id_productos` en Despacho

**Ubicación**: `ajax/ajax_guardarDespacho.php:25-32`

```php
if($producto['tipo'] == "CajaEnvases" && isset($producto['id_cajas_de_envases'])) {
    $dp->id_cajas_de_envases = $producto['id_cajas_de_envases'];
    // CRÍTICO: Obtener id_productos desde la caja
    $caja_temp = new CajaDeEnvases($producto['id_cajas_de_envases']);
    if($caja_temp->id_productos > 0) {
        $dp->id_productos = $caja_temp->id_productos;
    }
}
```

**Sin este código**: La facturación falla porque `id_productos = 0`.

### 2. Transferencia DespachoProducto → EntregaProducto

**Ubicación**: `ajax/ajax_guardarEntrega.php:75-82`

```php
$ep = new EntregaProducto;
$ep->setPropertiesNoId($dp);  // Copia TODOS los campos
$ep->id_entregas = $obj->id;
```

**`setPropertiesNoId()` copia**:
- `tipo`
- `cantidad`
- `tipos_cerveza`
- `id_barriles`
- `id_productos` ← CRÍTICO
- `id_cajas_de_envases`
- `codigo`

### 3. Validación de ProductoItem para Facturación

**Ubicación**: `php/libredte.php:50-82`

```php
foreach($producto->productos_items as $pi) {
    // Si productos_items está vacío, NO se genera detalle
    // La factura queda con Detalle: []
}
```

**Requisito**: Todo Producto debe tener al menos un ProductoItem configurado.

### 4. Reversión de Estados al Eliminar Despacho

**Ubicación**: `php/classes/Despacho.php:22-46`

```php
public function deleteSpecifics($values) {
    // Debe revertir tanto Barriles como CajasDeEnvases
    if($dp->tipo=="Barril") { /* revertir barril */ }
    if($dp->tipo=="CajaEnvases") { /* revertir caja */ }
}
```

### 5. Validación de Formato en Creación de Caja

**Ubicación**: `ajax/ajax_crearCajaDeEnvases.php:59-68`

```php
// Verificar que el formato del batch coincida con el producto
if($batch->id_formatos_de_envases != $producto->id_formatos_de_envases) {
    // ERROR: formatos incompatibles
}
```

---

## Archivos Clave por Proceso

### Producción

| Proceso | Archivos |
|---------|----------|
| Batch | `templates/batch.php`, `ajax/ajax_guardarBatch.php`, `php/classes/Batch.php` |
| Fermentación | `php/classes/BatchActivo.php`, `php/classes/BatchLupulizacion.php` |
| Envasado | `ajax/ajax_envasar.php`, `php/classes/BatchDeEnvases.php`, `php/classes/Envase.php` |
| Creación de Caja | `ajax/ajax_crearCajaDeEnvases.php`, `php/classes/CajaDeEnvases.php` |
| Revertir Caja | `ajax/ajax_eliminarCajaDeEnvases.php` |
| Barriles | `templates/barriles.php`, `php/classes/Barril.php` |

### Distribución

| Proceso | Archivos |
|---------|----------|
| Crear Despacho | `ajax/ajax_guardarDespacho.php`, `php/classes/Despacho.php` |
| Ver Despachos | `templates/despachos.php`, `templates/detalle-despachos.php` |
| Eliminar Despacho | `php/classes/Despacho.php` → `deleteSpecifics()` |

### Entrega

| Proceso | Archivos |
|---------|----------|
| Vista Repartidor | `templates/repartidor.php` |
| Guardar Entrega | `ajax/ajax_guardarEntrega.php` |
| Entidades | `php/classes/Entrega.php`, `php/classes/EntregaProducto.php` |

### Facturación

| Proceso | Archivos |
|---------|----------|
| Integración LibreDTE | `php/libredte.php` |
| DTE | `php/classes/DTE.php` |
| Items de Factura | `php/classes/ProductoItem.php` |

### Inventario

| Proceso | Archivos |
|---------|----------|
| Vista General | `templates/inventario-de-productos.php` |
| Formatos | `php/classes/FormatoDeEnvases.php` |

---

## Queries de Diagnóstico

### Ver trazabilidad completa de un Envase

```sql
SELECT
    e.id AS envase_id,
    e.estado AS envase_estado,
    bde.id AS batch_envases_id,
    bde.tipo AS tipo_envase,
    b.batch_nombre,
    r.nombre AS receta,
    c.codigo AS caja_codigo,
    c.estado AS caja_estado,
    p.nombre AS producto
FROM envases e
LEFT JOIN batches_de_envases bde ON e.id_batches_de_envases = bde.id
LEFT JOIN batches b ON bde.id_batches = b.id
LEFT JOIN recetas r ON bde.id_recetas = r.id
LEFT JOIN cajas_de_envases c ON e.id_cajas_de_envases = c.id
LEFT JOIN productos p ON c.id_productos = p.id
WHERE e.id = [ID_ENVASE];
```

### Ver productos en despacho sin id_productos

```sql
SELECT dp.*, d.estado AS despacho_estado
FROM despachos_productos dp
JOIN despachos d ON dp.id_despachos = d.id
WHERE dp.tipo = 'CajaEnvases'
AND (dp.id_productos = 0 OR dp.id_productos IS NULL);
```

### Ver entregas con productos sin items de facturación

```sql
SELECT
    en.id AS entrega_id,
    en.factura,
    ep.id_productos,
    p.nombre AS producto,
    (SELECT COUNT(*) FROM productos_items pi WHERE pi.id_productos = p.id) AS items_count
FROM entregas en
JOIN entregas_productos ep ON en.id_entregas = ep.id_entregas
LEFT JOIN productos p ON ep.id_productos = p.id
WHERE ep.id_productos > 0
HAVING items_count = 0;
```

### Ver cajas mixtas con su contenido

```sql
SELECT
    c.codigo,
    c.estado,
    p.nombre AS producto,
    r.nombre AS receta,
    COUNT(e.id) AS envases
FROM cajas_de_envases c
JOIN productos p ON c.id_productos = p.id
JOIN envases e ON e.id_cajas_de_envases = c.id
JOIN batches_de_envases bde ON e.id_batches_de_envases = bde.id
JOIN recetas r ON bde.id_recetas = r.id
WHERE p.es_mixto = 1
GROUP BY c.id, r.id
ORDER BY c.codigo, r.nombre;
```

---

## Changelog de Correcciones Críticas (2025-11)

### 1. Fix: `id_productos = 0` en DespachoProducto para CajaEnvases

**Archivo**: `ajax/ajax_guardarDespacho.php`
**Problema**: Al crear despacho con CajaEnvases, `id_productos` quedaba en 0.
**Solución**: Obtener `id_productos` desde `CajaDeEnvases.id_productos`.

### 2. Fix: CajaEnvases no se revertía al eliminar Despacho

**Archivo**: `php/classes/Despacho.php`
**Problema**: `deleteSpecifics()` solo manejaba Barriles.
**Solución**: Agregar lógica para revertir CajaEnvases a estado "En planta".

### 3. Fix: Descripción incorrecta en factura para Cajas

**Archivo**: `php/libredte.php`
**Problema**: Descripción mostraba "Caja [cantidad] [nombre]" en vez de formato de envases.
**Solución**: Detectar `producto.tipo == "Caja"` y generar descripción apropiada.

### 4. Fix: Migración de columna `cantidad_latas` a `cantidad_envases`

**Archivo**: `db/migrations/005_fix_cajas_envases.sql`
**Problema**: Tabla tenía `cantidad_latas`, clase usaba `cantidad_envases`.
**Solución**: `ALTER TABLE ... CHANGE COLUMN`.

---

## Interfaz de Inventario de Productos

La interfaz principal para gestión de inventario se encuentra en `templates/inventario-de-productos.php`. Esta vista centraliza todas las operaciones de trazabilidad del sistema.

### Estructura de la Vista

```
┌─────────────────────────────────────────────────────────────────┐
│                   INVENTARIO DE PRODUCTOS                        │
├────────────────────────────┬────────────────────────────────────┤
│  ┌────────────────────┐    │  ┌─────────────────────────────┐   │
│  │   FERMENTACIÓN     │    │  │       MADURACIÓN            │   │
│  │   [+ Agregar]      │    │  │       [Traspasar]           │   │
│  │                    │    │  │                             │   │
│  │  Batch #001 IPA    │    │  │  Batch #002 Pale Ale        │   │
│  │  └─ F001 (50L)     │    │  │  └─ F003 (30L) [Eliminar]   │   │
│  │  └─ F002 (50L)     │    │  │                             │   │
│  └────────────────────┘    │  └─────────────────────────────┘   │
├────────────────────────────┼────────────────────────────────────┤
│  ┌────────────────────┐    │  ┌─────────────────────────────┐   │
│  │ BARRILES LLENOS    │    │  │       DESPACHOS             │   │
│  │ [Llenar Barril]    │    │  │    [+ Nuevo Despacho]       │   │
│  │                    │    │  │                             │   │
│  │ B-001 | IPA #001   │    │  │ IPA #001 | B-001 | Cliente  │   │
│  │ B-002 | Pale #002  │    │  │                             │   │
│  └────────────────────┘    │  └─────────────────────────────┘   │
├────────────────────────────┼────────────────────────────────────┤
│  ┌────────────────────┐    │  ┌─────────────────────────────┐   │
│  │  ENVASES EN PLANTA │    │  │    CAJAS EN PLANTA          │   │
│  │     [Envasar]      │    │  │      (24 cajas)             │   │
│  │                    │    │  │                             │   │
│  │ ┌─ Latas (5)       │    │  │ ┌─ Latas (12)               │   │
│  │ │ 24 | IPA #001    │    │  │ │ CAJA-251129-A1 | Pack 24  │   │
│  │ │ [Crear Cajas]    │    │  │ │ [↺ Revertir]              │   │
│  │ ├─ Botellas (3)    │    │  │ ├─ Botellas (12)            │   │
│  │ │ 12 | Pale #002   │    │  │ │ CAJA-251129-B1 | Pack 12  │   │
│  │ │ [Crear Cajas]    │    │  │ │ [↺ Revertir]              │   │
│  └────────────────────┘    │  └─────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
```

### Cards de la Vista

| Card | Descripción | Permisos | Acciones |
|------|-------------|----------|----------|
| **Fermentación** | BatchActivos en estado 'Fermentación' | Todos | Agregar Fermentador, Ver detalles |
| **Maduración** | BatchActivos en estado 'Maduración' | Todos, Eliminar solo Admin | Traspasar, Eliminar |
| **Barriles Llenos** | Barriles con cerveza en planta | Admin, Jefe Planta | Llenar Barril |
| **Despachos** | Barriles en despacho | Todos | Nuevo Despacho |
| **Envases en Planta** | BatchDeEnvases con envases disponibles | Admin, Jefe Planta | Envasar, Crear Cajas, Revertir |
| **Cajas en Planta** | CajaDeEnvases estado "En planta" | Admin, Jefe Planta | Revertir |

### Datos Cargados (PHP)

```php
// Fermentadores activos
$batches_activos_fermentacion = BatchActivo::getAll("WHERE estado='Fermentación' AND litraje!=0");
$batches_activos_maduracion = BatchActivo::getAll("WHERE estado='Maduración' AND litraje>0");
$batches_activos_ferminacion_inox = BatchActivo::getAll("INNER JOIN activos...");

// Barriles
$barriles_en_planta = Barril::getAll("WHERE id_batches!=0 AND estado='En planta'...");
$barriles_en_terreno = Barril::getAll("WHERE id_batches!=0 AND estado='En despacho'...");
$barriles_disponibles = Barril::getAll("WHERE litros_cargados!=litraje AND estado='En planta'...");
$barriles_para_envasar = Barril::getAll("WHERE id_batches!=0 AND estado='En planta' AND litros_cargados>0...");

// Envases y Cajas
$formatos_de_envases = FormatoDeEnvases::getAllActivos();
$formatos_latas = FormatoDeEnvases::getAllByTipo('Lata');
$formatos_botellas = FormatoDeEnvases::getAllByTipo('Botella');

$batches_de_envases = BatchDeEnvases::getAllConDisponibles();
$batches_de_latas = BatchDeEnvases::getAllConDisponiblesByTipo('Lata');
$batches_de_botellas = BatchDeEnvases::getAllConDisponiblesByTipo('Botella');

$cajas_de_envases_en_planta = CajaDeEnvases::getCajasEnPlanta();
$cajas_de_latas_en_planta = CajaDeEnvases::getCajasEnPlantaByTipo('Lata');
$cajas_de_botellas_en_planta = CajaDeEnvases::getCajasEnPlantaByTipo('Botella');

$productos_de_envases = Producto::getProductosDeEnvases();
$productos_de_latas = Producto::getProductosDeEnvases('Lata');
$productos_de_botellas = Producto::getProductosDeEnvases('Botella');
```

### Modals y Funcionalidades

#### 1. Modal: Agregar Fermentador (`#agregar-fermentadores-modal`)

**Propósito**: Agregar un fermentador a un batch existente.

**Campos**:
- `id_batches`: Batch destino
- `id_activos`: Fermentador a agregar
- `cantidad`: Litraje (readonly, del fermentador)

**AJAX**: `ajax/ajax_inventarioProductosBatchActivoAgregar.php`

**Proceso**:
```javascript
var data = {
    'id_batches': $('#agregar-fermentadores_id_batches-select').val(),
    'id_activos': $('#agregar-fermentadores_id_activos-select').val(),
    'estado': 'Fermentación'
};
$.post('./ajax/ajax_inventarioProductosBatchActivoAgregar.php', data, callback);
```

#### 2. Modal: Traspasar (`#traspaso-modal`)

**Propósito**: Traspasar cerveza de un fermentador a otro.

**Validación**: Ambos fermentadores deben tener el mismo litraje.

**Campos**:
- `id_fermentadores_inicio`: Fermentador origen
- `id_fermentadores_final`: Fermentador destino
- `date`, `hora`: Fecha y hora del traspaso

**AJAX**: `ajax/ajax_agregarTraspasosInventarioProductos.php`

**Proceso**:
```javascript
var data = {
    'id_batches': id_batches,
    'id_fermentadores_inicio': $('#traspasos-desde-select').val(),
    'id_fermentadores_final': $('#traspasos-hasta-select').val(),
    'date': $('#nuevo-traspasos-date-input').val(),
    'hora': $('#nuevo-traspasos-hora-input').val()
};
$.post('./ajax/ajax_agregarTraspasosInventarioProductos.php', data, callback);
```

#### 3. Modal: Llenar Barril (`#llenar-barriles-modal`)

**Propósito**: Cargar cerveza desde un fermentador a un barril.

**Layout**: 3 columnas (Fermentador → Cantidad → Barril)

**Campos**:
- `id_batches_activos`: Fermentador origen
- `id_barriles`: Barril destino
- `cantidad_a_cargar`: Litros a transferir

**AJAX**: `ajax/ajax_llenarBarriles.php`

**Proceso**:
```javascript
var data = {
    'id_batches_activos': $('#llenar-barriles_id_batches_activos-select').val(),
    'id_barriles': $('#llenar-barriles_id_barriles-select').val(),
    'cantidad_a_cargar': $('#llenar-barriles-cantidad-a-cargar').val()
};
$.post('./ajax/ajax_llenarBarriles.php', data, callback);
```

#### 4. Modal: Envasar (`#envasar-modal`)

**Propósito**: Crear envases (latas/botellas) desde un fermentador o barril.

**Layout**: 2 columnas
- Izquierda: Origen (Fermentador/Barril selector, volumen disponible/restante)
- Derecha: Líneas de envasado dinámicas

**Características**:
- Permite múltiples líneas de envasado (mezclar latas y botellas)
- Calcula máximo de envases según volumen disponible
- Muestra merma estimada en tiempo real
- Validación de volumen total vs disponible

**Variables JavaScript**:
```javascript
var envasarLineas = [];  // Array de líneas de envasado
var envasarLineaIdCounter = 0;  // Contador para IDs únicos

// Estructura de línea:
{
    id: 1,
    tipo: 'Lata',
    id_formatos_de_envases: 4,
    volumen_ml: 473,
    cantidad: 24
}
```

**Funciones Clave**:
```javascript
function agregarLineaEnvasado(tipo)     // Agrega nueva línea
function actualizarEnvasarDisponible()  // Actualiza volumen disponible del origen
function actualizarLineaMax($linea)     // Calcula máximo envases para línea
function actualizarLineaVolumen($linea) // Actualiza display de volumen
function actualizarEnvasarTotales()     // Recalcula totales y habilita/deshabilita botón
```

**AJAX**: `ajax/ajax_envasar.php`

**Payload**:
```javascript
var data = {
    csrf_token: csrfToken,
    origen_tipo: 'fermentador',  // o 'barril'
    id_batches_activos: 123,     // si origen es fermentador
    id_barriles: 0,              // si origen es barril
    id_batches: 456,
    id_activos: 789,
    id_recetas: 1,
    volumen_origen_ml: 50000,
    volumen_total_usado_ml: 47300,
    merma_total_ml: 2700,
    lineas: JSON.stringify([
        { tipo: 'Lata', id_formatos_de_envases: 4, volumen_ml: 473, cantidad: 100 }
    ])
};
```

#### 5. Modal: Crear Cajas (Wizard) (`#crear-cajas-modal`)

**Propósito**: Crear cajas de envases asignando envases de uno o más batches.

**Pasos**:
1. **Paso 1**: Seleccionar producto (Pack 24, Pack 12, etc.)
2. **Paso 2**: Asignar envases de batches disponibles

**Características**:
- Soporta productos mixtos (múltiples recetas en una caja)
- Filtra batches por formato compatible
- Valida cantidad exacta requerida
- Muestra disponibilidad por batch

**Variables JavaScript**:
```javascript
var crearCajasProductoId = null;
var crearCajasFormatoId = null;
var crearCajasCantidadRequerida = 0;
var crearCajasEsMixto = false;
var crearCajasTipoEnvase = 'Lata';
```

**Funciones Clave**:
```javascript
function filtrarProductosPorTipo()    // Filtra opciones del select por tipo
function cargarBatchesParaCajas()     // Carga batches compatibles en paso 2
function actualizarTotalAsignado()    // Valida cantidad asignada vs requerida
```

**AJAX**: `ajax/ajax_crearCajaDeEnvases.php`

**Payload**:
```javascript
var data = {
    csrf_token: csrfToken,
    id_productos: 5,
    asignaciones: JSON.stringify({
        "123": 12,  // 12 envases del BatchDeEnvases #123
        "456": 12   // 12 envases del BatchDeEnvases #456 (para mixta)
    })
};
```

#### 6. Modal: Revertir Envasado (`#revertir-envasado-modal`)

**Propósito**: Eliminar un BatchDeEnvases y devolver el volumen al origen.

**Validación**: Solo se puede revertir si todos los envases están disponibles (no asignados a cajas).

**Información Mostrada**:
- Cantidad de envases a eliminar
- Volumen a devolver al origen
- Nombre del batch afectado

**AJAX**: `ajax/ajax_revertirEnvasado.php`

**Payload**:
```javascript
var data = {
    id_batch_de_envases: 123
};
```

#### 7. Modal: Revertir Caja (`#eliminar-caja-modal`)

**Propósito**: Eliminar una caja y liberar sus envases.

**Información Mostrada**:
- Código de la caja
- Producto asociado
- Cantidad de envases a liberar

**AJAX**: `ajax/ajax_eliminarCajaDeEnvases.php`

**Payload**:
```javascript
var data = {
    id_caja: 25
};
```

### Endpoints AJAX de Inventario

| Endpoint | Método | Descripción |
|----------|--------|-------------|
| `ajax_inventarioProductosBatchActivoAgregar.php` | POST | Agregar fermentador a batch |
| `ajax_inventarioProductosBatchActivoEliminar.php` | POST | Quitar fermentador de batch |
| `ajax_agregarTraspasosInventarioProductos.php` | POST | Traspasar entre fermentadores |
| `ajax_llenarBarriles.php` | POST | Cargar barril desde fermentador |
| `ajax_envasar.php` | POST | Crear BatchDeEnvases + Envases |
| `ajax_revertirEnvasado.php` | POST | Eliminar BatchDeEnvases |
| `ajax_crearCajaDeEnvases.php` | POST | Crear CajaDeEnvases |
| `ajax_eliminarCajaDeEnvases.php` | POST | Eliminar CajaDeEnvases |

### Flujo de Datos JavaScript

```javascript
// Datos disponibles globalmente en el template
const batches = <?= json_encode($batches); ?>;
const recetas = <?= json_encode(Receta::getAll()); ?>;
const formatos_latas = <?= json_encode($formatos_latas); ?>;
const formatos_botellas = <?= json_encode($formatos_botellas); ?>;
const batches_de_latas = <?= json_encode($batches_de_latas); ?>;
const batches_de_botellas = <?= json_encode($batches_de_botellas); ?>;
const productos_de_latas = <?= json_encode($productos_de_latas); ?>;
const productos_de_botellas = <?= json_encode($productos_de_botellas); ?>;
const barriles_para_envasar = <?= json_encode($barriles_para_envasar); ?>;
const batches_para_carga = <?= json_encode($batches_para_carga); ?>;
const barriles_disponibles = <?= json_encode($barriles_disponibles); ?>;
const activos_traspaso = <?= json_encode($activos_traspaso); ?>;
const activos_traspaso_disponibles = <?= json_encode($activos_traspaso_disponibles); ?>;
```

### Eventos y Handlers

```javascript
// Fermentación
$(document).on('click', '#agregar-fermentadores-btn', ...);
$(document).on('click', '#agregar-fermentadores-aceptar-btn', ...);
$(document).on('click', '.tr-fermentadores', ...);  // Click en fila = eliminar
$(document).on('click', '#eliminar-fermentadores-aceptar-btn', ...);

// Maduración
$(document).on('click', '#traspasar-btn', ...);
$(document).on('change', '#traspasos-desde-select', ...);
$(document).on('change', '#traspasos-hasta-select', ...);
$(document).on('click', '#traspasar-aceptar-btn', ...);
$(document).on('click', '.maduracion-eliminar-fermentadores-aceptar-btn', ...);

// Barriles
$(document).on('click', '#llenar-barriles-btn', ...);
$(document).on('change', '#llenar-barriles_id_barriles-select', ...);
$(document).on('change', '#llenar-barriles_id_batches_activos-select', ...);
$(document).on('click', '#llenar-barril-aceptar-btn', ...);

// Envasado
$(document).on('click', '#envasar-btn', ...);
$(document).on('change', 'input[name="envasar-origen-tipo"]', ...);
$(document).on('change', '#envasar-origen-fermentador-select', ...);
$(document).on('change', '#envasar-origen-barril-select', ...);
$(document).on('click', '#envasar-agregar-lata-btn', ...);
$(document).on('click', '#envasar-agregar-botella-btn', ...);
$(document).on('click', '.envasar-linea-eliminar', ...);
$(document).on('change', '.envasar-linea-formato', ...);
$(document).on('change keyup', '.envasar-linea-cantidad', ...);
$(document).on('click', '#envasar-aceptar-btn', ...);

// Crear Cajas
$(document).on('click', '#crear-cajas-latas-btn', ...);
$(document).on('click', '#crear-cajas-botellas-btn', ...);
$(document).on('change', '#crear-cajas-producto-select', ...);
$(document).on('click', '#crear-cajas-siguiente-btn', ...);
$(document).on('click', '#crear-cajas-atras-btn', ...);
$(document).on('change keyup', '.crear-cajas-input', ...);
$(document).on('click', '#crear-cajas-crear-btn', ...);

// Revertir Envasado
$(document).on('click', '.revertir-envasado-btn', ...);
$(document).on('click', '#revertir-envasado-confirmar-btn', ...);

// Eliminar Caja
$(document).on('click', '.eliminar-caja-btn', ...);
$(document).on('click', '#eliminar-caja-confirmar-btn', ...);
```

### Atributos Data en Botones

Los botones de acción almacenan datos en atributos `data-*` para evitar consultas adicionales:

#### Botón Revertir Envasado
```html
<button class="revertir-envasado-btn"
    data-id="<?= $bdl->id; ?>"
    data-tipo="Lata"
    data-envases="<?= $bdl->envases_disponibles; ?>"
    data-total="<?= $bdl->cantidad_de_envases; ?>"
    data-origen="<?= $origen; ?>"
    data-origen-tipo="<?= $origen_tipo; ?>"
    data-volumen="<?= $bdl->volumen_origen_ml; ?>"
    data-batch="<?= $receta->nombre; ?> #<?= $batch->batch_nombre; ?>">
```

#### Botón Eliminar Caja
```html
<button class="eliminar-caja-btn"
    data-id="<?= $caja->id; ?>"
    data-codigo="<?= $caja->codigo; ?>"
    data-producto="<?= $producto->nombre; ?>"
    data-cantidad="<?= $caja->cantidad_envases; ?>">
```

#### Checkbox en Repartidor (DespachoProducto)
```html
<input type="checkbox" class="despacho-checkbox"
    data-id="<?= $dp->id; ?>"
    data-tipo="<?= $dp->tipo; ?>"
    data-id-cajas-envases="<?= $dp->id_cajas_de_envases; ?>">
```

### Validaciones Importantes

1. **Traspaso**: Los fermentadores deben tener el mismo litraje
2. **Envasado**: El volumen total no puede exceder el disponible
3. **Crear Caja**: La cantidad asignada debe ser exactamente la requerida por el producto
4. **Revertir Envasado**: Solo si todos los envases están disponibles (no en cajas)
5. **Formato de Caja**: Solo se muestran batches con formato compatible

---

*Documento generado el 2025-11-29*
*Versión del sistema: Barril.cl ERP v1.0*
