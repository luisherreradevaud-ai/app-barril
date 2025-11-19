// Global variables
var allTableros = [];
var allUsers = [];
var allLabels = [];
var currentTask = null;
var currentColumn = null;
var saveTimeout = null;

// Initialize on document ready
$(document).ready(function() {
  if (typeof entityId !== 'undefined' && entityId) {
    loadTableros();
  }

  setupEventHandlers();
});

// Setup all event handlers
function setupEventHandlers() {
  // Add column button
  $('#btn-agregar-columna').on('click', openNewColumnModal);

  // Column modal
  $('#btn-save-column').on('click', saveColumn);
  $('#btn-delete-column').on('click', deleteColumn);

  // Task modal events
  $('#taskModal').on('hidden.bs.modal', function() {
    currentTask = null;
    clearTaskForm();
  });

  $('#task-status-checkbox').on('change', function() {
    if (currentTask) {
      var newStatus = $(this).is(':checked') ? 'Completada' : 'Pendiente';
      updateTaskField('estado', newStatus);
    }
  });

  $('#task-name, #task-description').on('input', function() {
    triggerAutoSave();
    autoResizeTextarea(this);
  });

  // Task action buttons
  $('#btn-assign-users').on('click', openUserAssignmentModal);
  $('#btn-add-labels').on('click', openLabelManagementModal);
  $('#btn-add-checklist').on('click', openChecklistModal);
  $('#btn-add-dates').on('click', openDatesModal);
  $('#btn-add-link').on('click', openLinkModal);
  $('#btn-delete-task').on('click', deleteTask);

  // User and label management
  $('#user-assignment-select').on('change', updateTaskUsers);
  $('#btn-create-label').on('click', createLabel);

  // Dates
  $('#task-start-date, #task-due-date, #task-reminder').on('change', updateTaskDates);

  // Links
  $('#btn-save-link').on('click', saveLink);

  // Checklists
  $('#btn-create-checklist').on('click', createChecklist);
}

// ===========================
// TABLEROS & COLUMNAS
// ===========================

function loadTableros() {
  $('#loading-spinner').show();

  $.ajax({
    url: './ajax/ajax_getTableros.php',
    type: 'GET',
    data: { id_entidad: entityId },
    dataType: 'json',
    success: function(response) {
      if (response.status === 'OK') {
        allTableros = response.tableros || [];
        allUsers = response.usuarios || [];
        allLabels = response.etiquetas || [];

        if (allTableros.length > 0) {
          currentTableroId = allTableros[0].id;
          renderKanbanBoard(allTableros[0]);
        } else {
          // Create default board
          createDefaultBoard();
        }
      } else {
        showError('Error al cargar tableros: ' + response.mensaje);
      }
    },
    error: function() {
      showError('Error de conexión al cargar tableros');
    },
    complete: function() {
      $('#loading-spinner').hide();
    }
  });
}

function createDefaultBoard() {
  $.ajax({
    url: './ajax/ajax_guardarTablero.php',
    type: 'POST',
    data: {
      nombre: 'Mi Tablero',
      id_entidad: entityId
    },
    dataType: 'json',
    success: function(response) {
      if (response.status === 'OK') {
        currentTableroId = response.tablero.id;

        // Create default columns
        createDefaultColumns(response.tablero.id);
      }
    },
    error: function() {
      showError('Error al crear tablero');
    }
  });
}

function createDefaultColumns(tableroId) {
  var columnas = [
    { nombre: 'Por Hacer', color: '#6C757D', orden: 1 },
    { nombre: 'En Progreso', color: '#FFC107', orden: 2 },
    { nombre: 'Completado', color: '#28A745', orden: 3 }
  ];

  var promises = [];

  columnas.forEach(function(col) {
    var promise = $.ajax({
      url: './ajax/ajax_guardarColumna.php',
      type: 'POST',
      data: {
        nombre: col.nombre,
        color: col.color,
        orden: col.orden,
        id_kanban_tableros: tableroId
      },
      dataType: 'json'
    });
    promises.push(promise);
  });

  Promise.all(promises).then(function() {
    loadTableros();
  });
}

function renderKanbanBoard(tablero) {
  $('#tablero-titulo').text(tablero.nombre);

  var container = $('#kanban-board');
  container.empty();

  if (!tablero.columnas || tablero.columnas.length === 0) {
    container.html('<p class="text-muted text-center">No hay columnas. Agrega una columna para comenzar.</p>');
    return;
  }

  tablero.columnas.forEach(function(columna) {
    var columnEl = createColumnElement(columna);
    container.append(columnEl);
  });

  // Initialize sortable for drag & drop
  initializeSortable();
}

