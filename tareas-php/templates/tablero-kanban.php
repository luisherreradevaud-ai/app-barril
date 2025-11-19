<div class="container-fluid p-0">
  <!-- Header -->
  <div class="kanban-header d-flex justify-content-between align-items-center p-3 bg-white border-bottom">
    <div class="d-flex align-items-center gap-3">
      <h2 class="mb-0" id="tablero-titulo">Cargando...</h2>
      <button class="btn btn-sm btn-link text-muted" id="btn-editar-tablero">
        <i class="bi bi-pencil"></i>
      </button>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-primary btn-sm" id="btn-agregar-columna">
        <i class="bi bi-plus-circle"></i> Nueva Columna
      </button>
    </div>
  </div>

  <!-- Loading Spinner -->
  <div id="loading-spinner" class="text-center my-5" style="display: none;">
    <div class="spinner-border text-primary" role="status">
      <span class="sr-only">Cargando...</span>
    </div>
  </div>

  <!-- Kanban Board -->
  <div id="kanban-board" class="kanban-container p-3">
    <!-- Columns will be loaded here -->
  </div>
</div>

<!-- Task Detail Modal -->
<div class="modal fade" id="taskModal" tabindex="-1" aria-labelledby="taskModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <div class="d-flex align-items-center gap-2 flex-grow-1">
          <input type="checkbox" id="task-status-checkbox" class="form-check-input">
          <textarea
            id="task-name"
            class="form-control fw-bold fs-5"
            rows="1"
            style="resize: none; overflow: hidden;"
          ></textarea>
        </div>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-sm btn-outline-danger" id="btn-delete-task">
            <i class="bi bi-trash"></i>
          </button>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
      </div>
      <div class="modal-body">
        <div class="row">
          <!-- Left Column -->
          <div class="col-md-8">
            <!-- Description -->
            <div class="mb-4">
              <label class="form-label fw-semibold">Descripción</label>
              <textarea
                id="task-description"
                class="form-control"
                rows="3"
                placeholder="Agregar descripción..."
              ></textarea>
            </div>

            <!-- Action Buttons -->
            <div class="mb-3">
              <button class="btn btn-sm btn-outline-primary" id="btn-assign-users">
                <i class="bi bi-person-plus"></i> Asignar Usuarios
              </button>
              <button class="btn btn-sm btn-outline-primary" id="btn-add-labels">
                <i class="bi bi-tag"></i> Etiquetas
              </button>
              <button class="btn btn-sm btn-outline-primary" id="btn-add-checklist">
                <i class="bi bi-check-square"></i> Checklist
              </button>
              <button class="btn btn-sm btn-outline-primary" id="btn-add-dates">
                <i class="bi bi-calendar"></i> Fechas
              </button>
              <button class="btn btn-sm btn-outline-primary" id="btn-add-link">
                <i class="bi bi-link-45deg"></i> Enlace
              </button>
            </div>

            <!-- Users, Labels, Dates Display -->
            <div id="task-users-display" class="mb-3"></div>
            <div id="task-labels-display" class="mb-3"></div>
            <div id="task-dates-display" class="mb-3"></div>
            <div id="task-checklists-container" class="mb-3"></div>
            <div id="task-links-container" class="mb-3"></div>

            <!-- Saving Indicator -->
            <div id="saving-indicator" class="text-muted small" style="display: none;">
              <span class="spinner-border spinner-border-sm me-1"></span>
              Guardando...
            </div>
          </div>

          <!-- Right Column -->
          <div class="col-md-4">
            <h6 class="fw-semibold mb-3">Comentarios y actividad</h6>
            <div id="task-activity" class="border rounded p-3" style="min-height: 300px;">
              <p class="text-muted small">No hay actividad aún</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Column Editor Modal -->
<div class="modal fade" id="columnModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="columnModalTitle">Nueva Columna</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Nombre de la Columna</label>
          <input type="text" id="column-name" class="form-control" placeholder="Ej: Por Hacer">
        </div>
        <div class="mb-3">
          <label class="form-label">Color</label>
          <input type="color" id="column-color" class="form-control form-control-color" value="#6A1693">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="btn-delete-column" style="display: none;">Eliminar</button>
        <button type="button" class="btn btn-primary" id="btn-save-column">Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modals (reusing from previous version) -->
