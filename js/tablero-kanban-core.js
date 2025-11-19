// Store tablero ID or entity ID
var tableroId = window.TableroKanban.config.tableroId || '';
var entityId = window.TableroKanban.config.entityId || '';
var currentTableroId = null;

// Global variables
var allTableros = [];
var allUsers = [];
var allLabels = [];
var currentTask = null;
var currentColumn = null;
var saveTimeout = null;
var isFirstLoad = true;

// Timer variables
var timerInterval = null;
var timerStartTime = null;
var timerElapsedSeconds = 0;
var timerPausedSeconds = 0;
var timerIsPaused = false;

// Cached selectors (initialized on DOM ready)
var $document = $(document);
var $kanbanBoard, $filtersContainer, $taskModal, $columnModal;

console.log('🎯 [INIT] Script loaded, tableroId:', tableroId, 'entityId:', entityId);

$(document).ready(function() {
  console.log('📄 [READY] Document ready');

  // Cache frequently used selectors
  $kanbanBoard = $('#kanban-board');
  $filtersContainer = $('#filters-container');
  $taskModal = $('#taskModal');
  $columnModal = $('#columnModal');

  if (tableroId && tableroId !== '') {
    console.log('✅ [READY] Tablero ID found, loading specific tablero...');
    loadTablero(tableroId);
  } else if (typeof entityId !== 'undefined' && entityId) {
    console.log('✅ [READY] EntityId found, loading tableros...');
    loadTableros();
  } else {
    console.warn('⚠️ [READY] No tableroId or entityId found');
  }

  setupEventHandlers();
});

// ===========================
// EVENT HANDLERS (OPTIMIZED WITH DELEGATION)
// ===========================

function setupEventHandlers() {
  console.log('🎮 [HANDLERS] Setting up delegated event handlers');

  // Single delegated handler for all document clicks
  $document.on('click', handleDocumentClick);

  // Delegated handlers for kanban board interactions
  $document.on('click', '.task-card', handleTaskCardClick);
  $document.on('click', '.column-header', handleColumnHeaderClick);
  $document.on('click', '.btn-add-task', handleAddTaskClick);

  // Form inputs with delegation
  $document.on('input', 'textarea[data-auto-resize]', function() {
    autoResizeTextarea(this);
  });

  $document.on('change', '#task-status-checkbox', handleTaskStatusChange);
  $document.on('input', '#task-name', handleTaskNameInput);
  $document.on('input', '#task-description', handleTaskDescriptionInput);

  // Date changes
  $document.on('change', '#task-start-date-inline, #task-due-date-inline', function() {
    console.log('📅 [EVENT] Date changed inline:', this.id, '=', $(this).val());
    updateTaskDatesInline();
  });


  // Enter key handlers
  $document.on('keypress', '#link-url-inline', function(e) {
    if (e.which === 13) {
      e.preventDefault();
      $('#btn-save-link-inline').click();
    }
  });

  $document.on('keypress', '#checklist-title-inline', function(e) {
    if (e.which === 13) {
      e.preventDefault();
      $('#btn-create-checklist-inline').click();
    }
  });

  // Modal events
  $taskModal.on('hidden.bs.modal', handleTaskModalClose);
  $columnModal.on('hidden.bs.modal', handleColumnModalClose);

  // Bootstrap dropdown events (delegated)
  $document.on('show.bs.dropdown', handleDropdownShow);

  // Filters
  $('#btn-toggle-filters').on('click', toggleFilters);
  $filtersContainer.on('click', function(e) {
    e.stopPropagation();
  });

  $('#filter-search').on('input', function() {
    console.log('🔍 [FILTER] Search changed:', $(this).val());
    applyFilters();
  });

  $('#filter-user, #filter-label, #filter-status, #filter-date').on('change', function() {
    console.log('🔍 [FILTER] Filter changed:', this.id, '=', $(this).val());
    applyFilters();
  });

  // Buttons (specific, non-delegatable)
  $('#btn-agregar-columna').on('click', openNewColumnModal);
  $('#btn-save-column').on('click', saveColumn);
  $('#btn-delete-column').on('click', deleteColumn);

  console.log('✅ [HANDLERS] All event handlers configured');
}

// Delegated event handler for document clicks
function handleDocumentClick(e) {
  var $target = $(e.target);

  // Close filters when clicking outside
  if ($filtersContainer.is(':visible') &&
      !$filtersContainer.is($target) &&
      $filtersContainer.has($target).length === 0 &&
      !$target.closest('#btn-toggle-filters').length) {
    $filtersContainer.hide();
  }

  // Handle various button clicks via ID or data attributes
  var targetId = $target.attr('id');
  var action = $target.data('action');

  // ID-based actions
  if (targetId) {
    handleIdAction(targetId, $target, e);
  }

  // Data-action attribute based actions
  if (action) {
    e.preventDefault();
    handleDataAction(action, $target);
  }
}

// Handle actions by element ID
function handleIdAction(id, $element, e) {
  var actions = {
    'tablero-titulo': editarNombreTablero,
    'description-edit-icon': function() { e.preventDefault(); enterDescriptionEditMode(); },
    'btn-save-description': saveDescriptionEdit,
    'btn-cancel-description': cancelDescriptionEdit,
    'btn-delete-task': function() { e.preventDefault(); deleteTask(); },
    'btn-copy-task': function() { e.preventDefault(); copyTask(); },
    'btn-start-timer': startTimer,
    'btn-pause-timer': pauseTimer,
    'btn-resume-timer': resumeTimer,
    'btn-reset-timer': resetTimer,
    'btn-create-label-inline': createLabelInline,
    'btn-save-link-inline': saveLinkInline,
    'btn-create-checklist-inline': createChecklistInline,
    'btn-clear-filters': clearFilters
  };

  if (actions[id]) {
    console.log('🎬 [ACTION] ID:', id);
    actions[id]($element);
  }
}

// Handle actions by data-action attribute
function handleDataAction(action, $element) {
  console.log('🎬 [ACTION] Data-action:', action);

  var actions = {
    'edit-tablero-name': editarNombreTablero,
    'save-description': saveDescriptionEdit,
    'cancel-description': cancelDescriptionEdit,
    'edit-description': enterDescriptionEditMode,
    'delete-task': deleteTask,
    'copy-task': copyTask,
    'start-timer': startTimer,
    'pause-timer': pauseTimer,
    'reset-timer': resetTimer,
    'add-checklist-item': addChecklistItemInline,
    'add-link-item': addLinkInline,
    'clear-due-date': function() { updateTaskField('fecha_vencimiento', ''); },
    'clear-filters': clearFilters,
    'apply-filters': applyFilters
  };

  if (actions[action]) {
    actions[action]($element);
  }
}

// Handle task card clicks
function handleTaskCardClick(e) {
  if ($(e.target).closest('.task-action-btn, .user-avatar, .label-tag').length) {
    return; // Don't open modal if clicking on interactive elements
  }

  var taskId = $(this).data('task-id');
  if (taskId) {
    openTaskModal(taskId);
  }
}

// Handle column header clicks
function handleColumnHeaderClick(e) {
  if ($(e.target).closest('.column-actions').length) {
    return;
  }

  var columnId = $(this).closest('.kanban-column').data('column-id');
  if (columnId && e.detail === 2) { // Double click
    editColumnName(columnId);
  }
}

// Handle add task button clicks
function handleAddTaskClick(e) {
  e.preventDefault();
  e.stopPropagation();
  var columnId = $(this).closest('.kanban-column').data('column-id');
  if (columnId) {
    openNewTaskModal(columnId);
  }
}

// Handle task status checkbox changes
function handleTaskStatusChange() {
  console.log('☑️ [EVENT] Task status checkbox changed:', $(this).is(':checked'));
  if (currentTask) {
    var newStatus = $(this).is(':checked') ? 'Completada' : 'Pendiente';
    console.log('   Status will be updated to:', newStatus);
    updateTaskField('estado', newStatus);
  }
}

// Handle task name input
function handleTaskNameInput() {
  console.log('✏️ [EVENT] Task name changed');
  triggerAutoSave();
  autoResizeTextarea(this);
}

// Handle task description input
function handleTaskDescriptionInput() {
  console.log('✏️ [EVENT] Task description changed');
  autoResizeTextarea(this);

  var isEmpty = !currentTask.descripcion || currentTask.descripcion.trim() === '';
  if (isEmpty || $(this).val() !== currentTask.descripcion) {
    $('#btn-save-description, #btn-cancel-description').show();
  }
}

// Handle task modal close
function handleTaskModalClose() {
  console.log('🪟 [MODAL] Task modal closed');

  if (timerInterval) {
    pauseTimer();
  }

  currentTask = null;
  clearTaskForm();
}

// Handle column modal close
function handleColumnModalClose() {
  console.log('🪟 [MODAL] Column modal closed');
  currentColumn = null;
  clearColumnForm();
}

// Handle Bootstrap dropdown show events
function handleDropdownShow(e) {
  var $dropdown = $(e.target);
  var dropdownParent = $dropdown.attr('id');

  // Try to find the button that triggered it
  var $btn = $dropdown.find('[data-bs-toggle="dropdown"], [data-toggle="dropdown"]').first();
  var btnId = $btn.attr('id');

  console.log('🔘 [EVENT] Dropdown opening:', dropdownParent, btnId);

  var handlers = {
    'btn-assign-users': populateUsersDropdown,
    'btn-add-labels': populateLabelsDropdown,
    'btn-add-dates': populateDatesDropdown,
    'btn-manage-users': populateTableroUsersDropdown,
    'btn-add-checklist': function() {
      $('#checklist-title-inline').val('').focus();
    },
    'btn-add-link': function() {
      $('#link-title-inline, #link-url-inline').val('');
    }
  };

  if (handlers[btnId]) {
    handlers[btnId]();
  }
}

// Toggle filters display
function toggleFilters(e) {
  e.stopPropagation();
  console.log('🔘 [EVENT] Btn toggle filters clicked');
  $filtersContainer.toggle();
}

// ===========================
// TABLEROS & COLUMNAS
// ===========================

function loadTablero(id) {
  console.log('📥 [AJAX] Loading tablero with id:', id, 'isFirstLoad:', isFirstLoad);

  // Only show spinner on first load
  if (isFirstLoad) {
    $('#loading-spinner').show();
  }

  $.ajax({
    url: './ajax/ajax_getTablero.php',
    type: 'GET',
    data: { id: id },
    dataType: 'json',
    success: function(response) {
      console.log('✅ [AJAX] Get tablero SUCCESS:', response);

      if (response.status === 'OK') {
        allUsers = response.usuarios || [];
        allLabels = response.etiquetas || [];

        console.log('   Users:', allUsers.length);
        console.log('   Labels:', allLabels.length);

        if (response.tablero) {
          currentTableroId = response.tablero.id;
          console.log('   Rendering tablero, id:', currentTableroId);
          renderKanbanBoard(response.tablero);
        } else {
          console.error('❌ [AJAX] No tablero in response');
          showError('Tablero no encontrado');
        }
      } else {
        console.error('❌ [AJAX] Get tablero returned error:', response.mensaje);
        showError('Error al cargar tablero: ' + response.mensaje);
      }
    },
    error: function(xhr, status, error) {
      console.error('❌ [AJAX] Get tablero FAILED:', status, error, xhr);
      showError('Error de conexión al cargar tablero');
    },
    complete: function() {
      console.log('🏁 [AJAX] Get tablero complete');

      // Hide spinner and mark that first load is complete
      if (isFirstLoad) {
        $('#loading-spinner').hide();
        isFirstLoad = false;
        console.log('   First load complete, future loads will not show spinner');
      }
    }
  });
}