function createColumnElement(columna) {
  var columnDiv = $('<div>')
    .addClass('kanban-column')
    .attr('data-column-id', columna.id);

  // Header
  var header = $('<div>')
    .addClass('kanban-column-header')
    .css('background-color', columna.color);

  var title = $('<h6>')
    .addClass('kanban-column-title')
    .text(columna.nombre);

  var count = $('<span>')
    .addClass('kanban-column-count')
    .text(columna.tareas.length);

  var actions = $('<div>').addClass('column-actions d-flex gap-1');

  var editBtn = $('<button>')
    .addClass('btn btn-sm btn-link text-white p-1')
    .html('<i class="bi bi-pencil"></i>')
    .on('click', function() {
      openEditColumnModal(columna);
    });

  actions.append(editBtn);
  header.append(title, count, actions);
  columnDiv.append(header);

  // Tasks container
  var tasksDiv = $('<div>')
    .addClass('kanban-tasks')
    .attr('data-column-id', columna.id);

  if (columna.tareas && columna.tareas.length > 0) {
    columna.tareas.forEach(function(tarea) {
      var taskEl = createTaskCard(tarea);
      tasksDiv.append(taskEl);
    });
  }

  columnDiv.append(tasksDiv);

  // Add task button
  var addTaskBtn = $('<button>')
    .addClass('kanban-add-task')
    .html('<i class="bi bi-plus"></i> Agregar tarea')
    .on('click', function() {
      createNewTask(columna.id);
    });

  columnDiv.append(addTaskBtn);

  return columnDiv;
}

function createTaskCard(tarea) {
  var card = $('<div>')
    .addClass('kanban-task')
    .attr('data-task-id', tarea.id)
    .on('click', function() {
      openTaskModal(tarea.id);
    });

  // Checkbox
  var checkboxDiv = $('<div>').addClass('d-flex align-items-start gap-2 mb-2');
  var checkbox = $('<input>')
    .attr('type', 'checkbox')
    .addClass('form-check-input mt-1')
    .prop('checked', tarea.estado === 'Completada')
    .on('click', function(e) {
      e.stopPropagation();
      toggleTaskStatus(tarea.id, $(this).is(':checked'));
    });

  var name = $('<div>')
    .addClass('kanban-task-name flex-grow-1')
    .toggleClass('text-decoration-line-through text-muted', tarea.estado === 'Completada')
    .text(tarea.nombre);

  checkboxDiv.append(checkbox, name);
  card.append(checkboxDiv);

  // Metadata
  var meta = $('<div>').addClass('kanban-task-meta');

  // Users
  if (tarea.usuarios && tarea.usuarios.length > 0) {
    var usersDiv = $('<div>').addClass('d-flex');
    tarea.usuarios.slice(0, 3).forEach(function(userId) {
      var user = allUsers.find(u => u.id == userId);
      if (user) {
        var avatar = $('<div>')
          .addClass('user-avatar')
          .text(user.nombre.charAt(0).toUpperCase())
          .attr('title', user.nombre);
        usersDiv.append(avatar);
      }
    });
    if (tarea.usuarios.length > 3) {
      var more = $('<div>')
        .addClass('user-avatar')
        .css('background-color', '#6c757d')
        .text('+' + (tarea.usuarios.length - 3));
      usersDiv.append(more);
    }
    meta.append(usersDiv);
  }

  // Due date
  if (tarea.fecha_vencimiento && tarea.fecha_vencimiento !== '0000-00-00') {
    var dateSpan = $('<span>')
      .addClass('d-flex align-items-center gap-1')
      .toggleClass('text-danger fw-bold', tarea.vencida);
    dateSpan.append($('<i>').addClass('bi bi-clock'));
    dateSpan.append(formatDate(tarea.fecha_vencimiento));
    meta.append(dateSpan);
  }

  // Files
  if (tarea.cantidad_archivos > 0) {
    var filesSpan = $('<span>').addClass('d-flex align-items-center gap-1');
    filesSpan.append($('<i>').addClass('bi bi-paperclip'));
    filesSpan.append(tarea.cantidad_archivos);
    meta.append(filesSpan);
  }

  // Checklist progress
  if (tarea.progreso_checklist && tarea.progreso_checklist.total > 0) {
    var checklistSpan = $('<span>').addClass('d-flex align-items-center gap-1');
    checklistSpan.append($('<i>').addClass('bi bi-check-square'));
    checklistSpan.append(tarea.progreso_checklist.completados + '/' + tarea.progreso_checklist.total);
    meta.append(checklistSpan);
  }

  card.append(meta);

  return card;
}

function initializeSortable() {
  $('.kanban-tasks').sortable({
    connectWith: '.kanban-tasks',
    placeholder: 'kanban-task-placeholder',
    cursor: 'move',
    opacity: 0.8,
    tolerance: 'pointer',
    start: function(event, ui) {
      ui.placeholder.height(ui.item.height());
    },
    update: function(event, ui) {
      // Only trigger if the item was actually moved to a new position
      if (this === ui.item.parent()[0]) {
        handleTaskMove(ui.item);
      }
    }
  }).disableSelection();
}