<div class="modal fade" id="userAssignmentModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Asignar Usuarios</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <select id="user-assignment-select" class="form-select" multiple size="10"></select>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="labelManagementModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Gestionar Etiquetas</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="labels-list" class="mb-3"></div>
        <hr>
        <h6>Crear Nueva Etiqueta</h6>
        <div class="mb-2">
          <input type="text" id="new-label-name" class="form-control" placeholder="Nombre de la etiqueta">
        </div>
        <div class="mb-2">
          <input type="color" id="new-label-color" class="form-control form-control-color" value="#6A1693">
        </div>
        <button class="btn btn-primary btn-sm" id="btn-create-label">Crear Etiqueta</button>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="datesModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Fechas</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Fecha de Inicio</label>
          <input type="date" id="task-start-date" class="form-control">
        </div>
        <div class="mb-3">
          <label class="form-label">Fecha de Vencimiento</label>
          <input type="date" id="task-due-date" class="form-control">
        </div>
        <div class="mb-3">
          <label class="form-label">Recordatorio</label>
          <select id="task-reminder" class="form-select">
            <option value="">Sin recordatorio</option>
            <option value="atTimeDueDate">A la hora de vencimiento</option>
            <option value="1 hour before">1 hora antes</option>
            <option value="2 hours before">2 horas antes</option>
            <option value="1 day before">1 día antes</option>
            <option value="2 days before">2 días antes</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="linkModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Agregar Enlace</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Título</label>
          <input type="text" id="link-title" class="form-control" placeholder="Título del enlace">
        </div>
        <div class="mb-3">
          <label class="form-label">URL</label>
          <input type="url" id="link-url" class="form-control" placeholder="https://">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btn-save-link">Guardar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="checklistModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Crear Checklist</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Nombre del Checklist</label>
          <input type="text" id="checklist-title" class="form-control" placeholder="Nombre del checklist">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btn-create-checklist">Crear</button>
      </div>
    </div>
  </div>
</div>

<style>
.kanban-container {
  display: flex;
  gap: 1rem;
  overflow-x: auto;
  min-height: calc(100vh - 150px);
  padding-bottom: 2rem;
}

.kanban-column {
  min-width: 320px;
  max-width: 320px;
  background-color: #f8f9fa;
  border-radius: 0.5rem;
  padding: 0.75rem;
  display: flex;
  flex-direction: column;
  height: fit-content;
}

.kanban-column-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding: 0.5rem;
  border-radius: 0.375rem;
}

.kanban-column-title {
  font-weight: 600;
  font-size: 0.875rem;
  margin: 0;
  color: white;
  flex-grow: 1;
}

.kanban-column-count {
  background-color: rgba(255, 255, 255, 0.3);
  color: white;
  padding: 0.125rem 0.5rem;
  border-radius: 1rem;
  font-size: 0.75rem;
  font-weight: 600;
}

.kanban-tasks {
  flex-grow: 1;
  min-height: 100px;
  padding: 0.25rem;
}

.kanban-task {
  background-color: white;
  border-radius: 0.5rem;
  padding: 0.75rem;
  margin-bottom: 0.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  cursor: move;
  transition: all 0.2s;
}

.kanban-task:hover {
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
  transform: translateY(-2px);
}

.kanban-task.ui-sortable-helper {
  transform: rotate(3deg);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
}

.kanban-task-name {
  font-weight: 500;
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
  color: #212529;
}

.kanban-task-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  align-items: center;
  font-size: 0.75rem;
  color: #6c757d;
}

.kanban-task-placeholder {
  background-color: #e9ecef;
  border: 2px dashed #adb5bd;
  border-radius: 0.5rem;
  height: 60px;
  margin-bottom: 0.5rem;
}

.kanban-add-task {
  width: 100%;
  text-align: left;
  color: #6c757d;
  font-size: 0.875rem;
  padding: 0.5rem;
  background-color: transparent;
  border: none;
  border-radius: 0.375rem;
  cursor: pointer;
  transition: background-color 0.2s;
}

.kanban-add-task:hover {
  background-color: rgba(0, 0, 0, 0.05);
}

.user-avatar {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background-color: #6A1693;
  color: white;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 0.625rem;
  font-weight: 500;
  border: 2px solid white;
  margin-left: -8px;
}

.user-avatar:first-child {
  margin-left: 0;
}

.label-badge {
  display: inline-block;
  padding: 0.125rem 0.5rem;
  border-radius: 0.25rem;
  font-size: 0.625rem;
  font-weight: 500;
  color: white;
}

.checklist-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem;
  border-bottom: 1px solid #dee2e6;
}

.checklist-item:last-child {
  border-bottom: none;
}

.progress-bar-custom {
  height: 0.5rem;
  background-color: #e9ecef;
  border-radius: 0.25rem;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background-color: #6A1693;
  transition: width 0.3s;
}

.column-actions {
  opacity: 0;
  transition: opacity 0.2s;
}

.kanban-column:hover .column-actions {
  opacity: 1;
}
</style>

<script>
// Store entity ID
var entityId = '<?php echo isset($_GET["entity_id"]) ? $_GET["entity_id"] : ""; ?>';
var currentTableroId = null;
</script>
