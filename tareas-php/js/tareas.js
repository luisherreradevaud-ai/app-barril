// Global variables
var allTasks = [];
var allUsers = [];
var allLabels = [];
var currentTask = null;
var saveTimeout = null;

// Initialize on document ready
$(document).ready(function() {
  if (typeof entityId !== 'undefined' && entityId) {
    loadTasks();
  }

  setupEventHandlers();
});

// Setup all event handlers
function setupEventHandlers() {
  // Add task button
  $('#btn-add-task').on('click', function() {
    $('#new-task-form').slideDown();
    $('#new-task-name').focus();
    $(this).hide();
  });

  // Save new task
  $('#btn-save-new-task').on('click', saveNewTask);

  // Cancel new task
  $('#btn-cancel-new-task').on('click', function() {
    $('#new-task-form').slideUp();
    $('#new-task-name').val('');
    $('#btn-add-task').show();
  });

  // Enter key on new task name
  $('#new-task-name').on('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      saveNewTask();
    }
  });

  // Modal event handlers
  $('#taskModal').on('hidden.bs.modal', function() {
    currentTask = null;
    clearTaskForm();
  });

  // Task status checkbox
  $('#task-status-checkbox').on('change', function() {
    if (currentTask) {
      var newStatus = $(this).is(':checked') ? 'Completada' : 'Pendiente';
      updateTaskField('estado', newStatus);
    }
  });

  // Auto-save on field changes
  $('#task-name, #task-description').on('input', function() {
    triggerAutoSave();
  });

  // Auto-resize textareas
  $('#task-name, #task-description').on('input', function() {
    autoResizeTextarea(this);
  });

  // Action buttons
  $('#btn-assign-users').on('click', openUserAssignmentModal);
  $('#btn-add-labels').on('click', openLabelManagementModal);
  $('#btn-add-checklist').on('click', openChecklistModal);
  $('#btn-add-dates').on('click', openDatesModal);
  $('#btn-add-link').on('click', openLinkModal);
  $('#btn-delete-task').on('click', deleteTask);

  // User assignment
  $('#user-assignment-select').on('change', function() {
    updateTaskUsers();
  });

  // Label management
  $('#btn-create-label').on('click', createLabel);

  // Dates
  $('#task-start-date, #task-due-date, #task-reminder').on('change', function() {
    updateTaskDates();
  });

  // Link management
  $('#btn-save-link').on('click', saveLink);

  // Checklist
  $('#btn-create-checklist').on('click', createChecklist);
}

// Load tasks from server
function loadTasks() {
  $('#loading-spinner').show();

  $.ajax({
    url: './ajax/ajax_getTareas.php',
    type: 'GET',
    data: { id_entidad: entityId },
    dataType: 'json',
    success: function(response) {
      if (response.status === 'OK') {
        allTasks = response.tareas || [];
        allUsers = response.usuarios || [];
        allLabels = response.etiquetas || [];
        renderTasks();
        $('#btn-add-task').show();
      } else {
        showError('Error al cargar tareas: ' + response.mensaje);
      }
    },
    error: function() {
      showError('Error de conexión al cargar tareas');
    },
    complete: function() {
      $('#loading-spinner').hide();
    }
  });
}

// Render all tasks
function renderTasks() {
  var container = $('#tasks-container');
  container.empty();

  if (allTasks.length === 0) {
    container.html('<p class="text-muted text-center">No hay tareas aún</p>');
    return;
  }

  allTasks.forEach(function(task) {
    var taskCard = createTaskCard(task);
    container.append(taskCard);
  });
}

