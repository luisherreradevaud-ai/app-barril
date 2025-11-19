/**
 * Tablero Kanban - Main JavaScript Module
 * Optimized version with event delegation
 */

(function() {
  'use strict';

  // ===========================
  // GLOBAL VARIABLES
  // ===========================

  window.TableroKanban = window.TableroKanban || {};

  var config = window.TableroKanban.config || {};
  var tableroId = config.tableroId || '';
  var entityId = config.entityId || '';
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

  // Cached selectors
  var $document = $(document);
  var $kanbanBoard, $filtersContainer, $taskModal, $columnModal;

  console.log('🎯 [INIT] Tablero Kanban module loaded', { tableroId, entityId });

  // ===========================
  // INITIALIZATION
  // ===========================

  $(document).ready(function() {
    console.log('📄 [READY] Document ready');

    // Cache frequently used selectors
    $kanbanBoard = $('#kanban-board');
    $filtersContainer = $('#filters-container');
    $taskModal = $('#taskModal');
    $columnModal = $('#columnModal');

    // Load data
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
  // EVENT HANDLERS (DELEGATED)
  // ===========================

  function setupEventHandlers() {
    console.log('🎮 [HANDLERS] Setting up delegated event handlers');

    // Single delegated handler for all clickable elements
    $document.on('click', handleDocumentClick);

    // Specific delegated handlers for common actions
    $kanbanBoard.on('click', '.task-card', handleTaskCardClick);
    $kanbanBoard.on('click', '.column-header', handleColumnHeaderClick);
    $kanbanBoard.on('click', '.btn-add-task', handleAddTaskClick);

    // Form inputs with delegation
    $document.on('input', 'textarea[data-auto-resize]', function() {
      autoResizeTextarea(this);
    });

    $document.on('change', '#task-status-checkbox', handleTaskStatusChange);
    $document.on('input', '#task-name', handleTaskNameInput);
    $document.on('input', '#task-description', handleTaskDescriptionInput);

    // Modal events
    $taskModal.on('hidden.bs.modal', handleTaskModalClose);
    $columnModal.on('hidden.bs.modal', handleColumnModalClose);

    // Bootstrap dropdown events
    $document.on('show.bs.dropdown', handleDropdownShow);

    // Filters
    $('#btn-toggle-filters').on('click', toggleFilters);
    $filtersContainer.on('click', function(e) {
      e.stopPropagation();
    });

    // Buttons (specific, non-delegatable)
    $('#btn-agregar-columna').on('click', openNewColumnModal);
    $('#btn-save-column').on('click', saveColumn);
    $('#btn-delete-column').on('click', deleteColumn);

    console.log('✅ [HANDLERS] All event handlers configured');
  }

  // ===========================
  // DELEGATED EVENT HANDLERS
  // ===========================

  function handleDocumentClick(e) {
    var $target = $(e.target);

    // Close filters when clicking outside
    if ($filtersContainer.is(':visible') &&
        !$filtersContainer.is($target) &&
        $filtersContainer.has($target).length === 0 &&
        !$target.closest('#btn-toggle-filters').length) {
      $filtersContainer.hide();
    }

    // Handle various button clicks via data attributes
    var action = $target.data('action');
    if (action) {
      e.preventDefault();
      handleAction(action, $target);
    }
  }

  function handleAction(action, $element) {
    console.log('🎬 [ACTION]', action);

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

  function handleTaskCardClick(e) {
    if ($(e.target).closest('.task-action-btn, .user-avatar, .label-tag').length) {
      return; // Don't open modal if clicking on interactive elements
    }

    var taskId = $(this).data('task-id');
    if (taskId) {
      openTaskModal(taskId);
    }
  }

  function handleColumnHeaderClick(e) {
    if ($(e.target).closest('.column-actions').length) {
      return;
    }

    var columnId = $(this).closest('.kanban-column').data('column-id');
    if (columnId && e.detail === 2) { // Double click
      editColumnName(columnId);
    }
  }

  function handleAddTaskClick(e) {
    e.preventDefault();
    e.stopPropagation();
    var columnId = $(this).closest('.kanban-column').data('column-id');
    if (columnId) {
      openNewTaskModal(columnId);
    }
  }

  function handleTaskStatusChange() {
    if (currentTask) {
      var newStatus = $(this).is(':checked') ? 'Completada' : 'Pendiente';
      updateTaskField('estado', newStatus);
    }
  }

  function handleTaskNameInput() {
    triggerAutoSave();
    autoResizeTextarea(this);
  }

  function handleTaskDescriptionInput() {
    autoResizeTextarea(this);
    var isEmpty = !currentTask.descripcion || currentTask.descripcion.trim() === '';
    if (isEmpty || $(this).val() !== currentTask.descripcion) {
      $('#btn-save-description, #btn-cancel-description').show();
    }
  }

  function handleTaskModalClose() {
    if (timerInterval) {
      pauseTimer();
    }
    currentTask = null;
    clearTaskForm();
  }

  function handleColumnModalClose() {
    currentColumn = null;
    clearColumnForm();
  }

  function handleDropdownShow(e) {
    var $dropdown = $(e.target);
    var dropdownId = $dropdown.attr('id');

    var handlers = {
      'users-dropdown': populateUsersDropdown,
      'labels-dropdown': populateLabelsDropdown,
      'dates-dropdown': populateDatesDropdown,
      'checklist-dropdown': function() { $('#checklist-title-inline').val('').focus(); },
      'link-dropdown': function() {
        $('#link-title-inline, #link-url-inline').val('');
      }
    };

    if (handlers[dropdownId]) {
      handlers[dropdownId]();
    }
  }

  function toggleFilters(e) {
    e.stopPropagation();
    $filtersContainer.toggle();
  }

  // ===========================
  // EXPOSE PUBLIC API
  // ===========================

  // Export functions that need to be accessed globally
  window.TableroKanban = $.extend(window.TableroKanban, {
    // Core functions will be loaded from the main script
    // This file only handles event delegation optimization
    init: setupEventHandlers
  });

  console.log('✅ [MODULE] TableroKanban module initialized');

})();
