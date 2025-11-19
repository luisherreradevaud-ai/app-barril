// ===========================
// TASK MODAL FUNCTIONS
// ===========================

function populateTaskModal() {
  if (!currentTask) return;

  $('#task-name').val(currentTask.nombre);
  $('#task-description').val(currentTask.descripcion || '');
  $('#task-status-checkbox').prop('checked', currentTask.estado === 'Completada');

  autoResizeTextarea($('#task-name')[0]);
  autoResizeTextarea($('#task-description')[0]);

  displayTaskUsers();
  displayTaskLabels();
  displayTaskDates();
  displayTaskChecklists();
  displayTaskLinks();
}

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

function displayTaskUsers() {
  var container = $('#task-users-display');
  container.empty();

  if (!currentTask.usuarios || currentTask.usuarios.length === 0) return;

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
  var container = $('#task-labels-display');
  container.empty();

  if (!currentTask.etiquetas || currentTask.etiquetas.length === 0) return;

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
    var total = checklist.items ? checklist.items.length : 0;
    var completed = checklist.items ? checklist.items.filter(item => item.completed).length : 0;
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

// ===========================
// AUTO-SAVE
// ===========================

function triggerAutoSave() {
  if (!currentTask || !currentTask.id) return;

  if (saveTimeout) {
    clearTimeout(saveTimeout);
  }

  saveTimeout = setTimeout(function() {
    autoSaveTask();
  }, 1000);
}

function autoSaveTask() {
  if (!currentTask || !currentTask.id) return;

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
  if (currentTask.recordatorio_vencimiento) data.recordatorio_vencimiento = currentTask.recordatorio_vencimiento;

  $.ajax({
    url: './ajax/ajax_guardarTarea.php',
    type: 'POST',
    data: data,
    dataType: 'json',
    success: function(response) {
      if (response.status === 'OK') {
        currentTask = response.tarea;
        loadTableros();
      }
    },
    complete: function() {
      $('#saving-indicator').hide();
    }
  });
}

function updateTaskField(field, value) {
  if (!currentTask) return;
  currentTask[field] = value;
  triggerAutoSave();
}

function deleteTask() {
  if (!currentTask || !currentTask.id) return;

  if (!confirm('¿Está seguro de eliminar esta tarea?')) return;

  $.ajax({
    url: './ajax/ajax_eliminarTarea.php',
    type: 'POST',
    data: { id: currentTask.id },
    dataType: 'json',
    success: function(response) {
      if (response.status === 'OK') {
        $('#taskModal').modal('hide');
        loadTableros();
      }
    }
  });
}

// ===========================
// USER MANAGEMENT
// ===========================

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

function updateTaskUsers() {
  var selectedUsers = $('#user-assignment-select').val() || [];
  currentTask.usuarios = selectedUsers.map(id => parseInt(id));
  displayTaskUsers();
  triggerAutoSave();
}

// ===========================
// LABEL MANAGEMENT
// ===========================

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

function updateTaskLabels() {
  var selectedLabels = [];
  $('#labels-list input:checked').each(function() {
    selectedLabels.push(parseInt($(this).val()));
  });
  currentTask.etiquetas = selectedLabels;
  displayTaskLabels();
  triggerAutoSave();
}

function createLabel() {
  var nombre = $('#new-label-name').val().trim();
  var color = $('#new-label-color').val();

  if (!nombre) return;

  $.ajax({
    url: './ajax/ajax_guardarEtiqueta.php',
    type: 'POST',
    data: { nombre: nombre, codigo_hex: color },
    dataType: 'json',
    success: function(response) {
      if (response.status === 'OK') {
        allLabels.push(response.etiqueta);
        $('#new-label-name').val('');
        $('#new-label-color').val('#6A1693');
        openLabelManagementModal();
      }
    }
  });
}

// ===========================
// DATES
// ===========================

function openDatesModal() {
  $('#task-start-date').val(currentTask.fecha_inicio || '');
  $('#task-due-date').val(currentTask.fecha_vencimiento || '');
  $('#task-reminder').val(currentTask.recordatorio_vencimiento || '');
  $('#datesModal').modal('show');
}

function updateTaskDates() {
  currentTask.fecha_inicio = $('#task-start-date').val() || null;
  currentTask.fecha_vencimiento = $('#task-due-date').val() || null;
  currentTask.recordatorio_vencimiento = $('#task-reminder').val() || null;
  displayTaskDates();
  triggerAutoSave();
}

// ===========================
// LINKS
// ===========================

function openLinkModal() {
  $('#link-title').val('');
  $('#link-url').val('');
  $('#linkModal').modal('show');
}

function saveLink() {
  var title = $('#link-title').val().trim();
  var url = $('#link-url').val().trim();

  if (!url) return;

  if (!currentTask.links) currentTask.links = [];

  currentTask.links.push({
    id: generateId(),
    title: title || url,
    url: url
  });

  displayTaskLinks();
  triggerAutoSave();
  $('#linkModal').modal('hide');
}

function deleteLink(linkIndex) {
  if (!currentTask.links) return;
  currentTask.links.splice(linkIndex, 1);
  displayTaskLinks();
  triggerAutoSave();
}

// ===========================
// CHECKLISTS
// ===========================

function openChecklistModal() {
  $('#checklist-title').val('');
  $('#checklistModal').modal('show');
}

function createChecklist() {
  var title = $('#checklist-title').val().trim();
  if (!title) return;

  if (!currentTask.checklist) currentTask.checklist = [];

  currentTask.checklist.push({
    id: generateId(),
    title: title,
    items: []
  });

  displayTaskChecklists();
  triggerAutoSave();
  $('#checklistModal').modal('hide');
}

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

function updateChecklistItem(checklistIndex, itemIndex, field, value) {
  currentTask.checklist[checklistIndex].items[itemIndex][field] = value;
  displayTaskChecklists();
  triggerAutoSave();
}

function deleteChecklistItem(checklistIndex, itemIndex) {
  currentTask.checklist[checklistIndex].items.splice(itemIndex, 1);
  displayTaskChecklists();
  triggerAutoSave();
}

function deleteChecklist(checklistIndex) {
  if (confirm('¿Eliminar este checklist?')) {
    currentTask.checklist.splice(checklistIndex, 1);
    displayTaskChecklists();
    triggerAutoSave();
  }
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
  alert(message);
}