function loadTableros() {
  console.log('📥 [AJAX] Loading tableros for entityId:', entityId, 'isFirstLoad:', isFirstLoad);

  // Only show spinner on first load
  if (isFirstLoad) {
    $('#loading-spinner').show();
  }

  $.ajax({
    url: './ajax/ajax_getTableros.php',
    type: 'GET',
    data: { id_entidad: entityId },
    dataType: 'json',
    success: function(response) {
      console.log('✅ [AJAX] Get tableros SUCCESS:', response);

      if (response.status === 'OK') {
        allTableros = response.tableros || [];
        allUsers = response.usuarios || [];
        allLabels = response.etiquetas || [];

        console.log('   Tableros:', allTableros.length);
        console.log('   Users:', allUsers.length);
        console.log('   Labels:', allLabels.length);

        if (allTableros.length > 0) {
          currentTableroId = allTableros[0].id;
          console.log('   Rendering first tablero, id:', currentTableroId);
          renderKanbanBoard(allTableros[0]);
        } else {
          console.log('   No tableros found, creating default board');
          createDefaultBoard();
        }
      } else {
        console.error('❌ [AJAX] Get tableros returned error:', response.mensaje);
        showError('Error al cargar tableros: ' + response.mensaje);
      }
    },
    error: function(xhr, status, error) {
      console.error('❌ [AJAX] Get tableros FAILED:', status, error, xhr);
      showError('Error de conexión al cargar tableros');
    },
    complete: function() {
      console.log('🏁 [AJAX] Get tableros complete');

      // Hide spinner and mark that first load is complete
      if (isFirstLoad) {
        $('#loading-spinner').hide();
        isFirstLoad = false;
        console.log('   First load complete, future loads will not show spinner');
      }
    }
  });
}

function createDefaultBoard() {
  console.log('🆕 [CREATE] Creating default board');

  $.ajax({
    url: './ajax/ajax_guardarTablero.php',
    type: 'POST',
    data: {
      nombre: 'Mi Tablero',
      id_entidad: entityId
    },
    dataType: 'json',
    success: function(response) {
      console.log('✅ [AJAX] Create default board SUCCESS:', response);

      if (response.status === 'OK') {
        currentTableroId = response.tablero.id;
        console.log('   New tablero id:', currentTableroId);
        console.log('   Creating default columns...');
        createDefaultColumns(response.tablero.id);
      }
    },
    error: function(xhr, status, error) {
      console.error('❌ [AJAX] Create default board FAILED:', status, error);
      showError('Error al crear tablero');
    }
  });
}

function createDefaultColumns(tableroId) {
  console.log('🆕 [CREATE] Creating default columns for tablero:', tableroId);

  var columnas = [
    { nombre: 'Por Hacer', color: '#6C757D', orden: 1 },
    { nombre: 'En Progreso', color: '#FFC107', orden: 2 },
    { nombre: 'Completado', color: '#28A745', orden: 3 }
  ];

  console.log('   Columns to create:', columnas);
  var promises = [];

  columnas.forEach(function(col) {
    console.log('   Creating column:', col.nombre);
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
    console.log('✅ [CREATE] All default columns created, reloading...');
    loadTableros();
  });
}

function editarNombreTablero() {
  console.log('✏️ [EDIT] Editing tablero name inline');

  var titulo = $('#tablero-titulo');
  var nombreActual = titulo.text();

  console.log('   Current name:', nombreActual);

  // Create input with same style as h2
  var input = $('<input>')
    .attr('type', 'text')
    .addClass('form-control')
    .css({
      'font-size': '1.5rem',
      'font-weight': '500',
      'border': '1px solid #dee2e6',
      'padding': '0.25rem 0.5rem'
    })
    .val(nombreActual);

  // Replace h2 with input
  titulo.replaceWith(input);
  input.focus().select();

  // Save function
  var saveEdit = function() {
    var nuevoNombre = input.val().trim();

    if (!nuevoNombre || nuevoNombre === nombreActual) {
      console.log('   No change or empty, reverting');
      input.replaceWith(titulo);
      return;
    }

    console.log('   Saving new name:', nuevoNombre);

    // Optimistically update
    var newTitulo = $('<h2>')
      .addClass('mb-0')
      .attr('id', 'tablero-titulo')
      .css('cursor', 'pointer')
      .text(nuevoNombre);

    input.replaceWith(newTitulo);

    // Save to server
    $.ajax({
      url: './ajax/ajax_guardarTablero.php',
      type: 'POST',
      data: {
        id: currentTableroId,
        nombre: nuevoNombre
      },
      dataType: 'json',
      success: function(response) {
        console.log('✅ [AJAX] Save tablero name SUCCESS:', response);

        if (response.status !== 'OK') {
          console.error('❌ [AJAX] Save tablero name returned error:', response.mensaje);
          showError('Error al guardar nombre: ' + response.mensaje);
          // Rollback
          $('#tablero-titulo').text(nombreActual);
        }
      },
      error: function(xhr, status, error) {
        console.error('❌ [AJAX] Save tablero name FAILED:', status, error);
        showError('Error de conexión al guardar nombre');
        // Rollback
        $('#tablero-titulo').text(nombreActual);
      }
    });
  };

  // Cancel function
  var cancelEdit = function() {
    console.log('   Cancelling edit');
    input.replaceWith(titulo);
  };

  // Handle blur (click outside)
  input.on('blur', function() {
    // Small delay to allow click on other elements to register first
    setTimeout(saveEdit, 100);
  });

  // Handle ESC key
  input.on('keydown', function(e) {
    if (e.key === 'Escape') {
      e.preventDefault();
      input.off('blur'); // Remove blur handler to prevent double action
      cancelEdit();
    } else if (e.key === 'Enter') {
      e.preventDefault();
      input.off('blur'); // Remove blur handler to prevent double action
      saveEdit();
    }
  });
}

function renderKanbanBoard(tablero) {
  console.log('🎨 [RENDER] Rendering kanban board:', tablero.nombre);

  // Save tablero globally for filters
  window.currentTablero = tablero;

  $('#tablero-titulo').text(tablero.nombre);

  // Load users for this tablero
  loadTableroUsers();

  var container = $('#kanban-board');
  container.empty();

  if (!tablero.columnas || tablero.columnas.length === 0) {
    console.warn('⚠️ [RENDER] No columns to render');
    container.html('<p class="text-muted text-center">No hay columnas. Agrega una columna para comenzar.</p>');
    return;
  }

  console.log('   Rendering', tablero.columnas.length, 'columns');
  tablero.columnas.forEach(function(columna) {
    console.log('   - Column:', columna.nombre, 'with', columna.tareas.length, 'tasks');
    var columnEl = createColumnElement(columna);
    container.append(columnEl);
  });

  console.log('   Initializing sortable...');
  initializeSortable();
  initializeColumnSortable();

  // Populate filter selects
  populateFilterSelects();
}

function createColumnElement(columna) {
  console.log('🏗️ [BUILD] Creating column element for:', columna.nombre);

  var columnDiv = $('<div>')
    .addClass('kanban-column')
    .attr('data-column-id', columna.id)
    .css('border-color', columna.color);

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
      console.log('✏️ [EVENT] Edit column clicked:', columna.nombre);
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
    .on('click', function(e) {
      e.stopPropagation();
      console.log('➕ [EVENT] Add task clicked for column:', columna.nombre);
      createNewTask(columna.id);
    });

  columnDiv.append(addTaskBtn);

  return columnDiv;
}

function createTaskCard(tarea) {
  console.log('📇 [BUILD] Creating task card for:', tarea.nombre);

  var card = $('<div>')
    .addClass('kanban-task')
    .attr('data-task-id', tarea.id)
    .on('click', function() {
      console.log('👆 [EVENT] Task card clicked:', tarea.nombre, 'id:', tarea.id);
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
      var isChecked = $(this).is(':checked');
      console.log('☑️ [EVENT] Task checkbox clicked:', tarea.nombre, 'checked:', isChecked);
      toggleTaskStatus(tarea.id, isChecked);
    });

  var name = $('<div>')
    .addClass('kanban-task-name flex-grow-1')
    .toggleClass('text-decoration-line-through text-muted', tarea.estado === 'Completada')
    .text(tarea.nombre);

  checkboxDiv.append(checkbox, name);
  card.append(checkboxDiv);

  // Labels
  if (tarea.etiquetas && tarea.etiquetas.length > 0) {
    var labelsDiv = $('<div>').addClass('d-flex gap-1 mb-2 flex-wrap');
    tarea.etiquetas.forEach(function(labelId) {
      var label = allLabels.find(l => l.id == labelId);
      if (label) {
        var labelBadge = $('<div>')
          .addClass('task-label-badge')
          .css('background-color', label.codigo_hex)
          .attr('title', label.nombre);
        labelsDiv.append(labelBadge);
      }
    });
    card.append(labelsDiv);
  }

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
  console.log('🔄 [SORTABLE] Initializing drag and drop for tasks');

  $('.kanban-tasks').sortable({
    connectWith: '.kanban-tasks',
    placeholder: 'kanban-task-placeholder',
    cursor: 'move',
    opacity: 0.8,
    tolerance: 'pointer',
    start: function(event, ui) {
      console.log('🎯 [DRAG] Drag started, task:', ui.item.data('task-id'));
      ui.placeholder.height(ui.item.height());
      // Store the original column
      ui.item.data('original-column', $(this).data('column-id'));
    },
    update: function(event, ui) {
      console.log('📍 [DRAG] Drop detected');
      // Only trigger if the item was actually moved to a new position
      if (this === ui.item.parent()[0]) {
        console.log('   Item moved to new position, handling...');
        var originalColumn = ui.item.data('original-column');
        var newColumn = $(this).data('column-id');

        // Update both columns if moved between columns
        if (originalColumn !== newColumn) {
          console.log('   Task moved between columns, updating both');
          handleTaskMove(ui.item);
          // Also update the original column's order
          updateColumnTaskOrder(originalColumn);
        } else {
          console.log('   Task reordered within same column');
          handleTaskMove(ui.item);
        }
      } else {
        console.log('   Item not in this container, skipping');
      }
    }
  }).disableSelection();

  console.log('✅ [SORTABLE] Task sortable initialized');
}

function updateColumnTaskOrder(columnId) {
  console.log('🔄 [UPDATE] Updating task order for column:', columnId);

  var taskOrders = [];
  $('.kanban-tasks[data-column-id="' + columnId + '"]').find('.kanban-task').each(function(index) {
    var id = $(this).data('task-id');
    taskOrders.push({
      id: id,
      orden: index
    });
  });

  if (taskOrders.length === 0) {
    console.log('   No tasks in column, skipping');
    return;
  }

  $.ajax({
    url: './ajax/ajax_reordenarTareas.php',
    type: 'POST',
    data: {
      id_kanban_columnas: columnId,
      tareas: JSON.stringify(taskOrders)
    },
    dataType: 'json',
    success: function(response) {
      console.log('✅ [AJAX] Update column order SUCCESS:', response);
    },
    error: function(xhr, status, error) {
      console.error('❌ [AJAX] Update column order FAILED:', status, error);
    }
  });
}