// Create task card HTML
function createTaskCard(task) {
  var isCompleted = task.estado === 'Completada';
  var isOverdue = task.vencida;

  var card = $('<div>').addClass('task-card mb-2').attr('data-task-id', task.id);

  // Header with checkbox and name
  var header = $('<div>').addClass('d-flex align-items-start gap-2 mb-2');

  var checkbox = $('<input>')
    .attr('type', 'checkbox')
    .addClass('form-check-input mt-1')
    .prop('checked', isCompleted);

  checkbox.on('click', function(e) {
    e.stopPropagation();
    toggleTaskStatus(task.id, $(this).is(':checked'));
  });

  var name = $('<h6>')
    .addClass('flex-grow-1 mb-0')
    .toggleClass('text-muted', isCompleted)
    .text(task.nombre);

  header.append(checkbox, name);
  card.append(header);

  // Metadata
  var metadata = $('<div>').addClass('d-flex flex-wrap align-items-center gap-3 small text-muted');

  // Users
  if (task.usuarios && task.usuarios.length > 0) {
    var usersDiv = $('<div>').addClass('d-flex');
    task.usuarios.slice(0, 3).forEach(function(userId) {
      var user = allUsers.find(u => u.id == userId);
      if (user) {
        var avatar = $('<div>')
          .addClass('user-avatar')
          .text(user.nombre.charAt(0).toUpperCase())
          .attr('title', user.nombre);
        usersDiv.append(avatar);
      }
    });
    if (task.usuarios.length > 3) {
      var more = $('<div>')
        .addClass('user-avatar')
        .css('background-color', '#6c757d')
        .text('+' + (task.usuarios.length - 3));
      usersDiv.append(more);
    }
    metadata.append(usersDiv);
  }

  // Due date
  if (task.fecha_vencimiento && task.fecha_vencimiento !== '0000-00-00') {
    var dateSpan = $('<span>')
      .addClass('d-flex align-items-center gap-1')
      .toggleClass('text-danger', isOverdue);

    dateSpan.append($('<i>').addClass('bi bi-clock'));
    dateSpan.append(formatDate(task.fecha_vencimiento));
    metadata.append(dateSpan);
  }

  // File count
  if (task.cantidad_archivos > 0) {
    var filesSpan = $('<span>').addClass('d-flex align-items-center gap-1');
    filesSpan.append($('<i>').addClass('bi bi-paperclip'));
    filesSpan.append(task.cantidad_archivos);
    metadata.append(filesSpan);
  }

  // Checklist progress
  if (task.progreso_checklist && task.progreso_checklist.total > 0) {
    var checklistSpan = $('<span>').addClass('d-flex align-items-center gap-1');
    checklistSpan.append($('<i>').addClass('bi bi-check-square'));
    checklistSpan.append(task.progreso_checklist.completados + '/' + task.progreso_checklist.total);
    metadata.append(checklistSpan);
  }

  card.append(metadata);

  // Click handler to open modal
  card.on('click', function() {
    openTaskModal(task.id);
  });

  return card;
}

// Save new task
function saveNewTask() {
  var nombre = $('#new-task-name').val().trim();

  if (!nombre) {
    return;
  }

  $.ajax({
    url: './ajax/ajax_guardarTarea.php',
    type: 'POST',
    data: {
      nombre: nombre,
      id_entidad: entityId
    },
    dataType: 'json',
    success: function(response) {
      if (response.status === 'OK') {
        $('#new-task-form').slideUp();
        $('#new-task-name').val('');
        $('#btn-add-task').show();
        loadTasks();
      } else {
        showError('Error al crear tarea: ' + response.mensaje);
      }
    },
    error: function() {
      showError('Error de conexión al crear tarea');
    }
  });
}

// Toggle task status
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
        loadTasks();
      } else {
        showError('Error al actualizar estado: ' + response.mensaje);
      }
    },
    error: function() {
      showError('Error de conexión al actualizar estado');
    }
  });
}

// Open task modal
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
        allUsers = response.usuarios || [];
        allLabels = response.etiquetas || [];
        populateTaskModal();
        $('#taskModal').modal('show');
      } else {
        showError('Error al cargar tarea: ' + response.mensaje);
      }
    },
    error: function() {
      showError('Error de conexión al cargar tarea');
    },
    complete: function() {
      $('#loading-spinner').hide();
    }
  });
}

// Populate task modal with data
function populateTaskModal() {
  if (!currentTask) return;

  $('#task-name').val(currentTask.nombre);
  $('#task-description').val(currentTask.descripcion || '');
  $('#task-status-checkbox').prop('checked', currentTask.estado === 'Completada');

  // Auto-resize textareas
  autoResizeTextarea($('#task-name')[0]);
  autoResizeTextarea($('#task-description')[0]);

  // Display users
  displayTaskUsers();

  // Display labels
  displayTaskLabels();

  // Display dates
  displayTaskDates();

  // Display checklists
  displayTaskChecklists();

  // Display links
  displayTaskLinks();
}

// Clear task form
function clearTaskForm() {
  $('#task-name').val('');
  $('#task-description').val('');
  $('#task-status-checkbox').prop('checked', false);
  $('#task-users-display').empty();
  $('#task-labels-display').empty();
  $('#task-dates-display').empty();
  $('#task-checklists-container').empty();
  $('#task-links-container').empty();
}

