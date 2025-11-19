# Sistema Kanban - Tableros con Drag & Drop

Sistema completo de gestión tipo Kanban con tableros, columnas y tareas drag & drop, construido en PHP/HTML/jQuery siguiendo la arquitectura de Barril.cl.

## 🎯 Características Principales

- ✅ **Múltiples Tableros** por entidad (batches, clientes, proyectos, etc.)
- ✅ **Columnas personalizables** con nombres y colores
- ✅ **Drag & Drop** de tareas entre columnas (jQuery UI Sortable)
- ✅ **Tarjetas de tareas** con información visual
- ✅ **Sistema completo de tareas** con checklists, etiquetas, usuarios, fechas, links
- ✅ **Auto-guardado** con debounce
- ✅ **Gestión de columnas** (crear, editar, eliminar)
- ✅ **Gestión de tableros** (crear, editar, eliminar)

## 📁 Estructura del Proyecto

```
tareas-php/
├── database/
│   └── schema.sql              # Esquema de BD (tableros, columnas, tareas)
├── php/classes/
│   ├── KanbanTablero.php      # Clase de tableros
│   ├── KanbanColumna.php      # Clase de columnas
│   ├── KanbanTarea.php        # Clase de tareas (actualizada)
│   ├── KanbanTareaUsuario.php # Relación many-to-many usuarios
│   ├── KanbanTareaEtiqueta.php # Relación many-to-many etiquetas
│   └── KanbanEtiqueta.php     # Clase de etiquetas
├── ajax/
│   ├── ajax_getTableros.php   # Obtener tableros con columnas y tareas
│   ├── ajax_guardarTablero.php # Crear/actualizar tablero
│   ├── ajax_eliminarTablero.php # Eliminar tablero
│   ├── ajax_guardarColumna.php # Crear/actualizar columna
│   ├── ajax_eliminarColumna.php # Eliminar columna
│   ├── ajax_moverTarea.php    # Mover tarea entre columnas
│   ├── ajax_getTarea.php      # Obtener detalle de tarea
│   ├── ajax_guardarTarea.php  # Crear/actualizar tarea
│   ├── ajax_eliminarTarea.php # Eliminar tarea
│   ├── ajax_getEtiquetas.php  # Obtener etiquetas
│   └── ajax_guardarEtiqueta.php # Crear etiqueta
├── templates/
│   └── tablero-kanban.php     # Template principal Kanban
├── js/
│   ├── kanban.js              # Lógica principal del tablero
│   └── kanban-task-functions.js # Funciones del modal de tareas
└── README.md
```

## 🗄️ Estructura de Base de Datos

### Jerarquía de Datos

```
Entidad (batch, cliente, proyecto, etc.)
  └─ KanbanTablero (id_entidad)
      └─ KanbanColumna (id_kanban_tableros)
          └─ KanbanTarea (id_kanban_columnas)
              ├─ Usuarios (many-to-many)
              ├─ Etiquetas (many-to-many)
              ├─ Checklist (JSON)
              ├─ Links (JSON)
              └─ Archivos (media)
```

### Tablas Principales

**`kanban_tableros`**
- `id`, `nombre`, `descripcion`, `id_entidad`, `orden`

**`kanban_columnas`**
- `id`, `nombre`, `id_kanban_tableros`, `orden`, `color`

**`kanban_tareas`**
- `id`, `nombre`, `descripcion`, `id_kanban_columnas`, `orden`
- `fecha_inicio`, `fecha_vencimiento`, `recordatorio_vencimiento`
- `checklist` (JSON), `links` (JSON), `estado`

**`kanban_tareas_usuarios`** (many-to-many)
- `id`, `id_kanban_tareas`, `id_usuarios`

**`kanban_tareas_etiquetas`** (many-to-many)
- `id`, `id_kanban_tareas`, `id_kanban_etiquetas`

**`kanban_etiquetas`**
- `id`, `nombre`, `codigo_hex`

**`media_kanban_tareas`** (para archivos adjuntos)
- `id`, `id_media`, `id_kanban_tareas`

## 🚀 Instalación

### 1. Ejecutar Script SQL

```sql
-- Ejecutar database/schema.sql en tu base de datos
-- Esto creará todas las tablas y datos de ejemplo
```

### 2. Copiar Archivos PHP

```bash
# Copiar clases
cp php/classes/*.php /path/to/barril.cl/php/classes/

# Copiar endpoints AJAX
cp ajax/*.php /path/to/barril.cl/ajax/

# Copiar template
cp templates/tablero-kanban.php /path/to/barril.cl/templates/

# Copiar JavaScript
cp js/*.js /path/to/barril.cl/js/
```

### 3. Actualizar app.php

Agregar en `/php/app.php` dentro de `createObjFromTableName()`:

```php
if($table_name=="kanban_tableros") {
  $obj = new KanbanTablero($id);
} else
if($table_name=="kanban_columnas") {
  $obj = new KanbanColumna($id);
} else
if($table_name=="kanban_tareas") {
  $obj = new KanbanTarea($id);
} else
if($table_name=="kanban_tareas_usuarios") {
  $obj = new KanbanTareaUsuario($id);
} else
if($table_name=="kanban_tareas_etiquetas") {
  $obj = new KanbanTareaEtiqueta($id);
} else
if($table_name=="kanban_etiquetas") {
  $obj = new KanbanEtiqueta($id);
}
```

### 4. Incluir Scripts en el Template

En tu layout principal o en el template, agregar:

```html
<!-- jQuery UI (requerido para drag & drop) -->
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

<!-- Scripts del Kanban -->
<script src="js/kanban.js"></script>
<script src="js/kanban-task-functions.js"></script>
```

## 📝 Uso

### Integración Básica

```php
<?php
  // En cualquier template (batch-detalle.php, cliente-detalle.php, etc.)

  // Definir el ID de la entidad
  $_GET['entity_id'] = 'batch_' . $batch->id;

  // Incluir el template del tablero Kanban
  incluir_template("tablero-kanban");
?>

<script>
  // Asegurarse de que entityId está definido
  entityId = 'batch_<?php echo $batch->id; ?>';
</script>
```

### Ejemplo en Página de Batch

```php
<?php
  $batch = new Batch($_GET['id']);
?>

<div class="container-fluid">
  <h2>Batch #<?php echo $batch->id; ?> - <?php echo $batch->nombre; ?></h2>

  <!-- Tabs -->
  <ul class="nav nav-tabs" id="batchTabs">
    <li class="nav-item">
      <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#info">
        Información
      </button>
    </li>
    <li class="nav-item">
      <button class="nav-link" data-bs-toggle="tab" data-bs-target="#kanban">
        Tablero de Tareas
      </button>
    </li>
  </ul>

  <div class="tab-content">
    <div class="tab-pane fade show active" id="info">
      <!-- Información del batch -->
    </div>

    <div class="tab-pane fade" id="kanban">
      <?php
        $_GET['entity_id'] = 'batch_' . $batch->id;
        incluir_template("tablero-kanban");
      ?>
    </div>
  </div>
</div>

<script>
  entityId = 'batch_<?php echo $batch->id; ?>';
</script>
```

## 🎨 Características del Sistema

### Tableros

- **Crear tablero automático**: Si no existe tablero para una entidad, se crea uno por defecto con 3 columnas (Por Hacer, En Progreso, Completado)
- **Nombre editable**: Cada tablero tiene un nombre personalizable
- **Un tablero por entidad**: Cada entidad (batch, cliente, etc.) tiene su propio tablero

### Columnas

- **Crear columnas**: Botón "Nueva Columna" para agregar más columnas
- **Editar columnas**: Click en el icono de lápiz en el header de la columna
- **Colores personalizados**: Cada columna puede tener su propio color
- **Eliminar columnas**: Al eliminar una columna, se eliminan todas sus tareas
- **Contador de tareas**: Cada columna muestra cuántas tareas contiene

### Tareas (Tarjetas)

#### Drag & Drop
- **Arrastrar entre columnas**: Las tareas se pueden mover entre columnas arrastrándolas
- **Reordenar dentro de columna**: Las tareas se pueden reordenar dentro de la misma columna
- **Auto-guardado al mover**: Al soltar una tarea, se guarda automáticamente su nueva posición

#### Información Visual en la Tarjeta
- ✅ Checkbox para marcar como completada
- 👥 Avatares de usuarios asignados
- 📅 Fecha de vencimiento (rojo si está vencida)
- 📎 Contador de archivos adjuntos
- ☑️ Progreso de checklists (X/Y items)

#### Modal de Detalle
Al hacer click en una tarjeta se abre un modal con:
- Nombre y descripción (textarea auto-expandible)
- Asignación de usuarios múltiples
- Etiquetas con colores
- Checklists con items marcables
- Enlaces externos
- Fechas de inicio y vencimiento
- Recordatorios
- Auto-guardado (debounce 1 segundo)

### Etiquetas (Labels)

- **Crear etiquetas**: Desde el modal de etiquetas
- **Colores personalizados**: Cada etiqueta tiene su color hexadecimal
- **Asignación múltiple**: Una tarea puede tener múltiples etiquetas
- **Visualización**: Se muestran como badges de colores

### Usuarios

- **Asignación múltiple**: Varias personas pueden estar asignadas a una tarea
- **Avatares**: Iniciales del nombre en círculo de color
- **Límite visual**: Se muestran hasta 3 avatares, el resto como "+N"

### Checklists

- **Múltiples checklists**: Una tarea puede tener varios checklists
- **Items marcables**: Cada item puede ser completado/pendiente
- **Barra de progreso**: Visualización del porcentaje completado
- **Agregar/eliminar items**: Gestión completa de items

### Fechas y Recordatorios

- **Fecha de inicio**: Opcional
- **Fecha de vencimiento**: Opcional
- **Indicador de vencimiento**: Las tareas vencidas se marcan en rojo
- **Recordatorios configurables**:
  - A la hora de vencimiento
  - 1/2 horas antes
  - 1/2 días antes