function initializeColumnSortable() {
  console.log('🔄 [SORTABLE] Initializing drag and drop for columns');

  $('#kanban-board').sortable({
    items: '.kanban-column',
    handle: '.kanban-column-header',
    cursor: 'move',
    opacity: 0.8,
    placeholder: 'kanban-column-placeholder',
    tolerance: 'pointer',
    start: function(event, ui) {
      console.log('🎯 [DRAG] Column drag started:', ui.item.data('column-id'));
      ui.placeholder.width(ui.item.width());
    },
    update: function(event, ui) {
      console.log('📍 [DRAG] Column dropped, updating order...');
      handleColumnMove();
    }
  }).disableSelection();

  console.log('✅ [SORTABLE] Column sortable initialized');
}

function handleColumnMove() {
  console.log('🚚 [MOVE] Updating column order');

  var columnOrders = [];
  $('#kanban-board .kanban-column').each(function(index) {
    var columnId = $(this).data('column-id');
    console.log('   Column', columnId, 'new order:', index);
    columnOrders.push({
      id: columnId,
      orden: index
    });
  });

  $.ajax({
    url: './ajax/ajax_reordenarColumnas.php',
    type: 'POST',
    data: {
      columnas: JSON.stringify(columnOrders)
    },
    dataType: 'json',
    success: function(response) {
      console.log('✅ [AJAX] Reorder columns SUCCESS:', response);

      if (response.status !== 'OK') {
        console.error('❌ [AJAX] Reorder columns returned error:', response.mensaje);
        showError('Error al reordenar columnas: ' + response.mensaje);
        loadTableros();
      }
    },
    error: function(xhr, status, error) {
      console.error('❌ [AJAX] Reorder columns FAILED:', status, error);
      showError('Error de conexión al reordenar columnas');
      loadTableros();
    }
  });
}

function handleTaskMove(taskElement) {
  var taskId = taskElement.data('task-id');
  var newColumnId = taskElement.parent().data('column-id');
  var newPosition = taskElement.index();

  console.log('🚚 [MOVE] Moving task', taskId, 'to column', newColumnId, 'position', newPosition);

  // Get all tasks in the column and update their order
  var taskOrders = [];
  taskElement.parent().find('.kanban-task').each(function(index) {
    var id = $(this).data('task-id');
    console.log('   Task', id, 'new order:', index);
    taskOrders.push({
      id: id,
      orden: index
    });
  });

  $.ajax({
    url: './ajax/ajax_reordenarTareas.php',
    type: 'POST',
    data: {
      id_tarea_movida: taskId,
      id_kanban_columnas: newColumnId,
      tareas: JSON.stringify(taskOrders)
    },
    dataType: 'json',
    success: function(response) {
      console.log('✅ [AJAX] Move task SUCCESS:', response);

      if (response.status === 'OK') {
        console.log('   Updating column counts...');
        updateColumnCounts();
      } else {
        console.error('❌ [AJAX] Move task returned error:', response.mensaje);
        showError('Error al mover tarea: ' + response.mensaje);
        loadTableros();
      }
    },
    error: function(xhr, status, error) {
      console.error('❌ [AJAX] Move task FAILED:', status, error);
      showError('Error de conexión al mover tarea');
      loadTableros();
    }
  });
}

function updateColumnCounts() {
  console.log('🔢 [COUNT] Updating column counts');

  $('.kanban-column').each(function() {
    var columnId = $(this).data('column-id');
    var taskCount = $(this).find('.kanban-task').length;
    console.log('   Column', columnId, ':', taskCount, 'tasks');
    $(this).find('.kanban-column-count').text(taskCount);
  });
}

// ===========================
// COLUMNAS
// ===========================

function openNewColumnModal() {
  console.log('🪟 [MODAL] Opening new column modal');
  currentColumn = null;
  $('#columnModalTitle').text('Nueva Columna');
  $('#column-name').val('');
  $('#column-color').val('#6A1693');
  $('#btn-delete-column').hide();
  $('#columnModal').modal('show');
}

function openEditColumnModal(columna) {
  console.log('🪟 [MODAL] Opening edit column modal for:', columna.nombre);
  currentColumn = columna;
  $('#columnModalTitle').text('Editar Columna');
  $('#column-name').val(columna.nombre);
  $('#column-color').val(columna.color);
  $('#btn-delete-column').show();
  $('#columnModal').modal('show');
}

async function saveColumn() {
  var nombre = $('#column-name').val().trim();
  var color = $('#column-color').val();

  console.log('💾 [SAVE] Saving column, name:', nombre, 'color:', color);

  if (!nombre) {
    console.warn('⚠️ [SAVE] No column name provided');
    await showAlert('Ingresa un nombre para la columna', 'Error');
    return;
  }

  var data = {
    nombre: nombre,
    color: color,
    id_kanban_tableros: currentTableroId
  };

  if (currentColumn) {
    data.id = currentColumn.id;
    console.log('   Updating existing column, id:', currentColumn.id);
  } else {
    console.log('   Creating new column');
  }

  $.ajax({
    url: './ajax/ajax_guardarColumna.php',
    type: 'POST',
    data: data,
    dataType: 'json',
    success: function(response) {
      console.log('✅ [AJAX] Save column SUCCESS:', response);

      if (response.status === 'OK') {
        $('#columnModal').modal('hide');
        loadTableros();
      } else {
        console.error('❌ [AJAX] Save column returned error:', response.mensaje);
        showError('Error al guardar columna: ' + response.mensaje);
      }
    },
    error: function(xhr, status, error) {
      console.error('❌ [AJAX] Save column FAILED:', status, error);
      showError('Error de conexión al guardar columna');
    }
  });
}

async function deleteColumn() {
  if (!currentColumn) return;

  console.log('🗑️ [DELETE] Deleting column:', currentColumn.nombre, 'id:', currentColumn.id);

  var confirmed = await showConfirm('¿Eliminar esta columna? Todas las tareas en ella también serán eliminadas.', 'Eliminar Columna');

  if (!confirmed) {
    console.log('   User cancelled deletion');
    return;
  }

  console.log('   User confirmed deletion, proceeding with optimistic delete...');

  // Close modal immediately
  $('#columnModal').modal('hide');

  // Find and hide the column immediately (optimistic)
  var columnDiv = $('.kanban-column[data-column-id="' + currentColumn.id + '"]');
  var columnHTML = columnDiv.prop('outerHTML'); // Save HTML for potential rollback
  var columnParent = columnDiv.parent();

  // Add fade out animation
  columnDiv.css('opacity', '0.3');
  setTimeout(function() {
    columnDiv.hide();
  }, 200);

  $.ajax({
    url: './ajax/ajax_eliminarColumna.php',
    type: 'POST',
    data: { id: currentColumn.id },
    success: function(response) {
      console.log('📥 [AJAX] Delete column RAW RESPONSE:', response);
      console.log('📥 [AJAX] Response type:', typeof response);

      try {
        var parsedResponse = typeof response === 'string' ? JSON.parse(response) : response;
        console.log('✅ [AJAX] Delete column PARSED:', parsedResponse);

        if (parsedResponse.status === 'OK') {
          // Actually remove the column from DOM
          columnDiv.remove();
          console.log('   Column deleted successfully');
        } else {
          console.error('❌ [AJAX] Delete column returned error:', parsedResponse.mensaje);
          showError('Error al eliminar columna: ' + parsedResponse.mensaje);
          // Rollback: restore the column
          columnDiv.css('opacity', '1').show();
        }
      } catch(e) {
        console.error('❌ [AJAX] Error parsing response:', e);
        // Rollback: restore the column
        columnDiv.css('opacity', '1').show();
        showError('Error al eliminar columna');
      }
    },
    error: function(xhr, status, error) {
      console.error('❌ [AJAX] Delete column FAILED:', status, error);
      console.error('   XHR:', xhr);
      console.error('   Response Text:', xhr.responseText);

      // Rollback: restore the column
      columnDiv.css('opacity', '1').show();
      showError('Error de conexión al eliminar columna');
    }
  });
}

// ===========================
// TAREAS
// ===========================

function createNewTask(columnId) {
  console.log('🆕 [CREATE] Creating new task in column:', columnId);

  var tasksContainer = $('.kanban-tasks[data-column-id="' + columnId + '"]');
  var addTaskBtn = tasksContainer.parent().find('.kanban-add-task');

  // Hide the add task button
  addTaskBtn.hide();

  // Create the new task card
  showNewTaskCard(columnId, tasksContainer, addTaskBtn);
}

function showNewTaskCard(columnId, tasksContainer, addTaskBtn) {
  // Create card container
  var newTaskCard = $('<div>')
    .addClass('kanban-task new-task-card')
    .css({
      'background-color': 'white',
      'cursor': 'default'
    })
    .on('click', function(e) {
      e.stopPropagation();
    });

  // Create textarea
  var textarea = $('<textarea>')
    .addClass('form-control')
    .attr('placeholder', 'Nombre de la tarea...')
    .css({
      'border': 'none',
      'resize': 'none',
      'padding': '0',
      'min-height': '50px',
      'outline': 'none',
      'box-shadow': 'none'
    })
    .on('input', function() {
      autoResizeTextarea(this);
    });

  // Create buttons container
  var buttonsDiv = $('<div>').addClass('d-flex gap-2 mt-2');

  var saveBtn = $('<button>')
    .addClass('btn btn-primary btn-sm')
    .text('Guardar')
    .on('click', function(e) {
      e.stopPropagation();
      saveNewTask(columnId, textarea.val(), tasksContainer, addTaskBtn);
    });

  var cancelBtn = $('<button>')
    .addClass('btn btn-secondary btn-sm')
    .text('Cancelar')
    .on('click', function(e) {
      e.stopPropagation();
      cancelNewTask(newTaskCard, addTaskBtn);
    });

  buttonsDiv.append(saveBtn, cancelBtn);
  newTaskCard.append(textarea, buttonsDiv);

  // Add card to container
  tasksContainer.append(newTaskCard);

  // Focus textarea
  setTimeout(function() {
    textarea.focus();
    autoResizeTextarea(textarea[0]);
  }, 50);

  // Handle ESC key
  textarea.on('keydown', function(e) {
    if (e.key === 'Escape') {
      e.preventDefault();
      cancelNewTask(newTaskCard, addTaskBtn);
    } else if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      saveNewTask(columnId, textarea.val(), tasksContainer, addTaskBtn);
    }
  });

  // Handle click outside (with delay to avoid immediate trigger)
  setTimeout(function() {
    $(document).on('click.newTask', function(e) {
      if (!$(e.target).closest('.new-task-card').length) {
        cancelNewTask(newTaskCard, addTaskBtn);
      }
    });
  }, 100);
}

function saveNewTask(columnId, nombre, tasksContainer, addTaskBtn) {
  if (!nombre || !nombre.trim()) {
    console.log('   Empty task name, ignoring');
    return;
  }

  console.log('💾 [SAVE] Saving new task:', nombre);

  // Remove the current new task card
  tasksContainer.find('.new-task-card').remove();

  // Create optimistic task object
  var tempId = 'temp-' + Date.now();
  var optimisticTask = {
    id: tempId,
    nombre: nombre.trim(),
    estado: 'Pendiente',
    descripcion: '',
    usuarios: [],
    etiquetas: [],
    fecha_inicio: null,
    fecha_vencimiento: null,
    checklist: [],
    links: []
  };

  // Create and show the task card immediately
  var taskCard = createTaskCard(optimisticTask);
  taskCard.addClass('optimistic-task').css('opacity', '0.7');
  tasksContainer.append(taskCard);

  // Show a new task card to continue creating tasks
  showNewTaskCard(columnId, tasksContainer, addTaskBtn);

  // Save to server
  $.ajax({
    url: './ajax/ajax_guardarTarea.php',
    type: 'POST',
    data: {
      nombre: nombre.trim(),
      id_kanban_columnas: columnId
    },
    dataType: 'json',
    success: function(response) {
      console.log('✅ [AJAX] Create task SUCCESS:', response);

      if (response.status === 'OK') {
        // Replace optimistic task with real task
        var realTask = response.tarea;
        var newTaskCard = createTaskCard(realTask);
        taskCard.replaceWith(newTaskCard);

        console.log('   Task created with ID:', realTask.id);
      } else {
        console.error('❌ [AJAX] Create task returned error:', response.mensaje);
        showError('Error al crear tarea: ' + response.mensaje);
        // Remove the optimistic task
        taskCard.remove();
      }
    },
    error: function(xhr, status, error) {
      console.error('❌ [AJAX] Create task FAILED:', status, error);
      showError('Error de conexión al crear tarea');
      // Remove the optimistic task
      taskCard.remove();
    }
  });
}

