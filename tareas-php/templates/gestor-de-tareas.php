<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <h2 class="mb-4">Gestor de Tareas</h2>

      <!-- Loading Spinner -->
      <div id="loading-spinner" class="text-center my-5" style="display: none;">
        <div class="spinner-border text-primary" role="status">
          <span class="sr-only">Cargando...</span>
        </div>
      </div>

      <!-- Tasks Container -->
      <div id="tasks-container">
        <!-- Tasks will be loaded here -->
      </div>

      <!-- Add Task Button -->
      <div class="text-center mt-3" id="add-task-container">
        <button class="btn btn-link text-primary" id="btn-add-task" style="display: none;">
          <i class="bi bi-plus-circle"></i> Agregar Tarea
        </button>
      </div>

      <!-- New Task Form (hidden by default) -->
      <div id="new-task-form" class="card mb-3" style="display: none;">
        <div class="card-body">
          <textarea
            id="new-task-name"
            class="form-control mb-2"
            rows="2"
            placeholder="Nombre de la tarea..."
          ></textarea>
          <div class="d-flex gap-2">
            <button class="btn btn-primary btn-sm" id="btn-save-new-task">Guardar</button>
            <button class="btn btn-secondary btn-sm" id="btn-cancel-new-task">Cancelar</button>
          </div>
        </div>
      </div>
    </div>
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

            <!-- Users Display -->
            <div id="task-users-display" class="mb-3"></div>

            <!-- Labels Display -->
            <div id="task-labels-display" class="mb-3"></div>

            <!-- Dates Display -->
            <div id="task-dates-display" class="mb-3"></div>

            <!-- Checklists Display -->
            <div id="task-checklists-container" class="mb-3"></div>

            <!-- Links Display -->
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

<!-- User Assignment Modal -->
<div class="modal fade" id="userAssignmentModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Asignar Usuarios</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <select id="user-assignment-select" class="form-select" multiple size="10">
          <!-- Users will be loaded here -->
        </select>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Label Management Modal -->
<div class="modal fade" id="labelManagementModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Gestionar Etiquetas</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="labels-list" class="mb-3">
          <!-- Labels checkboxes will be loaded here -->
        </div>
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

<!-- Dates Modal -->
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

<!-- Link Modal -->
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

<!-- Checklist Modal -->
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
.task-card {
  padding: 1rem;
  border-radius: 0.75rem;
  background-color: #f8f9fa;
  border: 1px solid #dee2e6;
  cursor: pointer;
  transition: all 0.3s;
}

.task-card:hover {
  box-shadow: 0 2px 4px rgba(0,0,0,0.2);
  border-color: #adb5bd;
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
  font-size: 0.75rem;
  font-weight: 500;
  border: 2px solid white;
  margin-left: -8px;
}

.user-avatar:first-child {
  margin-left: 0;
}

.label-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 1rem;
  font-size: 0.75rem;
  font-weight: 500;
  color: white;
  margin-right: 0.5rem;
  margin-bottom: 0.5rem;
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
</style>

<script>
// Store entity ID (passed from parent template)
var entityId = '<?php echo isset($_GET["entity_id"]) ? $_GET["entity_id"] : ""; ?>';
</script>