// Display task users
function displayTaskUsers() {
  var container = $('#task-users-display');
  container.empty();

  if (!currentTask.usuarios || currentTask.usuarios.length === 0) {
    return;
  }

  var usersDiv = $('<div>').addClass('d-flex align-items-center gap-2 mb-2');
  usersDiv.append($('<small>').addClass('text-muted').text('Asignado a:'));

  var avatarsDiv = $('<div>').addClass('d-flex');
  currentTask.usuarios.forEach(function(userId) {
    var user = allUsers.find(u => u.id == userId);
    if (user) {
      var avatar = $('<div>')
        .addClass('user-avatar')
        .css('margin-left', '0')
        .css('margin-right', '0.25rem')
        .text(user.nombre.charAt(0).toUpperCase())
        .attr('title', user.nombre);
      avatarsDiv.append(avatar);
    }
  });

  usersDiv.append(avatarsDiv);
  container.append(usersDiv);
}

// Display task labels
function displayTaskLabels() {
  var container = $('#task-labels-display');
  container.empty();

  if (!currentTask.etiquetas || currentTask.etiquetas.length === 0) {
    return;
  }

  var labelsDiv = $('<div>').addClass('mb-2');
  currentTask.etiquetas.forEach(function(labelId) {
    var label = allLabels.find(l => l.id == labelId);
    if (label) {
      var badge = $('<span>')
        .addClass('label-badge')
        .css('background-color', label.codigo_hex)
        .text(label.nombre);
      labelsDiv.append(badge);
    }
  });

  container.append(labelsDiv);
}

// Display task dates
function displayTaskDates() {
  var container = $('#task-dates-display');
  container.empty();

  if ((!currentTask.fecha_inicio || currentTask.fecha_inicio === '0000-00-00') &&
      (!currentTask.fecha_vencimiento || currentTask.fecha_vencimiento === '0000-00-00')) {
    return;
  }

  var datesDiv = $('<div>').addClass('small text-muted mb-2');

  if (currentTask.fecha_inicio && currentTask.fecha_inicio !== '0000-00-00') {
    datesDiv.append($('<div>').text('Inicio: ' + formatDate(currentTask.fecha_inicio)));
  }

  if (currentTask.fecha_vencimiento && currentTask.fecha_vencimiento !== '0000-00-00') {
    datesDiv.append($('<div>').text('Vencimiento: ' + formatDate(currentTask.fecha_vencimiento)));
  }

  container.append(datesDiv);
}

// Display task checklists
function displayTaskChecklists() {
  var container = $('#task-checklists-container');
  container.empty();

  if (!currentTask.checklist || !Array.isArray(currentTask.checklist) || currentTask.checklist.length === 0) {
    return;
  }

  currentTask.checklist.forEach(function(checklist, checklistIndex) {
    var checklistDiv = $('<div>').addClass('card mb-3');
    var cardBody = $('<div>').addClass('card-body');

    // Header
    var header = $('<div>').addClass('d-flex justify-content-between align-items-center mb-2');
    header.append($('<h6>').addClass('mb-0').text(checklist.title));

    var deleteBtn = $('<button>')
      .addClass('btn btn-sm btn-link text-danger')
      .html('<i class="bi bi-trash"></i>')
      .on('click', function() {
        deleteChecklist(checklistIndex);
      });
    header.append(deleteBtn);

    cardBody.append(header);

    // Progress
    var total = checklist.items.length;
    var completed = checklist.items.filter(item => item.completed).length;
    var percentage = total > 0 ? Math.round((completed / total) * 100) : 0;

    var progressDiv = $('<div>').addClass('mb-2');
    progressDiv.append($('<small>').addClass('text-muted').text(percentage + '%'));

    var progressBar = $('<div>').addClass('progress-bar-custom');
    var progressFill = $('<div>').addClass('progress-fill').css('width', percentage + '%');
    progressBar.append(progressFill);
    progressDiv.append(progressBar);

    cardBody.append(progressDiv);

    // Items
    if (checklist.items && Array.isArray(checklist.items)) {
      checklist.items.forEach(function(item, itemIndex) {
        var itemDiv = $('<div>').addClass('checklist-item');

        var checkbox = $('<input>')
          .attr('type', 'checkbox')
          .addClass('form-check-input')
          .prop('checked', item.completed)
          .on('change', function() {
            updateChecklistItem(checklistIndex, itemIndex, 'completed', $(this).is(':checked'));
          });

        var text = $('<span>').addClass('flex-grow-1').text(item.text);

        var deleteItemBtn = $('<button>')
          .addClass('btn btn-sm btn-link text-danger')
          .html('<i class="bi bi-x"></i>')
          .on('click', function() {
            deleteChecklistItem(checklistIndex, itemIndex);
          });

        itemDiv.append(checkbox, text, deleteItemBtn);
        cardBody.append(itemDiv);
      });
    }

    // Add item button
    var addItemBtn = $('<button>')
      .addClass('btn btn-sm btn-link')
      .text('+ Agregar Item')
      .on('click', function() {
        addChecklistItem(checklistIndex);
      });
    cardBody.append(addItemBtn);

    checklistDiv.append(cardBody);
    container.append(checklistDiv);
  });
}