function cancelNewTask(newTaskCard, addTaskBtn) {
  console.log('❌ [CANCEL] Canceling new task creation');

  newTaskCard.remove();
  addTaskBtn.show();
  $(document).off('click.newTask');
}

function toggleTaskStatus(taskId, isCompleted) {
  var newStatus = isCompleted ? 'Completada' : 'Pendiente';

  console.log('🔄 [STATUS] Toggling task', taskId, 'to:', newStatus);

  $.ajax({
    url: './ajax/ajax_guardarTarea.php',
    type: 'POST',
    data: {
      id: taskId,
      estado: newStatus
    },
    dataType: 'json',
    success: function(response) {
      console.log('✅ [AJAX] Toggle status SUCCESS:', response);

      if (response.status === 'OK') {
        loadTableros();
      }
    },
    error: function(xhr, status, error) {
      console.error('❌ [AJAX] Toggle status FAILED:', status, error);
    }
  });
}

function openTaskModal(taskId) {
  console.log('🪟 [MODAL] Opening task modal for id:', taskId);

  // Show modal immediately with loading spinner
  $('#task-header-content').hide();
  $('#task-modal-content').hide();
  $('#task-modal-loading').show();
  $('#taskModal').modal('show');

  $.ajax({
    url: './ajax/ajax_getTarea.php',
    type: 'GET',
    data: { id: taskId },
    dataType: 'json',
    success: function(response) {
      console.log('✅ [AJAX] Get task SUCCESS:', response);

      if (response.status === 'OK') {
        currentTask = response.tarea;
        allUsers = response.usuarios || allUsers;
        allLabels = response.etiquetas || allLabels;

        console.log('   Current task:', currentTask);
        console.log('   Populating modal...');
        populateTaskModal();

        // Hide loading, show content
        $('#task-modal-loading').hide();
        $('#task-header-content').show();
        $('#task-modal-content').show();
      }
    },
    error: function(xhr, status, error) {
      console.error('❌ [AJAX] Get task FAILED:', status, error);
      $('#taskModal').modal('hide');
      showError('Error al cargar la tarea');
    }
  });
}

// ===========================
// TASK MODAL FUNCTIONS
// ===========================

function populateTaskModal() {
  if (!currentTask) return;

  console.log('📝 [POPULATE] Populating task modal with:', currentTask.nombre);

  $('#task-name').val(currentTask.nombre);
  $('#task-status-checkbox').prop('checked', currentTask.estado === 'Completada');

  displayTaskUsers();
  displayTaskLabels();
  displayTaskDates();
  displayTaskChecklists();
  displayTaskLinks();
  displayTaskDescription();

  // Update timer display
  $('#timer-display').text('00:00:00');
  $('#btn-start-timer').show();
  $('#btn-pause-timer').hide();
  $('#btn-resume-timer').hide();

  // Show reset button only if there's accumulated time
  var hasTime = parseInt(currentTask.time_elapsed) > 0;
  if (hasTime) {
    $('#btn-reset-timer').show();
  } else {
    $('#btn-reset-timer').hide();
  }

  updateTotalTimeDisplay();

  console.log('✅ [POPULATE] Task modal populated');
}

function clearTaskForm() {
  console.log('🧹 [CLEAR] Clearing task form');

  $('#task-name').val('');
  $('#task-description').val('');
  $('#task-status-checkbox').prop('checked', false);
  $('#task-users-display').empty();
  $('#task-labels-display').empty();
  $('#task-dates-display').empty();
  $('#task-checklists-container').empty();
  $('#task-links-container').empty();

  // Clear description view/edit states
  $('#task-description-view').hide();
  $('#task-description-edit-container').hide();
  $('#description-edit-icon').hide();
}

function displayTaskDescription() {
  console.log('📝 [DISPLAY] Displaying task description');

  var description = currentTask.descripcion || '';
  var isEmpty = !description || description.trim() === '';

  // Reset all states
  $('#task-description-view').hide();
  $('#task-description-edit-container').hide();
  $('#description-edit-icon').hide();

  if (isEmpty) {
    // State 1: Empty description - show edit mode
    console.log('   Description is empty, showing edit mode');
    $('#task-description').val('');
    $('#task-description-edit-container').show();
    $('#btn-save-description').hide();
    $('#btn-cancel-description').hide();
  } else {
    // State 3: Has description - show view mode with edit icon on hover
    console.log('   Description exists, showing view mode');
    var formattedDescription = linkifyText(description);
    $('#task-description-view').html(formattedDescription).show();
    $('#description-edit-icon').css('display', 'none'); // Will show on hover via CSS
  }
}

function enterDescriptionEditMode() {
  console.log('✏️ [EDIT] Entering description edit mode');

  var currentDescription = currentTask.descripcion || '';

  // Store original value for cancel
  $('#task-description').data('original-value', currentDescription);
  $('#task-description').val(currentDescription);

  // Show edit container, hide view
  $('#task-description-view').hide();
  $('#task-description-edit-container').show();
  $('#description-edit-icon').hide();

  // Show both buttons in edit mode
  $('#btn-save-description').show();
  $('#btn-cancel-description').show();

  // Focus textarea
  $('#task-description').focus();
  autoResizeTextarea($('#task-description')[0]);
}

function saveDescriptionEdit() {
  console.log('💾 [SAVE] Saving description');

  var newDescription = $('#task-description').val();
  currentTask.descripcion = newDescription;

  triggerAutoSave();
  displayTaskDescription();
}

function cancelDescriptionEdit() {
  console.log('❌ [CANCEL] Cancelling description edit');

  // Restore original value
  var originalValue = $('#task-description').data('original-value');
  $('#task-description').val(originalValue);

  displayTaskDescription();
}

function displayTaskUsers() {
  console.log('👥 [DISPLAY] Displaying task users');

  var container = $('#task-users-display');
  container.empty();

  if (!currentTask.usuarios || currentTask.usuarios.length === 0) {
    console.log('   No users to display');
    return;
  }

  console.log('   Displaying', currentTask.usuarios.length, 'users');

  var usersDiv = $('<div>').addClass('d-flex align-items-center gap-2 mb-2');
  usersDiv.append($('<small>').addClass('text-muted').text('Asignado a:'));

  var avatarsDiv = $('<div>').addClass('d-flex');
  currentTask.usuarios.forEach(function(userId) {
    var user = allUsers.find(u => u.id == userId);
    if (user) {
      var avatar = $('<div>')
        .addClass('user-avatar')
        .css({'margin-left': '0', 'margin-right': '0.25rem'})
        .text(user.nombre.charAt(0).toUpperCase())
        .attr('title', user.nombre);
      avatarsDiv.append(avatar);
    }
  });

  usersDiv.append(avatarsDiv);
  container.append(usersDiv);
}

function displayTaskLabels() {
  console.log('🏷️ [DISPLAY] Displaying task labels');

  var container = $('#task-labels-display');
  container.empty();

  if (!currentTask.etiquetas || currentTask.etiquetas.length === 0) {
    console.log('   No labels to display');
    return;
  }

  console.log('   Displaying', currentTask.etiquetas.length, 'labels');

  var labelsDiv = $('<div>').addClass('mb-2');
  currentTask.etiquetas.forEach(function(labelId) {
    var label = allLabels.find(l => l.id == labelId);
    if (label) {
      var badge = $('<span>')
        .addClass('label-badge me-1 mb-1')
        .css('background-color', label.codigo_hex)
        .text(label.nombre);
      labelsDiv.append(badge);
    }
  });

  container.append(labelsDiv);
}

function displayTaskDates() {
  console.log('📅 [DISPLAY] Displaying task dates');

  var container = $('#task-dates-display');
  container.empty();

  if ((!currentTask.fecha_inicio || currentTask.fecha_inicio === '0000-00-00') &&
      (!currentTask.fecha_vencimiento || currentTask.fecha_vencimiento === '0000-00-00')) {
    console.log('   No dates to display');
    return;
  }

  var datesDiv = $('<div>').addClass('small text-muted mb-2');

  if (currentTask.fecha_inicio && currentTask.fecha_inicio !== '0000-00-00') {
    console.log('   Start date:', currentTask.fecha_inicio);
    datesDiv.append($('<div>').text('Inicio: ' + formatDate(currentTask.fecha_inicio)));
  }

  if (currentTask.fecha_vencimiento && currentTask.fecha_vencimiento !== '0000-00-00') {
    console.log('   Due date:', currentTask.fecha_vencimiento);
    datesDiv.append($('<div>').text('Vencimiento: ' + formatDate(currentTask.fecha_vencimiento)));
  }

  container.append(datesDiv);
}