## 🔌 API Endpoints

### Tableros

**GET** `/ajax/ajax_getTableros.php?id_entidad={entityId}`
- Obtiene todos los tableros de una entidad con sus columnas y tareas
- Respuesta: `{ tableros: [...], usuarios: [...], etiquetas: [...] }`

**POST** `/ajax/ajax_guardarTablero.php`
- Parámetros: `nombre`, `descripcion`, `id_entidad`, `id` (opcional para editar)
- Respuesta: `{ tablero: {...} }`

**POST** `/ajax/ajax_eliminarTablero.php`
- Parámetros: `id`
- Elimina el tablero y todas sus columnas y tareas en cascada

### Columnas

**POST** `/ajax/ajax_guardarColumna.php`
- Parámetros: `nombre`, `id_kanban_tableros`, `color`, `orden`, `id` (opcional)
- Respuesta: `{ columna: {...} }`

**POST** `/ajax/ajax_eliminarColumna.php`
- Parámetros: `id`
- Elimina la columna y todas sus tareas

### Tareas

**POST** `/ajax/ajax_guardarTarea.php`
- Parámetros: `nombre`, `id_kanban_columnas`, `descripcion`, `checklist`, `links`, etc.
- Respuesta: `{ tarea: {...} }`

**POST** `/ajax/ajax_moverTarea.php`
- Parámetros: `id`, `id_kanban_columnas`, `orden`
- Mueve una tarea a otra columna o cambia su orden

**GET** `/ajax/ajax_getTarea.php?id={taskId}`
- Obtiene detalle completo de una tarea
- Respuesta: `{ tarea: {...}, usuarios: [...], etiquetas: [...] }`

**POST** `/ajax/ajax_eliminarTarea.php`
- Parámetros: `id`
- Elimina la tarea

### Formato de Respuesta Estándar

```json
{
  "status": "OK",
  "mensaje": "Operación exitosa",
  "obj": { ... }
}
```

## 🎨 Personalización

### Colores

Modificar en `tablero-kanban.php`:

```css
.kanban-column-header {
  /* Color de fondo del header (definido por columna) */
}

.kanban-task {
  background-color: white;
  /* Color de las tarjetas */
}

.kanban-task:hover {
  /* Efecto hover */
}

.user-avatar {
  background-color: #6A1693;
  /* Color de los avatares de usuario */
}
```

### Debounce del Auto-guardado

En `kanban-task-functions.js`:

```javascript
saveTimeout = setTimeout(function() {
  autoSaveTask();
}, 1000); // Cambiar a los milisegundos deseados
```

### Tamaño de Columnas

En `tablero-kanban.php` (CSS):

```css
.kanban-column {
  min-width: 320px;  /* Cambiar ancho mínimo */
  max-width: 320px;  /* Cambiar ancho máximo */
}
```

## 🔧 Dependencias

### Requeridas

- **PHP 7.0+**
- **MySQL 5.7+** con soporte para JSON
- **jQuery 1.12.4+**
- **jQuery UI 1.12.1+** (para drag & drop con Sortable)
- **Bootstrap 5.x**
- **Bootstrap Icons**

### Incluir en tu Layout

```html
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>

<!-- jQuery UI (IMPORTANTE para drag & drop) -->
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
```

## 🐛 Troubleshooting

### El drag & drop no funciona

- Verificar que jQuery UI está cargado **después** de jQuery
- Verificar en consola si hay errores de JavaScript
- Asegurarse de que `initializeSortable()` se ejecuta después de renderizar las columnas

### Las tareas no se guardan al moverlas

- Verificar que `ajax_moverTarea.php` está en la ruta correcta
- Revisar la consola del navegador para errores AJAX
- Verificar permisos de archivo PHP

### No aparecen las columnas

- Verificar que existe un tablero para la entidad
- Revisar que `entityId` está definido correctamente
- Revisar respuesta de `ajax_getTableros.php` en Network tab

### El auto-guardado es muy lento

- Ajustar el tiempo de debounce en `kanban-task-functions.js`
- Verificar rendimiento del servidor
- Considerar optimizar queries SQL

## 🚀 Características Futuras

- [ ] Múltiples tableros por entidad con selector
- [ ] Filtros de tareas (por usuario, etiqueta, fecha)
- [ ] Búsqueda de tareas
- [ ] Plantillas de tableros
- [ ] Archivar/desarchivar columnas
- [ ] Copiar/mover tareas entre tableros
- [ ] Comentarios en tareas
- [ ] Notificaciones push
- [ ] Vista de calendario
- [ ] Exportación a PDF/Excel
- [ ] Modo tablero compartido (colaborativo)

## 📄 Licencia

Este código es parte del sistema Barril.cl para uso interno.

## 📞 Soporte

Para reportar problemas o sugerencias, contactar al equipo de desarrollo de Barril.cl.

---

**Versión:** 2.0 (Kanban con Drag & Drop)
**Última actualización:** 2024
