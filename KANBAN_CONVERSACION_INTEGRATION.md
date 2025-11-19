# Integración de Conversaciones en Kanban

Guía paso a paso para integrar el componente de conversaciones internas en el sistema Kanban.

## Cambios Necesarios

### 1. Incluir el archivo JavaScript del componente

**En `tablero-kanban.php`, en la sección de scripts:**

```html
<!-- Componente de Conversaciones -->
<script src="/js/conversacion-interna.js"></script>
```

---

### 2. Modificar el HTML del Modal

**Ubicación:** Líneas 298-304

**Reemplazar:**
```html
<div id="task-activity" class="border rounded p-3" style="min-height: 300px;">
  <p class="text-muted small">No hay actividad aún</p>
</div>
```

**Por:**
```html
<div id="task-conversation-container"></div>
```

**Resultado final:**
```html
<!-- Right Column -->
<div class="col-md-4">
  <h6 class="fw-semibold mb-3">Conversación</h6>
  <div id="task-conversation-container"></div>
</div>
```

---

### 3. Agregar JavaScript para manejar el modal

**Ubicación:** En la sección de variables globales del Kanban

```javascript
// Variable global para trackear la conversación actual
var currentTaskConversationId = null;

// Variable para el ID del usuario actual (requerida por el componente)
window.currentUserId = '<?php echo $GLOBALS['usuario']->id; ?>';
```

**Ubicación:** En los event listeners del modal

```javascript
// Cuando se abre el modal de tarea
$('#taskModal').on('shown.bs.modal', function() {
  if(currentTask && currentTask.id) {
    currentTaskConversationId = ConversacionInterna.render(
      'task-conversation-container',
      'tarea',
      currentTask.id,
      {
        height: '500px',
        compact: true,
        placeholder: 'Comentar sobre esta tarea...'
      }
    );
  }
});

// Cuando se cierra el modal - IMPORTANTE para evitar memory leaks
$('#taskModal').on('hidden.bs.modal', function() {
  if(currentTaskConversationId) {
    ConversacionInterna.destroy(currentTaskConversationId);
    currentTaskConversationId = null;
  }
});
```

---

## Resumen de Cambios

La integración completa requiere solo:

1. **1 línea** para incluir el JavaScript: `<script src="/js/conversacion-interna.js"></script>`
2. **1 cambio HTML** en el modal: Reemplazar el div de actividad por `<div id="task-conversation-container"></div>`
3. **2 funciones JavaScript** para manejar el modal show/hide

Total: **Menos de 30 líneas de código**

---

## Opciones de Configuración

Puedes personalizar el componente con estas opciones:

```javascript
ConversacionInterna.render('task-conversation-container', 'tarea', tareaId, {
  // Altura del área de comentarios
  height: '500px',

  // Modo compacto (textarea más pequeño, botones sm)
  compact: true,

  // Placeholder del textarea
  placeholder: 'Comentar sobre esta tarea...',

  // Mostrar/ocultar adjuntar archivos
  showFiles: true,

  // Mostrar encabezado
  showHeader: false,

  // Texto del encabezado (si showHeader es true)
  headerText: 'Conversación',

  // Auto-refresh cada 60 segundos
  autoRefresh: true
});
```

---

## Verificación

Después de implementar, verifica que:

1. ✅ Al abrir el modal de una tarea, se carga la conversación
2. ✅ Puedes publicar comentarios
3. ✅ Puedes dar like a comentarios
4. ✅ Puedes adjuntar archivos (si `showFiles: true`)
5. ✅ Al cerrar el modal, la conversación se destruye
6. ✅ Al abrir otra tarea, se carga la conversación correcta

---

## Troubleshooting

### El componente no se renderiza
```javascript
// Verificar que el contenedor existe
console.log($('#task-conversation-container').length); // Debe ser 1

// Verificar que ConversacionInterna está disponible
console.log(typeof ConversacionInterna); // Debe ser 'object'
```

### Los comentarios no se guardan
- Verificar que el endpoint `/ajax/ajax_guardarComentarioConArchivos.php` esté funcionando
- Revisar la consola del navegador (F12) para ver errores

### Memory leaks (múltiples instancias)
- Asegúrate de llamar a `ConversacionInterna.destroy()` en el evento `hidden.bs.modal`
- Verifica con `ConversacionInterna.getAllInstances()` en consola

### La conversación muestra datos de otra tarea
- Verifica que estás pasando el `tareaId` correcto
- Asegúrate de destruir la instancia anterior antes de crear una nueva

---

## Ejemplo Completo Funcional

```javascript
// Variables globales del Kanban
var currentTaskId = null;
var currentTaskConversationId = null;

// Función que abre el modal de tarea
function openTaskModal(tareaId) {
  currentTaskId = tareaId;

  // ... tu código existente para cargar datos de la tarea ...

  // Abrir modal
  var modal = new bootstrap.Modal(document.getElementById('taskModal'));
  modal.show();
}

// Event listener para cuando el modal se muestra
$('#taskModal').on('shown.bs.modal', function() {
  if(currentTaskId) {
    // Renderizar conversación
    currentTaskConversationId = ConversacionInterna.render(
      'task-conversation-container',
      'tarea',
      currentTaskId,
      { height: '500px', compact: true }
    );
  }
});

// Event listener para cuando el modal se cierra
$('#taskModal').on('hidden.bs.modal', function() {
  // Destruir conversación
  if(currentTaskConversationId) {
    ConversacionInterna.destroy(currentTaskConversationId);
    currentTaskConversationId = null;
  }

  // Limpiar ID de tarea
  currentTaskId = null;
});
```

---

## Usando en Otras Vistas

Una vez implementado en Kanban, puedes usar el mismo patrón para cualquier otra vista:

### Ejemplo: Pedidos

```html
<!-- En tu template de pedido -->
<div id="pedido-conversation-container"></div>
```

```javascript
// Al cargar el pedido
var convId = ConversacionInterna.render(
  'pedido-conversation-container',
  'pedido',
  pedidoId,
  { height: '600px' }
);

// Al salir de la vista
ConversacionInterna.destroy(convId);
```

### Ejemplo: Batches

```javascript
ConversacionInterna.render('batch-conv', 'batch', batchId, { compact: true });
```

### Ejemplo: Clientes

```javascript
ConversacionInterna.render('cliente-conv', 'cliente', clienteId);
```

**El componente es 100% reutilizable.** Solo cambia el `viewName` y el `entityId`.