function displayTaskChecklists() {
  console.log('✅ [DISPLAY] Displaying task checklists');

  var container = $('#task-checklists-container');

  // Store which checklists are currently open before clearing
  var openChecklists = [];
  container.find('[id^="checklist-content-"]').each(function() {
    if ($(this).is(':visible')) {
      var index = parseInt($(this).attr('id').replace('checklist-content-', ''));
      openChecklists.push(index);
    }
  });

  container.empty();

  if (!currentTask.checklist || !Array.isArray(currentTask.checklist) || currentTask.checklist.length === 0) {
    console.log('   No checklists to display');
    return;
  }

  console.log('   Displaying', currentTask.checklist.length, 'checklists');

  currentTask.checklist.forEach(function(checklist, checklistIndex) {
    console.log('   - Checklist:', checklist.title);

    var checklistDiv = $('<div>')
      .addClass('card mb-3 checklist-draggable')
      .attr('draggable', 'true')
      .attr('data-checklist-index', checklistIndex);

    var cardBody = $('<div>').addClass('card-body');

    // Header
    var header = $('<div>').addClass('d-flex justify-content-between align-items-center mb-2');

    // Left side: drag handle + chevron + title
    var headerLeft = $('<div>').addClass('d-flex align-items-center flex-grow-1');

    // Drag handle
    var dragHandle = $('<i>')
      .addClass('bi bi-grip-vertical me-2 text-muted')
      .css('cursor', 'grab');

    // Check if this checklist was previously open
    var wasOpen = openChecklists.indexOf(checklistIndex) !== -1;

    var chevron = $('<i>')
      .addClass('bi ' + (wasOpen ? 'bi-chevron-down' : 'bi-chevron-right') + ' me-2')
      .attr('id', 'chevron-' + checklistIndex)
      .css('cursor', 'pointer');

    // Calculate progress for display
    var total = checklist.items ? checklist.items.length : 0;
    var completed = checklist.items ? checklist.items.filter(item => item.completed).length : 0;
    var progressText = ' (' + completed + '/' + total + ')';

    // Editable title
    var titleElement = $('<h6>')
      .addClass('mb-0')
      .css('cursor', 'pointer')
      .text(checklist.title + progressText)
      .on('dblclick', function(e) {
        e.stopPropagation();
        console.log('✏️ [EVENT] Edit checklist title clicked');
        var fullText = $(this).text();
        var currentTitle = fullText.replace(/\s*\(\d+\/\d+\)$/, '');
        var input = $('<input>')
          .attr('type', 'text')
          .addClass('form-control form-control-sm')
          .val(currentTitle)
          .on('blur', function() {
            var newTitle = $(this).val().trim();
            if (newTitle && newTitle !== currentTitle) {
              console.log('💾 [SAVE] Updating checklist title to:', newTitle);
              currentTask.checklist[checklistIndex].title = newTitle;
              triggerAutoSave();
            }
            displayTaskChecklists();
          })
          .on('keypress', function(e) {
            if (e.which === 13) {
              $(this).blur();
            }
          });
        $(this).replaceWith(input);
        input.focus().select();
      });

    headerLeft.append(dragHandle, chevron, titleElement);
    header.append(headerLeft);

    // Delete dropdown with confirmation
    var deleteContainer = $('<div>').addClass('dropdown');

    var deleteBtn = $('<button>')
      .addClass('btn btn-sm btn-link text-danger')
      .attr({
        'type': 'button',
        'data-bs-toggle': 'dropdown',
        'aria-expanded': 'false'
      })
      .html('<i class="bi bi-trash"></i>');

    var confirmDropdown = $('<div>')
      .addClass('dropdown-menu dropdown-menu-end p-2')
      .css('min-width', '200px');

    var confirmText = $('<p>')
      .addClass('small mb-2')
      .text('¿Eliminar este checklist?');

    var confirmBtn = $('<button>')
      .addClass('btn btn-danger btn-sm w-100 mb-1')
      .text('Eliminar')
      .on('click', function() {
        console.log('🗑️ [EVENT] Delete checklist confirmed:', checklist.title);
        var dropdown = bootstrap.Dropdown.getInstance(deleteBtn[0]);
        if (dropdown) dropdown.hide();
        deleteChecklist(checklistIndex);
      });

    var cancelBtn = $('<button>')
      .addClass('btn btn-secondary btn-sm w-100')
      .text('Cancelar')
      .on('click', function() {
        var dropdown = bootstrap.Dropdown.getInstance(deleteBtn[0]);
        if (dropdown) dropdown.hide();
      });

    confirmDropdown.append(confirmText, confirmBtn, cancelBtn);
    deleteContainer.append(deleteBtn, confirmDropdown);
    header.append(deleteContainer);

    cardBody.append(header);

    // Collapsible content wrapper (show if was previously open, otherwise hidden)
    var contentId = 'checklist-content-' + checklistIndex;
    var content = $('<div>').attr('id', contentId).css('display', wasOpen ? 'block' : 'none');

    // Progress
    var total = checklist.items ? checklist.items.length : 0;
    var completed = checklist.items ? checklist.items.filter(item => item.completed).length : 0;
    var percentage = total > 0 ? Math.round((completed / total) * 100) : 0;

    var progressDiv = $('<div>').addClass('mb-2');
    progressDiv.append($('<small>').addClass('text-muted').text(percentage + '%'));

    var progressBar = $('<div>').addClass('progress-bar-custom');
    var progressFill = $('<div>').addClass('progress-fill').css('width', percentage + '%');
    progressBar.append(progressFill);
    progressDiv.append(progressBar);

    content.append(progressDiv);

    // Items
    if (checklist.items && Array.isArray(checklist.items)) {
      checklist.items.forEach(function(item, itemIndex) {
        var itemDiv = $('<div>').addClass('checklist-item');

        var checkbox = $('<input>')
          .attr('type', 'checkbox')
          .addClass('form-check-input')
          .prop('checked', item.completed)
          .on('change', function() {
            var isChecked = $(this).is(':checked');
            console.log('☑️ [EVENT] Checklist item changed:', item.text, 'checked:', isChecked);
            updateChecklistItem(checklistIndex, itemIndex, 'completed', isChecked);
          });

        var text = $('<span>').addClass('flex-grow-1').html(linkifyText(item.text));

        var deleteItemBtn = $('<button>')
          .addClass('btn btn-sm btn-link text-danger')
          .html('<i class="bi bi-x"></i>')
          .on('click', function() {
            console.log('🗑️ [EVENT] Delete checklist item clicked:', item.text);
            deleteChecklistItem(checklistIndex, itemIndex);
          });

        itemDiv.append(checkbox, text, deleteItemBtn);
        content.append(itemDiv);
      });
    }

    // Add item inline form
    var addItemContainer = $('<div>').addClass('dropdown mt-2');

    var addItemBtn = $('<button>')
      .addClass('btn btn-sm btn-link dropdown-toggle')
      .attr({
        'type': 'button',
        'data-bs-toggle': 'dropdown',
        'aria-expanded': 'false'
      })
      .text('+ Agregar Item');

    var dropdownMenu = $('<div>')
      .addClass('dropdown-menu p-2')
      .css('min-width', '250px');

    var inputGroup = $('<div>').addClass('input-group input-group-sm mb-2');
    var itemInput = $('<input>')
      .attr({
        'type': 'text',
        'placeholder': 'Texto del item',
        'data-checklist-index': checklistIndex
      })
      .addClass('form-control checklist-item-input');

    inputGroup.append(itemInput);

    var addBtn = $('<button>')
      .addClass('btn btn-primary btn-sm w-100')
      .text('Agregar')
      .on('click', function() {
        var text = itemInput.val().trim();
        if (!text) return;

        console.log('➕ [ADD] Adding item to checklist at index:', checklistIndex);
        console.log('   Item text:', text);

        if (!currentTask.checklist[checklistIndex].items) {
          currentTask.checklist[checklistIndex].items = [];
        }

        currentTask.checklist[checklistIndex].items.push({
          id: generateId(),
          text: text,
          completed: false
        });

        console.log('   Total items in checklist:', currentTask.checklist[checklistIndex].items.length);

        displayTaskChecklists();
        triggerAutoSave();
      });

    // Handle Enter key
    itemInput.on('keypress', function(e) {
      if (e.which === 13) {
        e.preventDefault();
        addBtn.click();
      }
    });

    dropdownMenu.append(inputGroup, addBtn);
    addItemContainer.append(addItemBtn, dropdownMenu);
    content.append(addItemContainer);

    // Focus input when dropdown opens
    addItemContainer.on('show.bs.dropdown', function() {
      setTimeout(function() {
        itemInput.val('').focus();
      }, 100);
    });

    // Append content to cardBody
    cardBody.append(content);

    // Toggle collapse on chevron click only
    chevron.on('click', function(e) {
      e.stopPropagation();
      $('#' + contentId).slideToggle(200);
      if ($(this).hasClass('bi-chevron-down')) {
        $(this).removeClass('bi-chevron-down').addClass('bi-chevron-right');
      } else {
        $(this).removeClass('bi-chevron-right').addClass('bi-chevron-down');
      }
    });

    // Drag and drop handlers
    checklistDiv.on('dragstart', function(e) {
      $(this).addClass('dragging');
      e.originalEvent.dataTransfer.effectAllowed = 'move';
      e.originalEvent.dataTransfer.setData('text/html', $(this).html());
    });

    checklistDiv.on('dragend', function(e) {
      $(this).removeClass('dragging');
      $('.drag-over').removeClass('drag-over');
    });

    checklistDiv.on('dragover', function(e) {
      e.preventDefault();
      e.originalEvent.dataTransfer.dropEffect = 'move';

      var dragging = $('.dragging');
      if (dragging.length === 0) return;
      if ($(this).hasClass('dragging')) return;

      var bounding = this.getBoundingClientRect();
      var offset = bounding.y + (bounding.height / 2);

      if (e.originalEvent.clientY - offset > 0) {
        $(this).after(dragging);
      } else {
        $(this).before(dragging);
      }
    });

    checklistDiv.on('dragenter', function(e) {
      if (!$(this).hasClass('dragging')) {
        $(this).addClass('drag-over');
      }
    });

    checklistDiv.on('dragleave', function(e) {
      $(this).removeClass('drag-over');
    });

    checklistDiv.on('drop', function(e) {
      e.preventDefault();
      $(this).removeClass('drag-over');

      // Update the checklist array based on new DOM order
      var newOrder = [];
      container.find('.checklist-draggable').each(function() {
        var index = parseInt($(this).attr('data-checklist-index'));
        newOrder.push(currentTask.checklist[index]);
      });

      currentTask.checklist = newOrder;
      console.log('📋 [REORDER] Checklists reordered, saving...');
      displayTaskChecklists();
      triggerAutoSave();
    });

    checklistDiv.append(cardBody);
    container.append(checklistDiv);
  });
}

function displayTaskLinks() {
  console.log('🔗 [DISPLAY] Displaying task links');

  var container = $('#task-links-container');
  container.empty();

  if (!currentTask.links || !Array.isArray(currentTask.links) || currentTask.links.length === 0) {
    console.log('   No links to display');
    return;
  }

  console.log('   Displaying', currentTask.links.length, 'links');

  var linksDiv = $('<div>').addClass('mb-2');

  currentTask.links.forEach(function(link, linkIndex) {
    console.log('   - Link:', link.title, link.url);

    var linkDiv = $('<div>').addClass('d-flex align-items-center gap-2 mb-1');

    var anchor = $('<a>')
      .attr('href', link.url)
      .attr('target', '_blank')
      .addClass('text-primary')
      .html('<i class="bi bi-link-45deg"></i> ' + (link.title || link.url));

    var deleteBtn = $('<button>')
      .addClass('btn btn-sm btn-link text-danger')
      .html('<i class="bi bi-x"></i>')
      .on('click', function() {
        console.log('🗑️ [EVENT] Delete link clicked:', link.title);
        deleteLink(linkIndex);
      });

    linkDiv.append(anchor, deleteBtn);
    linksDiv.append(linkDiv);
  });

  container.append(linksDiv);
}

// ===========================
// AUTO-SAVE
// ===========================

function triggerAutoSave() {
  if (!currentTask || !currentTask.id) return;

  console.log('⏰ [AUTOSAVE] Auto-save triggered, waiting 1s...');

  if (saveTimeout) {
    clearTimeout(saveTimeout);
  }

  saveTimeout = setTimeout(function() {
    autoSaveTask();
  }, 1000);
}

function autoSaveTask() {
  if (!currentTask || !currentTask.id) return;

  console.log('💾 [AUTOSAVE] Auto-saving task:', currentTask.id);

  $('#saving-indicator').show();

  var data = {
    id: currentTask.id,
    nombre: $('#task-name').val(),
    descripcion: $('#task-description').val(),
    estado: $('#task-status-checkbox').is(':checked') ? 'Completada' : 'Pendiente',
    id_kanban_columnas: currentTask.id_kanban_columnas,
    checklist: JSON.stringify(currentTask.checklist || []),
    links: JSON.stringify(currentTask.links || []),
    usuarios: currentTask.usuarios || [],
    etiquetas: currentTask.etiquetas || []
  };

  if (currentTask.fecha_inicio) data.fecha_inicio = currentTask.fecha_inicio;
  if (currentTask.fecha_vencimiento) data.fecha_vencimiento = currentTask.fecha_vencimiento;

  console.log('   Data to save:', data);

  $.ajax({
    url: './ajax/ajax_guardarTarea.php',
    type: 'POST',
    data: data,
    dataType: 'json',
    success: function(response) {
      console.log('✅ [AJAX] Auto-save SUCCESS:', response);

      if (response.status === 'OK') {
        currentTask = response.tarea;
        loadTableros();
      }
    },
    error: function(xhr, status, error) {
      console.error('❌ [AJAX] Auto-save FAILED:', status, error);
    },
    complete: function() {
      $('#saving-indicator').hide();
    }
  });
}