function handleTaskMove(taskElement) {
  var taskId = taskElement.data('task-id');
  var newColumnId = taskElement.parent().data('column-id');
  var newPosition = taskElement.index();

  $.ajax({
    url: './ajax/ajax_moverTarea.php',
    type: 'POST',
    data: {
      id: taskId,
      id_kanban_columnas: newColumnId,
      orden: newPosition
    },
    dataType: 'json',
    success: function(response) {
      if (response.status === 'OK') {
        // Update counts
        updateColumnCounts();
      } else {
        showError('Error al mover tarea: ' + response.mensaje);
        loadTableros(); // Reload to reset
      }
    },
    error: function() {
      showError('Error de conexión al mover tarea');
      loadTableros();
    }
  });
}

function updateColumnCounts() {
  $('.kanban-column').each(function() {
    var columnId = $(this).data('column-id');
    var taskCount = $(this).find('.kanban-task').length;
    $(this).find('.kanban-column-count').text(taskCount);
  });
}

// ===========================
// COLUMNAS
// ===========================

function openNewColumnModal() {
  currentColumn = null;
  $('#columnModalTitle').text('Nueva Columna');
  $('#column-name').val('');
  $('#column-color').val('#6A1693');
  $('#btn-delete-column').hide();
  $('#columnModal').modal('show');
}

function openEditColumnModal(columna) {
  currentColumn = columna;
  $('#columnModalTitle').text('Editar Columna');
  $('#column-name').val(columna.nombre);
  $('#column-color').val(columna.color);
  $('#btn-delete-column').show();
  $('#columnModal').modal('show');
}

function saveColumn() {
  var nombre = $('#column-name').val().trim();
  var color = $('#column-color').val();

  if (!nombre) {
    alert('Ingresa un nombre para la columna');
    return;
  }

  var data = {
    nombre: nombre,
    color: color,
    id_kanban_tableros: currentTableroId
  };

  if (currentColumn) {
    data.id = currentColumn.id;
  }

  $.ajax({
    url: './ajax/ajax_guardarColumna.php',
    type: 'POST',
    data: data,
    dataType: 'json',
    success: function(response) {
      if (response.status === 'OK') {
        $('#columnModal').modal('hide');
        loadTableros();
      } else {
        showError('Error al guardar columna: ' + response.mensaje);
      }
    },
    error: function() {
      showError('Error de conexión al guardar columna');
    }
  });
}

function deleteColumn() {
  if (!currentColumn) return;

  if (!confirm('¿Eliminar esta columna? Todas las tareas en ella también serán eliminadas.')) {
    return;
  }

  $.ajax({
    url: './ajax/ajax_eliminarColumna.php',
    type: 'POST',
    data: { id: currentColumn.id },
    dataType: 'json',
    success: function(response) {
      if (response.status === 'OK') {
        $('#columnModal').modal('hide');
        loadTableros();
      } else {
        showError('Error al eliminar columna: ' + response.mensaje);
      }
    },
    error: function() {
      showError('Error de conexión al eliminar columna');
    }
  });
}

// ===========================
// TAREAS
// ===========================

function createNewTask(columnId) {
  var nombre = prompt('Nombre de la tarea:');
  if (!nombre || !nombre.trim()) return;

  $.ajax({
    url: './ajax/ajax_guardarTarea.php',
    type: 'POST',
    data: {
      nombre: nombre,
      id_kanban_columnas: columnId
    },
    dataType: 'json',
    success: function(response) {
      if (response.status === 'OK') {
        loadTableros();
      } else {
        showError('Error al crear tarea: ' + response.mensaje);
      }
    },
    error: function() {
      showError('Error de conexión al crear tarea');
    }
  });
}

function toggleTaskStatus(taskId, isCompleted) {
  var newStatus = isCompleted ? 'Completada' : 'Pendiente';

  $.ajax({
    url: './ajax/ajax_guardarTarea.php',
    type: 'POST',
    data: {
      id: taskId,
      estado: newStatus
    },
    dataType: 'json',
    success: function(response) {
      if (response.status === 'OK') {
        loadTableros();
      }
    }
  });
}

function openTaskModal(taskId) {
  $('#loading-spinner').show();

  $.ajax({
    url: './ajax/ajax_getTarea.php',
    type: 'GET',
    data: { id: taskId },
    dataType: 'json',
    success: function(response) {
      if (response.status === 'OK') {
        currentTask = response.tarea;
        allUsers = response.usuarios || allUsers;
        allLabels = response.etiquetas || allLabels;
        populateTaskModal();
        $('#taskModal').modal('show');
      }
    },
    complete: function() {
      $('#loading-spinner').hide();
    }
  });
}

// (Continue in next part - task modal functions are similar to previous version)