// Display task links
function displayTaskLinks() {
  var container = $('#task-links-container');
  container.empty();

  if (!currentTask.links || !Array.isArray(currentTask.links) || currentTask.links.length === 0) {
    return;
  }

  var linksDiv = $('<div>').addClass('mb-2');

  currentTask.links.forEach(function(link, linkIndex) {
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
        deleteLink(linkIndex);
      });

    linkDiv.append(anchor, deleteBtn);
    linksDiv.append(linkDiv);
  });

  container.append(linksDiv);
}

// Trigger auto-save with debounce
function triggerAutoSave() {
  if (!currentTask || !currentTask.id) return;

  if (saveTimeout) {
    clearTimeout(saveTimeout);
  }

  saveTimeout = setTimeout(function() {
    autoSaveTask();
  }, 1000);
}

// Auto-save task
function autoSaveTask() {
  if (!currentTask || !currentTask.id) return;

  $('#saving-indicator').show();

  var data = {
    id: currentTask.id,
    nombre: $('#task-name').val(),
    descripcion: $('#task-description').val(),
    estado: $('#task-status-checkbox').is(':checked') ? 'Completada' : 'Pendiente',
    checklist: JSON.stringify(currentTask.checklist || []),
    links: JSON.stringify(currentTask.links || []),
    usuarios: currentTask.usuarios || [],
    etiquetas: currentTask.etiquetas || []
  };

  if (currentTask.fecha_inicio) {
    data.fecha_inicio = currentTask.fecha_inicio;
  }
  if (currentTask.fecha_vencimiento) {
    data.fecha_vencimiento = currentTask.fecha_vencimiento;
  }
  if (currentTask.recordatorio_vencimiento) {
    data.recordatorio_vencimiento = currentTask.recordatorio_vencimiento;
  }

  $.ajax({
    url: './ajax/ajax_guardarTarea.php',
    type: 'POST',
    data: data,
    dataType: 'json',
    success: function(response) {
      if (response.status === 'OK') {
        currentTask = response.tarea;
        loadTasks();
      } else {
        showError('Error al guardar: ' + response.mensaje);
      }
    },
    error: function() {
      showError('Error de conexión al guardar');
    },
    complete: function() {
      $('#saving-indicator').hide();
    }
  });
}

// Update single task field
function updateTaskField(field, value) {
  if (!currentTask) return;
  currentTask[field] = value;
  triggerAutoSave();
}

// Open user assignment modal
function openUserAssignmentModal() {
  var select = $('#user-assignment-select');
  select.empty();

  allUsers.forEach(function(user) {
    var option = $('<option>')
      .val(user.id)
      .text(user.nombre)
      .prop('selected', currentTask.usuarios && currentTask.usuarios.includes(parseInt(user.id)));
    select.append(option);
  });

  $('#userAssignmentModal').modal('show');
}

// Update task users
function updateTaskUsers() {
  var selectedUsers = $('#user-assignment-select').val() || [];
  currentTask.usuarios = selectedUsers.map(id => parseInt(id));
  displayTaskUsers();
  triggerAutoSave();
}

// Open label management modal
function openLabelManagementModal() {
  var container = $('#labels-list');
  container.empty();

  allLabels.forEach(function(label) {
    var div = $('<div>').addClass('form-check mb-2');

    var checkbox = $('<input>')
      .attr('type', 'checkbox')
      .attr('id', 'label-' + label.id)
      .addClass('form-check-input')
      .val(label.id)
      .prop('checked', currentTask.etiquetas && currentTask.etiquetas.includes(parseInt(label.id)))
      .on('change', function() {
        updateTaskLabels();
      });

    var labelSpan = $('<span>')
      .addClass('label-badge ms-2')
      .css('background-color', label.codigo_hex)
      .text(label.nombre);

    var labelEl = $('<label>')
      .addClass('form-check-label')
      .attr('for', 'label-' + label.id)
      .append(labelSpan);

    div.append(checkbox, labelEl);
    container.append(div);
  });

  $('#labelManagementModal').modal('show');
}

// Update task labels
function updateTaskLabels() {
  var selectedLabels = [];
  $('#labels-list input:checked').each(function() {
    selectedLabels.push(parseInt($(this).val()));
  });
  currentTask.etiquetas = selectedLabels;
  displayTaskLabels();
  triggerAutoSave();
}

