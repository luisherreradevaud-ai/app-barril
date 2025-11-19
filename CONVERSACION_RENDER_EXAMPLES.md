# ConversacionInterna - Guía de Uso del Método `render()`

El componente de Conversación Interna puede ser renderizado dinámicamente en cualquier contenedor con una sola línea de código.

## Instalación

Incluye el archivo JavaScript en tu template:

```html
<script src="/js/conversacion-interna.js"></script>
```

Este archivo incluye:
- ✅ Templates HTML necesarios
- ✅ Estilos CSS del componente
- ✅ Lógica completa del componente
- ✅ Auto-inyección de dependencias

**No necesitas incluir nada más.** El componente es completamente autocontenido.

---

## Uso Básico

```javascript
// Sintaxis:
ConversacionInterna.render(containerId, viewName, entityId, options);

// Ejemplo simple:
ConversacionInterna.render('mi-contenedor', 'tarea', '123');
```

**Nota:** Asegúrate de definir `window.currentUserId` antes de usar el componente:
```javascript
window.currentUserId = '<?php echo $GLOBALS['usuario']->id; ?>';
```

## Parámetros

### `containerId` (string, requerido)
ID del elemento HTML donde se renderizará el componente.

### `viewName` (string, requerido)
Nombre de la vista/tipo de conversación (ej: 'tarea', 'pedido', 'batch', 'cliente').

### `entityId` (string, requerido)
ID de la entidad específica.

### `options` (object, opcional)
Objeto de configuración con las siguientes opciones:

| Opción | Tipo | Default | Descripción |
|--------|------|---------|-------------|
| `height` | string | `'400px'` | Altura máxima del área de comentarios |
| `placeholder` | string | `'Escribe un comentario...'` | Placeholder del textarea |
| `showFiles` | boolean | `true` | Mostrar/ocultar funcionalidad de archivos adjuntos |
| `compact` | boolean | `false` | Modo compacto (textarea de 1 línea, botones más pequeños) |
| `autoRefresh` | boolean | `true` | Auto-actualizar cada 60 segundos |
| `showHeader` | boolean | `false` | Mostrar encabezado con título |
| `headerText` | string | `'Conversación'` | Texto del encabezado (si showHeader es true) |

## Ejemplos de Uso

### 1. En un Modal de Tarea (Kanban)

```javascript
var currentConversationId = null;

// Cuando se abre el modal
$('#taskModal').on('shown.bs.modal', function() {
  var tareaId = getCurrentTaskId();

  currentConversationId = ConversacionInterna.render(
    'task-conversation-container',
    'tarea',
    tareaId,
    {
      height: '500px',
      compact: true,
      placeholder: 'Comentar sobre esta tarea...'
    }
  );
});

// Cuando se cierra el modal - IMPORTANTE: limpiar la instancia
$('#taskModal').on('hidden.bs.modal', function() {
  if(currentConversationId) {
    ConversacionInterna.destroy(currentConversationId);
    currentConversationId = null;
  }
});
```

### 2. En un Sidebar

```javascript
// Renderizar en sidebar con modo compacto
ConversacionInterna.render(
  'sidebar-chat',
  'proyecto',
  proyectoId,
  {
    compact: true,
    height: '300px',
    showHeader: true,
    headerText: 'Chat del Proyecto'
  }
);
```

### 3. En una Página Completa

```javascript
// Vista completa de pedido con conversación
ConversacionInterna.render(
  'pedido-conversacion',
  'pedido',
  pedidoId,
  {
    height: '600px',
    placeholder: 'Agregar comentario sobre el pedido...',
    showHeader: true,
    headerText: 'Conversación del Pedido #' + pedidoNumero
  }
);
```

### 4. Sin Archivos Adjuntos

```javascript
// Chat simple sin opción de adjuntar archivos
ConversacionInterna.render(
  'soporte-chat',
  'ticket',
  ticketId,
  {
    showFiles: false,
    compact: true,
    height: '400px'
  }
);
```