function updateTaskField(field, value) {
  if (!currentTask) return;

  console.log('🔄 [UPDATE] Updating task field:', field, '=', value);

  currentTask[field] = value;
  triggerAutoSave();
}

async function deleteTask() {
  if (!currentTask || !currentTask.id) return;

  console.log('🗑️ [DELETE] Deleting task:', currentTask.nombre, 'id:', currentTask.id);

  // Save task ID and data before closing modal
  var taskIdToDelete = currentTask.id;
  var taskData = JSON.parse(JSON.stringify(currentTask)); // Deep copy

  // Close task modal before showing confirmation
  console.log('   Closing task modal before confirmation');
  $('#taskModal').modal('hide');

  // Wait a bit for modal to close
  await new Promise(resolve => setTimeout(resolve, 300));

  // Show confirmation dialog
  var confirmed = await showConfirm('¿Está seguro de eliminar esta tarea?', 'Eliminar Tarea');

  if (!confirmed) {
    console.log('   User cancelled deletion, reopening task modal');
    // Reopen the task modal
    openTaskModal(taskIdToDelete);
    return;
  }

  console.log('   User confirmed deletion, proceeding with optimistic delete...');

  // Find and hide the task card immediately (optimistic)
  var taskCard = $('.kanban-task[data-task-id="' + taskIdToDelete + '"]');
  var taskCardHTML = taskCard.prop('outerHTML'); // Save HTML for potential rollback
  var taskCardParent = taskCard.parent();
  var taskCardIndex = taskCard.index();

  // Add fade out animation
  taskCard.css('opacity', '0.3');
  setTimeout(function() {
    taskCard.hide();
  }, 200);

  $.ajax({
    url: './ajax/ajax_eliminarTarea.php',
    type: 'POST',
    data: { id: taskIdToDelete },
    success: function(response) {
      console.log('📥 [AJAX] Delete task RAW RESPONSE:', response);
      console.log('📥 [AJAX] Response type:', typeof response);

      try {
        var parsedResponse = typeof response === 'string' ? JSON.parse(response) : response;
        console.log('✅ [AJAX] Delete task PARSED:', parsedResponse);

        if (parsedResponse.status === 'OK') {
          // Actually remove the task card from DOM
          taskCard.remove();
          console.log('   Task deleted successfully');
        } else {
          // Rollback: restore the task card
          console.error('❌ [ERROR] Delete failed, restoring task');
          showError('Error al eliminar tarea: ' + parsedResponse.mensaje);
          taskCard.css('opacity', '1').show();
        }
      } catch(e) {
        console.error('❌ [AJAX] Error parsing response:', e);
        // Rollback: restore the task card
        taskCard.css('opacity', '1').show();
        showError('Error al eliminar tarea');
      }
    },
    error: function(xhr, status, error) {
      console.error('❌ [AJAX] Delete task FAILED:', status, error);
      console.error('   XHR:', xhr);
      console.error('   Response Text:', xhr.responseText);

      // Rollback: restore the task card
      taskCard.css('opacity', '1').show();
      showError('Error de conexión al eliminar tarea');
    }
  });
}

async function copyTask() {
  if (!currentTask || !currentTask.id) return;

  console.log('📋 [COPY] Copying task:', currentTask.nombre, 'id:', currentTask.id);

  // Save task ID and data before closing modal
  var taskIdToCopy = currentTask.id;
  var taskData = JSON.parse(JSON.stringify(currentTask)); // Deep copy

  // Close task modal before showing prompt
  console.log('   Closing task modal before prompt');
  $('#taskModal').modal('hide');

  // Wait a bit for modal to close
  await new Promise(resolve => setTimeout(resolve, 300));

  // Show prompt for new task name
  var newName = await showPrompt('Ingrese el nombre para la nueva tarea:', taskData.nombre + ' (copia)', 'Copiar Tarea');

  if (!newName || !newName.trim()) {
    console.log('   User cancelled copy, reopening task modal');
    // Reopen the original task modal
    openTaskModal(taskIdToCopy);
    return;
  }

  console.log('   User entered new name:', newName, 'proceeding with copy...');

  // Prepare data for new task (copy all fields except ID and name)
  var data = {
    nombre: newName.trim(),
    descripcion: taskData.descripcion || '',
    estado: taskData.estado || 'Pendiente',
    id_kanban_columnas: taskData.id_kanban_columnas,
    checklist: JSON.stringify(taskData.checklist || []),
    links: JSON.stringify(taskData.links || []),
    usuarios: taskData.usuarios || [],
    etiquetas: taskData.etiquetas || []
  };

  if (taskData.fecha_inicio && taskData.fecha_inicio !== '0000-00-00') {
    data.fecha_inicio = taskData.fecha_inicio;
  }
  if (taskData.fecha_vencimiento && taskData.fecha_vencimiento !== '0000-00-00') {
    data.fecha_vencimiento = taskData.fecha_vencimiento;
  }

  console.log('   Data to save:', data);

  $.ajax({
    url: './ajax/ajax_guardarTarea.php',
    type: 'POST',
    data: data,
    dataType: 'json',
    success: function(response) {
      console.log('✅ [AJAX] Copy task SUCCESS:', response);

      if (response.status === 'OK') {
        console.log('   Task copied successfully with ID:', response.tarea.id);

        // Reload the board to show the new task
        loadTableros();
      } else {
        console.error('❌ [AJAX] Copy task returned error:', response.mensaje);
        showError('Error al copiar tarea: ' + response.mensaje);
      }
    },
    error: function(xhr, status, error) {
      console.error('❌ [AJAX] Copy task FAILED:', status, error);
      showError('Error de conexión al copiar tarea');
    }
  });
}

// ===========================
// TIMER FUNCTIONS
// ===========================

function startTimer() {
  if (!currentTask || !currentTask.id) return;
  if (timerInterval) return; // Already running

  console.log('⏱️ [TIMER] Starting timer');

  timerStartTime = Date.now();
  timerElapsedSeconds = 0;
  timerPausedSeconds = 0;
  timerIsPaused = false;

  // Show pause and reset buttons, hide others
  $('#btn-start-timer').hide();
  $('#btn-pause-timer').show();
  $('#btn-resume-timer').hide();
  $('#btn-reset-timer').show();

  // Update button to show it's running
  $('#btn-timer').html('<i class="bi bi-stopwatch-fill"></i> <span class="text-success">●</span> Timer');

  // Update display every second
  timerInterval = setInterval(function() {
    timerElapsedSeconds = Math.floor((Date.now() - timerStartTime) / 1000);
    updateTimerDisplay(timerElapsedSeconds);
  }, 1000);

  console.log('   Timer started');
}

function pauseTimer() {
  if (!currentTask || !currentTask.id) return;
  if (!timerInterval) return; // Not running

  console.log('⏱️ [TIMER] Pausing and saving timer');

  // Clear interval
  clearInterval(timerInterval);
  timerInterval = null;

  // Calculate elapsed seconds
  var elapsedSeconds = Math.floor((Date.now() - timerStartTime) / 1000);
  timerPausedSeconds = elapsedSeconds;

  console.log('   Timer paused at:', elapsedSeconds, 'seconds');

  // Save to database automatically
  if (elapsedSeconds > 0) {
    var currentTimeElapsed = parseInt(currentTask.time_elapsed) || 0;
    var newTimeElapsed = currentTimeElapsed + elapsedSeconds;

    console.log('   Auto-saving:', elapsedSeconds, 'seconds to total:', currentTimeElapsed);
    console.log('   New total:', newTimeElapsed, 'seconds');

    // Update task
    currentTask.time_elapsed = newTimeElapsed;

    // Save to server
    saveTimeElapsed(newTimeElapsed);
  }

  // Reset display and show resume button
  $('#timer-display').text('00:00:00');
  $('#btn-pause-timer').hide();
  $('#btn-resume-timer').show();
  $('#btn-reset-timer').show();

  // Reset variables
  timerStartTime = null;
  timerElapsedSeconds = 0;
  timerPausedSeconds = 0;
  timerIsPaused = false;

  // Update display
  updateTotalTimeDisplay();

  console.log('   Timer paused and saved');
}

function resumeTimer() {
  if (!currentTask || !currentTask.id) return;
  if (timerInterval) return; // Already running

  console.log('⏱️ [TIMER] Resuming timer (starting new session from 0)');

  // Start fresh timer (previous time was already saved on pause)
  timerStartTime = Date.now();
  timerElapsedSeconds = 0;
  timerPausedSeconds = 0;
  timerIsPaused = false;

  // Show pause button, hide others
  $('#btn-resume-timer').hide();
  $('#btn-pause-timer').show();
  $('#btn-reset-timer').show();

  // Update button to show it's running
  $('#btn-timer').html('<i class="bi bi-stopwatch-fill"></i> <span class="text-success">●</span> Timer');

  // Update display every second
  timerInterval = setInterval(function() {
    timerElapsedSeconds = Math.floor((Date.now() - timerStartTime) / 1000);
    updateTimerDisplay(timerElapsedSeconds);
  }, 1000);

  console.log('   Timer resumed');
}

function resetTimer() {
  if (!currentTask || !currentTask.id) return;

  console.log('⏱️ [TIMER] Resetting timer');

  // Stop interval if running
  if (timerInterval) {
    clearInterval(timerInterval);
    timerInterval = null;
  }

  // Reset all variables
  timerStartTime = null;
  timerElapsedSeconds = 0;
  timerPausedSeconds = 0;
  timerIsPaused = false;

  // Reset display
  $('#timer-display').text('00:00:00');
  $('#btn-pause-timer').hide();
  $('#btn-resume-timer').hide();
  $('#btn-stop-timer').hide();
  $('#btn-reset-timer').hide();
  $('#btn-start-timer').show();

  // Set time_elapsed to 0 and save
  currentTask.time_elapsed = 0;
  saveTimeElapsed(0);

  console.log('   Timer reset to 0');
}

function updateTimerDisplay(seconds) {
  var hours = Math.floor(seconds / 3600);
  var minutes = Math.floor((seconds % 3600) / 60);
  var secs = seconds % 60;

  var display =
    String(hours).padStart(2, '0') + ':' +
    String(minutes).padStart(2, '0') + ':' +
    String(secs).padStart(2, '0');

  $('#timer-display').text(display);

  // Update button to show current time
  $('#btn-timer').html('<i class="bi bi-stopwatch-fill"></i> <span class="text-success">●</span> ' + display);
}

function updateTotalTimeDisplay() {
  if (!currentTask) return;

  var totalSeconds = parseInt(currentTask.time_elapsed) || 0;
  var hours = Math.floor(totalSeconds / 3600);
  var minutes = Math.floor((totalSeconds % 3600) / 60);
  var seconds = totalSeconds % 60;

  var timeText = '';
  if (hours > 0) {
    timeText = hours + 'h ' + minutes + 'm';
  } else if (minutes > 0) {
    timeText = minutes + 'm ' + seconds + 's';
  } else {
    timeText = seconds + 's';
  }

  $('#total-time-display').text(timeText);

  // Update button label with total time (only if timer is not running)
  if (!timerInterval) {
    $('#btn-timer').html('<i class="bi bi-stopwatch"></i> Timer (' + timeText + ')');
  }
}