// Create new label
function createLabel() {
  var nombre = $('#new-label-name').val().trim();
  var color = $('#new-label-color').val();

  if (!nombre) {
    return;
  }

  $.ajax({
    url: './ajax/ajax_guardarEtiqueta.php',
    type: 'POST',
    data: {
      nombre: nombre,
      codigo_hex: color
    },
    dataType: 'json',
    success: function(response) {
      if (response.status === 'OK') {
        allLabels.push(response.etiqueta);
        $('#new-label-name').val('');
        $('#new-label-color').val('#6A1693');
        openLabelManagementModal(); // Refresh the modal
      } else {
        showError('Error al crear etiqueta: ' + response.mensaje);
      }
    },
    error: function() {
      showError('Error de conexión al crear etiqueta');
    }
  });
}

// Open dates modal
function openDatesModal() {
  $('#task-start-date').val(currentTask.fecha_inicio || '');
  $('#task-due-date').val(currentTask.fecha_vencimiento || '');
  $('#task-reminder').val(currentTask.recordatorio_vencimiento || '');

  $('#datesModal').modal('show');
}

// Update task dates
function updateTaskDates() {
  currentTask.fecha_inicio = $('#task-start-date').val() || null;
  currentTask.fecha_vencimiento = $('#task-due-date').val() || null;
  currentTask.recordatorio_vencimiento = $('#task-reminder').val() || null;
  displayTaskDates();
  triggerAutoSave();
}

// Open link modal
function openLinkModal() {
  $('#link-title').val('');
  $('#link-url').val('');
  $('#linkModal').modal('show');
}

// Save link
function saveLink() {
  var title = $('#link-title').val().trim();
  var url = $('#link-url').val().trim();

  if (!url) {
    return;
  }

  if (!currentTask.links) {
    currentTask.links = [];
  }

  currentTask.links.push({
    id: generateId(),
    title: title || url,
    url: url
  });

  displayTaskLinks();
  triggerAutoSave();
  $('#linkModal').modal('hide');
}

// Delete link
function deleteLink(linkIndex) {
  if (!currentTask.links) return;
  currentTask.links.splice(linkIndex, 1);
  displayTaskLinks();
  triggerAutoSave();
}

// Open checklist modal
function openChecklistModal() {
  $('#checklist-title').val('');
  $('#checklistModal').modal('show');
}

// Create checklist
function createChecklist() {
  var title = $('#checklist-title').val().trim();

  if (!title) {
    return;
  }

  if (!currentTask.checklist) {
    currentTask.checklist = [];
  }

  currentTask.checklist.push({
    id: generateId(),
    title: title,
    items: []
  });

  displayTaskChecklists();
  triggerAutoSave();
  $('#checklistModal').modal('hide');
}

// Add checklist item
function addChecklistItem(checklistIndex) {
  var text = prompt('Texto del item:');
  if (!text) return;

  if (!currentTask.checklist[checklistIndex].items) {
    currentTask.checklist[checklistIndex].items = [];
  }

  currentTask.checklist[checklistIndex].items.push({
    id: generateId(),
    text: text,
    completed: false
  });

  displayTaskChecklists();
  triggerAutoSave();
}

// Update checklist item
function updateChecklistItem(checklistIndex, itemIndex, field, value) {
  currentTask.checklist[checklistIndex].items[itemIndex][field] = value;
  displayTaskChecklists();
  triggerAutoSave();
}

// Delete checklist item
function deleteChecklistItem(checklistIndex, itemIndex) {
  currentTask.checklist[checklistIndex].items.splice(itemIndex, 1);
  displayTaskChecklists();
  triggerAutoSave();
}

// Delete checklist
function deleteChecklist(checklistIndex) {
  if (confirm('¿Eliminar este checklist?')) {
    currentTask.checklist.splice(checklistIndex, 1);
    displayTaskChecklists();
    triggerAutoSave();
  }
}

// Delete task
function deleteTask() {
  if (!currentTask || !currentTask.id) return;

  if (!confirm('¿Está seguro de eliminar esta tarea?')) {
    return;
  }

  $.ajax({
    url: './ajax/ajax_eliminarTarea.php',
    type: 'POST',
    data: { id: currentTask.id },
    dataType: 'json',
    success: function(response) {
      if (response.status === 'OK') {
        $('#taskModal').modal('hide');
        loadTasks();
      } else {
        showError('Error al eliminar tarea: ' + response.mensaje);
      }
    },
    error: function() {
      showError('Error de conexión al eliminar tarea');
    }
  });
}

// Utility functions
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
  alert(message); // Can be replaced with a nicer notification system
}