### 5. En un Tab/Accordion

```javascript
// Inicializar cuando se muestra el tab
$('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
  if(e.target.id === 'conversacion-tab') {
    ConversacionInterna.render(
      'tab-conversacion-content',
      'batch',
      batchId,
      { height: '450px' }
    );
  }
});
```

### 6. Múltiples Conversaciones en la Misma Página

```javascript
var conversaciones = {};

// Renderizar múltiples conversaciones
conversaciones.general = ConversacionInterna.render(
  'conv-general',
  'proyecto_general',
  proyectoId
);

conversaciones.tecnica = ConversacionInterna.render(
  'conv-tecnica',
  'proyecto_tecnica',
  proyectoId,
  { placeholder: 'Discusión técnica...' }
);

// Limpiar todas al salir
function cleanupConversaciones() {
  Object.values(conversaciones).forEach(function(convId) {
    ConversacionInterna.destroy(convId);
  });
  conversaciones = {};
}
```

## HTML Requerido

Solo necesitas un `<div>` vacío en tu template:

```html
<div id="mi-contenedor"></div>
```

El componente generará automáticamente todo el HTML necesario.

## Retorno

El método `render()` retorna el ID único de la conversación generada:

```javascript
var convId = ConversacionInterna.render('container', 'tarea', '123');
// convId = 'conv_tarea_123'

// Usar el ID para obtener la instancia
var instance = ConversacionInterna.getInstance(convId);

// O para destruirla
ConversacionInterna.destroy(convId);
```

## Destrucción de Instancias

**IMPORTANTE:** Siempre destruye las instancias cuando ya no las necesites (especialmente en modales y componentes dinámicos):

```javascript
// Obtener el ID cuando renderizas
var convId = ConversacionInterna.render('container', 'tarea', tareaId);

// Destruir cuando termines
ConversacionInterna.destroy(convId);

// Esto:
// - Detiene el auto-refresh
// - Limpia event listeners
// - Libera memoria
```

## Integración con Componentes Existentes

### Kanban (tablero-kanban.php)

```html
<!-- En el modal, agregar solo el contenedor -->
<div class="col-md-4">
  <h6 class="fw-semibold mb-3">Conversación</h6>
  <div id="task-conversation-container"></div>
</div>
```

```javascript
// En el JavaScript del kanban
var currentTaskConversationId = null;

$('#taskModal').on('shown.bs.modal', function() {
  currentTaskConversationId = ConversacionInterna.render(
    'task-conversation-container',
    'tarea',
    currentTaskId,
    { height: '500px', compact: true }
  );
});

$('#taskModal').on('hidden.bs.modal', function() {
  if(currentTaskConversationId) {
    ConversacionInterna.destroy(currentTaskConversationId);
    currentTaskConversationId = null;
  }
});
```

## Notas Importantes

1. **No necesitas incluir el template PHP** - El componente se genera dinámicamente
2. **Siempre destruye las instancias** cuando el componente se oculta o elimina
3. **El componente es completamente autocontenido** - maneja su propio estado y eventos
4. **Preserva el scroll** automáticamente al actualizar
5. **Loading solo en primera carga** - las actualizaciones posteriores son silenciosas

## Troubleshooting

### El componente no se renderiza
- Verifica que el contenedor existe: `$('#mi-contenedor').length`
- Revisa la consola para ver errores

### Los archivos no se pueden adjuntar
- Asegúrate de que `showFiles: true` en las opciones
- Verifica que los iconos de Bootstrap estén cargados

### La conversación no se actualiza
- Verifica que `autoRefresh: true` (es el default)
- Revisa que el endpoint `/ajax/ajax_getConversacion.php` esté respondiendo

### Memory leaks
- Siempre llama a `ConversacionInterna.destroy(convId)` cuando el componente se desmonta