function saveTimeElapsed(timeElapsed) {
  if (!currentTask || !currentTask.id) return;

  console.log('💾 [TIMER] Saving time_elapsed:', timeElapsed);

  // Show saving indicator
  $('#saving-indicator').show();

  $.ajax({
    url: './ajax/ajax_guardarTarea.php',
    type: 'POST',
    data: {
      id: currentTask.id,
      time_elapsed: timeElapsed
    },
    dataType: 'json',
    success: function(response) {
      console.log('✅ [AJAX] Save time_elapsed SUCCESS:', response);

      if (response.status === 'OK') {
        console.log('   Time elapsed saved successfully');

        // Update current task with saved data
        currentTask = response.tarea;

        // Update display with saved time
        updateTotalTimeDisplay();

        // Update the task card on the board
        loadTableros();

        // Show brief success indicator
        $('#saving-indicator').html('<span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>Tiempo guardado</span>');
        setTimeout(function() {
          $('#saving-indicator').fadeOut(function() {
            $(this).html('<span class="spinner-border spinner-border-sm me-1"></span>Guardando...').show();
          });
        }, 2000);
      } else {
        console.error('❌ [ERROR] Save time_elapsed failed:', response.mensaje);
        $('#saving-indicator').html('<span class="text-danger"><i class="bi bi-exclamation-circle-fill me-1"></i>Error al guardar</span>');
        setTimeout(function() {
          $('#saving-indicator').fadeOut(function() {
            $(this).html('<span class="spinner-border spinner-border-sm me-1"></span>Guardando...').show();
          });
        }, 3000);
      }
    },
    error: function(xhr, status, error) {
      console.error('❌ [AJAX] Save time_elapsed FAILED:', status, error);
      $('#saving-indicator').html('<span class="text-danger"><i class="bi bi-exclamation-circle-fill me-1"></i>Error de conexión</span>');
      setTimeout(function() {
        $('#saving-indicator').fadeOut(function() {
          $(this).html('<span class="spinner-border spinner-border-sm me-1"></span>Guardando...').show();
        });
      }, 3000);
    }
  });
}

// ===========================
// USER MANAGEMENT
// ===========================

function populateUsersDropdown() {
  console.log('👥 [POPULATE] Populating users dropdown');

  var container = $('#users-list');
  container.empty();

  console.log('   All users:', allUsers);
  console.log('   Current task users:', currentTask.usuarios);

  if (allUsers.length === 0) {
    container.append('<p class="text-muted small mb-0">No hay usuarios disponibles</p>');
    return;
  }

  allUsers.forEach(function(user) {
    var isChecked = currentTask.usuarios && currentTask.usuarios.includes(parseInt(user.id));

    var div = $('<div>').addClass('form-check user-checkbox-item');

    var checkbox = $('<input>')
      .attr('type', 'checkbox')
      .attr('id', 'user-inline-' + user.id)
      .addClass('form-check-input')
      .val(user.id)
      .prop('checked', isChecked)
      .on('change', function() {
        var userId = parseInt($(this).val());
        var isChecked = $(this).is(':checked');

        console.log('☑️ [EVENT] User checkbox changed:', user.nombre, isChecked);

        if (!currentTask.usuarios) {
          currentTask.usuarios = [];
        }

        if (isChecked) {
          if (!currentTask.usuarios.includes(userId)) {
            currentTask.usuarios.push(userId);
          }
        } else {
          currentTask.usuarios = currentTask.usuarios.filter(id => id !== userId);
        }

        displayTaskUsers();
        triggerAutoSave();
      });

    var label = $('<label>')
      .addClass('form-check-label')
      .attr('for', 'user-inline-' + user.id)
      .text(user.nombre);

    div.append(checkbox, label);
    container.append(div);
  });
}

// ===========================
// LABEL MANAGEMENT
// ===========================

function populateLabelsDropdown() {
  console.log('🏷️ [POPULATE] Populating labels dropdown');

  var container = $('#labels-list-inline');
  container.empty();

  console.log('   All labels:', allLabels);
  console.log('   Current task labels:', currentTask.etiquetas);

  if (allLabels.length === 0) {
    container.append('<p class="text-muted small mb-0">No hay etiquetas. Crea una nueva abajo.</p>');
    return;
  }

  allLabels.forEach(function(label) {
    var isChecked = currentTask.etiquetas && currentTask.etiquetas.includes(parseInt(label.id));

    var div = $('<div>').addClass('form-check label-checkbox-item');

    var checkbox = $('<input>')
      .attr('type', 'checkbox')
      .attr('id', 'label-inline-' + label.id)
      .addClass('form-check-input')
      .val(label.id)
      .prop('checked', isChecked)
      .on('change', function() {
        var labelId = parseInt($(this).val());
        var isChecked = $(this).is(':checked');

        console.log('☑️ [EVENT] Label checkbox changed:', label.nombre, isChecked);

        if (!currentTask.etiquetas) {
          currentTask.etiquetas = [];
        }

        if (isChecked) {
          if (!currentTask.etiquetas.includes(labelId)) {
            currentTask.etiquetas.push(labelId);
          }
        } else {
          currentTask.etiquetas = currentTask.etiquetas.filter(id => id !== labelId);
        }

        displayTaskLabels();
        triggerAutoSave();
      });

    var labelSpan = $('<span>')
      .addClass('label-badge ms-2')
      .css('background-color', label.codigo_hex)
      .text(label.nombre);

    var labelEl = $('<label>')
      .addClass('form-check-label')
      .attr('for', 'label-inline-' + label.id)
      .append(labelSpan);

    div.append(checkbox, labelEl);
    container.append(div);
  });
}

function createLabelInline() {
  var nombre = $('#new-label-name-inline').val().trim();
  var color = $('#new-label-color-inline').val();

  console.log('🆕 [CREATE] Creating new label inline:', nombre, color);

  if (!nombre) {
    console.warn('⚠️ [CREATE] No label name provided');
    showAlert('Ingresa un nombre para la etiqueta', 'Error');
    return;
  }

  $.ajax({
    url: './ajax/ajax_guardarEtiqueta.php',
    type: 'POST',
    data: { nombre: nombre, codigo_hex: color },
    dataType: 'json',
    success: function(response) {
      console.log('✅ [AJAX] Create label SUCCESS:', response);

      if (response.status === 'OK') {
        allLabels.push(response.etiqueta);
        $('#new-label-name-inline').val('');
        $('#new-label-color-inline').val('#6A1693');
        populateLabelsDropdown();
      }
    },
    error: function(xhr, status, error) {
      console.error('❌ [AJAX] Create label FAILED:', status, error);
    }
  });
}

// ===========================
// DESCRIPTION PREVIEW
// ===========================

function linkifyText(text) {
  if (!text) return '';
  // Pattern to match URLs starting with http:// or https://
  var urlPattern = /(https?:\/\/[^\s<]+)/g;
  return text.replace(urlPattern, '<a href="$1" target="_blank" rel="noopener noreferrer" class="text-primary">$1</a>');
}

function updateDescriptionPreview() {
  var description = $('#task-description').val();
  var preview = $('#task-description-preview');

  if (description && description.trim()) {
    preview.html(linkifyText(description));
  } else {
    preview.empty();
  }
}

// ===========================
// DATES
// ===========================

function populateDatesDropdown() {
  console.log('📅 [POPULATE] Populating dates dropdown');
  console.log('   Start date:', currentTask.fecha_inicio);
  console.log('   Due date:', currentTask.fecha_vencimiento);

  $('#task-start-date-inline').val(currentTask.fecha_inicio || '');
  $('#task-due-date-inline').val(currentTask.fecha_vencimiento || '');
}

function updateTaskDatesInline() {
  var startDate = $('#task-start-date-inline').val() || null;
  var dueDate = $('#task-due-date-inline').val() || null;

  // Validate that due date is not before start date
  if (startDate && dueDate && new Date(dueDate) < new Date(startDate)) {
    console.warn('⚠️ [VALIDATION] Due date cannot be before start date');
    showError('La fecha de vencimiento no puede ser anterior a la fecha de inicio');
    // Reset the date that was just changed
    $('#task-start-date-inline').val(currentTask.fecha_inicio || '');
    $('#task-due-date-inline').val(currentTask.fecha_vencimiento || '');
    return;
  }

  currentTask.fecha_inicio = startDate;
  currentTask.fecha_vencimiento = dueDate;

  console.log('📅 [UPDATE] Updating task dates inline:');
  console.log('   Start:', currentTask.fecha_inicio);
  console.log('   Due:', currentTask.fecha_vencimiento);

  displayTaskDates();
  triggerAutoSave();
}

// ===========================
// LINKS
// ===========================

function saveLinkInline() {
  var title = $('#link-title-inline').val().trim();
  var url = $('#link-url-inline').val().trim();

  console.log('💾 [SAVE] Saving link inline, title:', title, 'url:', url);

  if (!url) {
    console.warn('⚠️ [SAVE] No URL provided');
    showAlert('Ingresa una URL', 'Error');
    return;
  }

  if (!currentTask.links) currentTask.links = [];

  currentTask.links.push({
    id: generateId(),
    title: title || url,
    url: url
  });

  console.log('   Total links now:', currentTask.links.length);

  // Clear form
  $('#link-title-inline').val('');
  $('#link-url-inline').val('');

  displayTaskLinks();
  triggerAutoSave();

  // Close dropdown
  $('#btn-add-link').dropdown('hide');
}

function deleteLink(linkIndex) {
  if (!currentTask.links) return;

  console.log('🗑️ [DELETE] Deleting link at index:', linkIndex);

  currentTask.links.splice(linkIndex, 1);
  displayTaskLinks();
  triggerAutoSave();
}

// ===========================
// CHECKLISTS
// ===========================

function createChecklistInline() {
  var title = $('#checklist-title-inline').val().trim();

  console.log('🆕 [CREATE] Creating checklist inline:', title);

  if (!title) {
    console.warn('⚠️ [CREATE] No checklist title provided');
    showAlert('Ingresa un nombre para el checklist', 'Error');
    return;
  }

  if (!currentTask.checklist) currentTask.checklist = [];

  currentTask.checklist.push({
    id: generateId(),
    title: title,
    items: []
  });

  console.log('   Total checklists now:', currentTask.checklist.length);

  // Clear form
  $('#checklist-title-inline').val('');

  displayTaskChecklists();
  triggerAutoSave();

  // Close dropdown
  $('#btn-add-checklist').dropdown('hide');
}

function updateChecklistItem(checklistIndex, itemIndex, field, value) {
  console.log('🔄 [UPDATE] Updating checklist item:', checklistIndex, itemIndex, field, '=', value);

  currentTask.checklist[checklistIndex].items[itemIndex][field] = value;
  displayTaskChecklists();
  triggerAutoSave();
}

function deleteChecklistItem(checklistIndex, itemIndex) {
  console.log('🗑️ [DELETE] Deleting checklist item:', checklistIndex, itemIndex);

  currentTask.checklist[checklistIndex].items.splice(itemIndex, 1);
  displayTaskChecklists();
  triggerAutoSave();
}

function deleteChecklist(checklistIndex) {
  console.log('🗑️ [DELETE] Deleting checklist at index:', checklistIndex);

  currentTask.checklist.splice(checklistIndex, 1);
  displayTaskChecklists();
  triggerAutoSave();
}

// ===========================
// FILTERS
// ===========================

function populateFilterSelects() {
  console.log('🔍 [FILTERS] Populating filter selects');

  // Populate users filter
  var userSelect = $('#filter-user');
  userSelect.find('option:not(:first)').remove();
  if (allUsers && allUsers.length > 0) {
    allUsers.forEach(function(user) {
      userSelect.append($('<option>').val(user.id).text(user.nombre));
    });
  }

  // Populate labels filter
  var labelSelect = $('#filter-label');
  labelSelect.find('option:not(:first)').remove();
  if (allLabels && allLabels.length > 0) {
    allLabels.forEach(function(label) {
      labelSelect.append($('<option>').val(label.id).text(label.nombre));
    });
  }
}

function applyFilters() {
  console.log('🔍 [FILTERS] Applying filters');

  var searchTerm = $('#filter-search').val().toLowerCase().trim();
  var filterUser = $('#filter-user').val();
  var filterLabel = $('#filter-label').val();
  var filterStatus = $('#filter-status').val();
  var filterDate = $('#filter-date').val();

  var activeFiltersCount = 0;
  if (searchTerm) activeFiltersCount++;
  if (filterUser) activeFiltersCount++;
  if (filterLabel) activeFiltersCount++;
  if (filterStatus) activeFiltersCount++;
  if (filterDate) activeFiltersCount++;

  // Update active filters count in badge
  if (activeFiltersCount > 0) {
    $('#filters-badge').text(activeFiltersCount).show();
  } else {
    $('#filters-badge').hide();
  }

  console.log('   Active filters:', activeFiltersCount);
  console.log('   Search:', searchTerm);
  console.log('   User:', filterUser);
  console.log('   Label:', filterLabel);
  console.log('   Status:', filterStatus);
  console.log('   Date:', filterDate);

  // Get today's date for date filters
  var today = new Date();
  today.setHours(0, 0, 0, 0);
  var endOfWeek = new Date(today);
  endOfWeek.setDate(today.getDate() + 7);

  // Filter all tasks
  $('.kanban-task').each(function() {
    var taskCard = $(this);

    // Skip new task cards
    if (taskCard.hasClass('new-task-card')) {
      return;
    }

    var taskId = taskCard.attr('data-task-id');
    var taskName = taskCard.find('.kanban-task-name').text().toLowerCase();
    var show = true;

    // Find task data from current tablero
    var taskData = null;
    if (window.currentTablero && window.currentTablero.columnas) {
      window.currentTablero.columnas.forEach(function(columna) {
        if (columna.tareas) {
          columna.tareas.forEach(function(tarea) {
            if (tarea.id == taskId) {
              taskData = tarea;
            }
          });
        }
      });
    }

    if (!taskData) {
      taskCard.hide();
      return;
    }

    // Filter by search term
    if (searchTerm && !taskName.includes(searchTerm)) {
      if (!taskData.descripcion || !taskData.descripcion.toLowerCase().includes(searchTerm)) {
        show = false;
      }
    }

    // Filter by user
    if (filterUser && show) {
      if (!taskData.usuarios || !taskData.usuarios.includes(parseInt(filterUser))) {
        show = false;
      }
    }

    // Filter by label
    if (filterLabel && show) {
      if (!taskData.etiquetas || !taskData.etiquetas.includes(parseInt(filterLabel))) {
        show = false;
      }
    }

    // Filter by status
    if (filterStatus && show) {
      if (taskData.estado !== filterStatus) {
        show = false;
      }
    }

    // Filter by date
    if (filterDate && show) {
      var dueDate = taskData.fecha_vencimiento;

      if (filterDate === 'sin-fecha') {
        if (dueDate && dueDate !== '0000-00-00') {
          show = false;
        }
      } else if (filterDate === 'vencidas') {
        if (!dueDate || dueDate === '0000-00-00') {
          show = false;
        } else {
          var taskDueDate = new Date(dueDate);
          if (taskDueDate >= today) {
            show = false;
          }
        }
      } else if (filterDate === 'hoy') {
        if (!dueDate || dueDate === '0000-00-00') {
          show = false;
        } else {
          var taskDueDate = new Date(dueDate);
          taskDueDate.setHours(0, 0, 0, 0);
          if (taskDueDate.getTime() !== today.getTime()) {
            show = false;
          }
        }
      } else if (filterDate === 'semana') {
        if (!dueDate || dueDate === '0000-00-00') {
          show = false;
        } else {
          var taskDueDate = new Date(dueDate);
          if (taskDueDate < today || taskDueDate > endOfWeek) {
            show = false;
          }
        }
      }
    }

    // Show or hide task
    if (show) {
      taskCard.show();
    } else {
      taskCard.hide();
    }
  });

  console.log('   Filters applied');
}

function clearFilters() {
  console.log('🔍 [FILTERS] Clearing all filters');

  $('#filter-search').val('');
  $('#filter-user').val('');
  $('#filter-label').val('');
  $('#filter-status').val('');
  $('#filter-date').val('');

  $('#filters-badge').hide();

  // Show all tasks
  $('.kanban-task:not(.new-task-card)').show();

  console.log('   Filters cleared');
}

// ===========================
// UTILITY FUNCTIONS
// ===========================

function formatDate(dateString) {
  if (!dateString || dateString === '0000-00-00') return '';
  var parts = dateString.split('-');
  if (parts.length !== 3) return dateString;
  return parts[2] + '/' + parts[1] + '/' + parts[0];
}

function autoResizeTextarea(element) {
  element.style.height = 'auto';
  element.style.height = element.scrollHeight + 'px';
}

function generateId() {
  return 'id-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
}

function showError(message) {
  console.error('⛔ [ERROR] Showing error to user:', message);
  showAlert(message, 'Error');
}

// ===========================
// TABLERO USERS MANAGEMENT
// ===========================

var tableroUsers = [];

function loadTableroUsers() {
  if (!currentTableroId) {
    console.warn('⚠️ [TABLERO USERS] No tablero selected');
    return;
  }

  console.log('📥 [AJAX] Loading tablero users for tablero:', currentTableroId);

  $.ajax({
    url: './ajax/ajax_getTableroUsuarios.php',
    type: 'GET',
    data: { id_tablero: currentTableroId },
    dataType: 'json',
    success: function(response) {
      console.log('✅ [AJAX] Get tablero users SUCCESS:', response);

      if (response.status === 'OK') {
        tableroUsers = response.usuarios || [];
        console.log('   Tablero users loaded:', tableroUsers.length);
        updateTableroUsersBadge();
      } else {
        console.error('❌ [AJAX] Get tablero users returned error:', response.mensaje);
      }
    },
    error: function(xhr, status, error) {
      console.error('❌ [AJAX] Get tablero users FAILED:', status, error);
    }
  });
}

function updateTableroUsersBadge() {
  var count = tableroUsers.length;
  $('#users-count-badge').text(count);
  console.log('🔄 [UI] Updated tablero users badge:', count);
}

function populateTableroUsersDropdown() {
  console.log('📝 [UI] Populating tablero users dropdown');

  var container = $('#tablero-users-list');
  container.empty();

  if (allUsers.length === 0) {
    container.html('<p class="text-muted small mb-0">No hay usuarios disponibles</p>');
    return;
  }

  // Get IDs of users assigned to tablero
  var assignedUserIds = tableroUsers.map(function(u) { return String(u.id); });

  allUsers.forEach(function(user) {
    var userId = String(user.id);
    var isAssigned = assignedUserIds.indexOf(userId) !== -1;

    var item = $('<div>')
      .addClass('user-checkbox-item form-check py-2 px-2 mb-1')
      .append(
        $('<input>')
          .addClass('form-check-input')
          .attr('type', 'checkbox')
          .attr('id', 'tablero-user-' + userId)
          .prop('checked', isAssigned)
          .on('change', function() {
            toggleTableroUser(userId, $(this).is(':checked'));
          })
      )
      .append(
        $('<label>')
          .addClass('form-check-label w-100')
          .attr('for', 'tablero-user-' + userId)
          .text((user.nombre || 'Usuario') + ' ' + (user.apellido || ''))
      );

    container.append(item);
  });

  console.log('   Populated', allUsers.length, 'users');
}

function toggleTableroUser(userId, assign) {
  console.log('🔄 [TABLERO USER] Toggle user:', userId, 'assign:', assign);

  if (!currentTableroId) {
    console.error('❌ [TABLERO USER] No tablero selected');
    return;
  }

  var action = assign ? 'agregar' : 'quitar';

  $.ajax({
    url: './ajax/ajax_toggleTableroUsuario.php',
    type: 'POST',
    data: {
      id_tablero: currentTableroId,
      id_usuario: userId,
      action: action
    },
    dataType: 'json',
    success: function(response) {
      console.log('✅ [AJAX] Toggle tablero user SUCCESS:', response);

      if (response.status === 'OK') {
        // Reload tablero users to update badge
        loadTableroUsers();
      } else {
        console.error('❌ [AJAX] Toggle tablero user returned error:', response.mensaje);
        showError('Error al ' + action + ' usuario: ' + response.mensaje);
        // Revert checkbox
        $('#tablero-user-' + userId).prop('checked', !assign);
      }
    },
    error: function(xhr, status, error) {
      console.error('❌ [AJAX] Toggle tablero user FAILED:', status, error);
      showError('Error de conexión al ' + action + ' usuario');
      // Revert checkbox
      $('#tablero-user-' + userId).prop('checked', !assign);
    }
  });
}

// ===========================
// MODAL UTILITIES
// ===========================

function showAlert(message, title) {
  return new Promise(function(resolve) {
    console.log('🔔 [ALERT] Showing alert:', message);

    $('#alertModalTitle').text(title || 'Aviso');
    $('#alertModalMessage').text(message);

    var modal = new bootstrap.Modal(document.getElementById('alertModal'));

    $('#alertModal').off('hidden.bs.modal').on('hidden.bs.modal', function() {
      resolve();
    });

    modal.show();
  });
}

function showConfirm(message, title) {
  return new Promise(function(resolve) {
    console.log('❓ [CONFIRM] Showing confirm:', message);

    $('#confirmModalTitle').text(title || 'Confirmar');
    $('#confirmModalMessage').text(message);

    var modal = new bootstrap.Modal(document.getElementById('confirmModal'));

    $('#confirmModalOk').off('click').on('click', function() {
      console.log('   User confirmed: YES');
      modal.hide();
      resolve(true);
    });

    $('#confirmModalCancel').off('click').on('click', function() {
      console.log('   User confirmed: NO');
      modal.hide();
      resolve(false);
    });

    $('#confirmModal').off('hidden.bs.modal').on('hidden.bs.modal', function() {
      resolve(false);
    });

    modal.show();
  });
}

function showPrompt(message, defaultValue, title) {
  return new Promise(function(resolve) {
    console.log('✏️ [PROMPT] Showing prompt:', message, 'default:', defaultValue);

    $('#promptModalTitle').text(title || 'Ingrese un valor');
    $('#promptModalMessage').text(message);
    $('#promptModalInput').val(defaultValue || '');

    var modal = new bootstrap.Modal(document.getElementById('promptModal'));

    // Focus input when modal is shown
    $('#promptModal').off('shown.bs.modal').on('shown.bs.modal', function() {
      $('#promptModalInput').focus().select();
    });

    // Handle Enter key
    $('#promptModalInput').off('keypress').on('keypress', function(e) {
      if (e.which === 13) {
        e.preventDefault();
        $('#promptModalOk').click();
      }
    });

    $('#promptModalOk').off('click').on('click', function() {
      var value = $('#promptModalInput').val();
      console.log('   User entered:', value);
      modal.hide();
      resolve(value);
    });

    $('#promptModalCancel').off('click').on('click', function() {
      console.log('   User cancelled prompt');
      modal.hide();
      resolve(null);
    });

    $('#promptModal').off('hidden.bs.modal').on('hidden.bs.modal', function() {
      resolve(null);
    });

    modal.show();
  });
}

console.log('✅ [INIT] All functions loaded');
