<?php

  $formatos = FormatoDeEnvases::getAll("WHERE estado!='eliminado' ORDER BY tipo ASC, volumen_ml ASC");
  $formatos_latas = FormatoDeEnvases::getAllByTipo('Lata');
  $formatos_botellas = FormatoDeEnvases::getAllByTipo('Botella');

?>
<style>
.tr-formatos {
  cursor: pointer;
}
.nav-tabs .nav-link.active {
  font-weight: bold;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-box"></i> <b>Formatos de Envases</b></h1>
  </div>
  <div>
    <div>
      <button class="btn btn-sm btn-primary shadow-sm mb-2" data-bs-toggle="modal" data-bs-target="#nuevo-formato-modal">
        <i class="fas fa-fw fa-plus"></i> Nuevo Formato
      </button>
    </div>
  </div>
</div>
<hr />
<?php
Msg::show(1,'Formato guardado con &eacute;xito','info');
Msg::show(2,'Formato eliminado con &eacute;xito','danger');
?>

<!-- Tabs para tipo de envase -->
<ul class="nav nav-tabs mb-3" id="envases-tabs" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="latas-tab" data-bs-toggle="tab" data-bs-target="#latas-content" type="button" role="tab">
      <i class="fas fa-fw fa-wine-bottle"></i> Latas (<?= count($formatos_latas); ?>)
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="botellas-tab" data-bs-toggle="tab" data-bs-target="#botellas-content" type="button" role="tab">
      <i class="fas fa-fw fa-wine-glass"></i> Botellas (<?= count($formatos_botellas); ?>)
    </button>
  </li>
</ul>

<div class="tab-content" id="envases-tabs-content">
  <!-- Tab Latas -->
  <div class="tab-pane fade show active" id="latas-content" role="tabpanel">
    <table class="table table-hover table-striped table-sm mb-4" id="formatos-latas-table">
      <thead class="thead-dark">
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Volumen (ml)</th>
          <th>Estado</th>
          <th>Creada</th>
          <th style="width: 100px">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($formatos_latas as $formato) { ?>
        <tr class="tr-formatos" data-id="<?= $formato->id; ?>">
          <td><?= $formato->id; ?></td>
          <td><?= $formato->nombre; ?></td>
          <td><?= number_format($formato->volumen_ml, 0, ',', '.'); ?> ml</td>
          <td>
            <?php if($formato->estado == 'activo') { ?>
              <span class="badge bg-success">Activo</span>
            <?php } else { ?>
              <span class="badge bg-secondary">Inactivo</span>
            <?php } ?>
          </td>
          <td><?= datetime2fechayhora($formato->creada); ?></td>
          <td>
            <button class="btn btn-sm btn-outline-primary editar-formato-btn"
                    data-id="<?= $formato->id; ?>"
                    data-nombre="<?= htmlspecialchars($formato->nombre); ?>"
                    data-tipo="<?= $formato->tipo; ?>"
                    data-volumen="<?= $formato->volumen_ml; ?>"
                    data-estado="<?= $formato->estado; ?>">
              <i class="fas fa-fw fa-edit"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger eliminar-formato-btn"
                    data-id="<?= $formato->id; ?>"
                    data-nombre="<?= htmlspecialchars($formato->nombre); ?>">
              <i class="fas fa-fw fa-trash"></i>
            </button>
          </td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>

  <!-- Tab Botellas -->
  <div class="tab-pane fade" id="botellas-content" role="tabpanel">
    <table class="table table-hover table-striped table-sm mb-4" id="formatos-botellas-table">
      <thead class="thead-dark">
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Volumen (ml)</th>
          <th>Estado</th>
          <th>Creada</th>
          <th style="width: 100px">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($formatos_botellas as $formato) { ?>
        <tr class="tr-formatos" data-id="<?= $formato->id; ?>">
          <td><?= $formato->id; ?></td>
          <td><?= $formato->nombre; ?></td>
          <td><?= number_format($formato->volumen_ml, 0, ',', '.'); ?> ml</td>
          <td>
            <?php if($formato->estado == 'activo') { ?>
              <span class="badge bg-success">Activo</span>
            <?php } else { ?>
              <span class="badge bg-secondary">Inactivo</span>
            <?php } ?>
          </td>
          <td><?= datetime2fechayhora($formato->creada); ?></td>
          <td>
            <button class="btn btn-sm btn-outline-primary editar-formato-btn"
                    data-id="<?= $formato->id; ?>"
                    data-nombre="<?= htmlspecialchars($formato->nombre); ?>"
                    data-tipo="<?= $formato->tipo; ?>"
                    data-volumen="<?= $formato->volumen_ml; ?>"
                    data-estado="<?= $formato->estado; ?>">
              <i class="fas fa-fw fa-edit"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger eliminar-formato-btn"
                    data-id="<?= $formato->id; ?>"
                    data-nombre="<?= htmlspecialchars($formato->nombre); ?>">
              <i class="fas fa-fw fa-trash"></i>
            </button>
          </td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Nuevo/Editar Formato -->
<div class="modal fade" tabindex="-1" role="dialog" id="nuevo-formato-modal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="formato-modal-title">Nuevo Formato de Envase</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="formato-form">
          <input type="hidden" name="id" value="">
          <div class="row">
            <div class="col-4 mb-3">
              <label class="form-label">Tipo:</label>
            </div>
            <div class="col-8 mb-3">
              <select name="tipo" class="form-control" id="formato-tipo-select">
                <option value="Lata">Lata</option>
                <option value="Botella">Botella</option>
              </select>
            </div>
            <div class="col-4 mb-3">
              <label class="form-label">Nombre:</label>
            </div>
            <div class="col-8 mb-3">
              <input type="text" name="nombre" class="form-control" placeholder="Ej: Lata 350ml" required>
            </div>
            <div class="col-4 mb-3">
              <label class="form-label">Volumen (ml):</label>
            </div>
            <div class="col-8 mb-3">
              <div class="input-group">
                <input type="number" name="volumen_ml" class="form-control" placeholder="350" min="1" required>
                <span class="input-group-text">ml</span>
              </div>
            </div>
            <div class="col-4 mb-3">
              <label class="form-label">Estado:</label>
            </div>
            <div class="col-8 mb-3">
              <select name="estado" class="form-control">
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
              </select>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="guardar-formato-btn">
          <i class="fas fa-fw fa-save"></i> Guardar
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Eliminar Formato -->
<div class="modal fade" tabindex="-1" role="dialog" id="eliminar-formato-modal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Eliminar Formato</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>¿Esta seguro que desea eliminar el formato <strong id="eliminar-formato-nombre"></strong>?</p>
        <p class="text-danger"><small>Esta accion no se puede deshacer.</small></p>
        <input type="hidden" id="eliminar-formato-id">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="eliminar-formato-aceptar-btn">
          <i class="fas fa-fw fa-trash"></i> Eliminar
        </button>
      </div>
    </div>
  </div>
</div>

<script>

console.log('🎯 [INIT] Formatos de Envases cargado');

// CSRF Token para llamadas AJAX seguras
const csrfToken = '<?= Security::getCSRFToken(); ?>';
console.log('🔐 [CSRF] Token generado:', csrfToken.substring(0, 10) + '...');

$(document).ready(function() {
  console.log('📄 [READY] Document ready - inicializando DataTables');

  // DataTable para Latas
  new DataTable('#formatos-latas-table', {
    language: {
      url: '//cdn.datatables.net/plug-ins/2.1.3/i18n/es-CL.json'
    },
    pageLength: 25,
    stateSave: true,
    order: [[2, 'asc']]
  });

  // DataTable para Botellas
  new DataTable('#formatos-botellas-table', {
    language: {
      url: '//cdn.datatables.net/plug-ins/2.1.3/i18n/es-CL.json'
    },
    pageLength: 25,
    stateSave: true,
    order: [[2, 'asc']]
  });
});

// Abrir modal para nuevo formato - setear tipo segun tab activo
$(document).on('click', '[data-bs-target="#nuevo-formato-modal"]', function() {
  console.log('➕ [FORMATOS] Abriendo modal nuevo formato');
  $('#formato-modal-title').text('Nuevo Formato de Envase');
  $('#formato-form')[0].reset();
  $('input[name="id"]').val('');
  $('select[name="estado"]').val('activo');

  // Detectar tab activo para preseleccionar tipo
  if($('#botellas-tab').hasClass('active')) {
    $('select[name="tipo"]').val('Botella');
  } else {
    $('select[name="tipo"]').val('Lata');
  }
});

// Abrir modal para editar formato
$(document).on('click', '.editar-formato-btn', function(e) {
  e.stopPropagation();
  var id = $(this).data('id');
  var nombre = $(this).data('nombre');
  var tipo = $(this).data('tipo');
  var volumen = $(this).data('volumen');
  var estado = $(this).data('estado');

  console.log('✏️ [FORMATOS] Editando formato:', { id: id, nombre: nombre, tipo: tipo, volumen: volumen, estado: estado });

  $('#formato-modal-title').text('Editar Formato de Envase');
  $('input[name="id"]').val(id);
  $('select[name="tipo"]').val(tipo);
  $('input[name="nombre"]').val(nombre);
  $('input[name="volumen_ml"]').val(volumen);
  $('select[name="estado"]').val(estado);

  $('#nuevo-formato-modal').modal('show');
});

// Guardar formato
$(document).on('click', '#guardar-formato-btn', function(e) {
  e.preventDefault();

  var nombre = $('input[name="nombre"]').val().trim();
  var volumen = $('input[name="volumen_ml"]').val();

  if(!nombre) {
    alert('Debe ingresar un nombre para el formato');
    return false;
  }

  if(!volumen || volumen <= 0) {
    alert('Debe ingresar un volumen valido');
    return false;
  }

  var data = {
    csrf_token: csrfToken,
    id: $('input[name="id"]').val(),
    tipo: $('select[name="tipo"]').val(),
    nombre: nombre,
    volumen_ml: volumen,
    estado: $('select[name="estado"]').val()
  };

  console.log('💾 [FORMATOS] Guardando formato:', data);

  $.post('./ajax/ajax_guardarFormatoDeEnvases.php', data, function(response) {
    console.log('📥 [FORMATOS] Respuesta:', response);
    if(response.status == 'OK') {
      window.location.href = './?s=formatos-de-envases&msg=1';
    } else {
      alert('Error: ' + response.mensaje);
    }
  }, 'json').fail(function(xhr, status, error) {
    console.error('❌ [FORMATOS] Error:', error);
    alert('Error al guardar el formato');
  });
});

// Abrir modal eliminar
$(document).on('click', '.eliminar-formato-btn', function(e) {
  e.stopPropagation();
  var id = $(this).data('id');
  var nombre = $(this).data('nombre');

  $('#eliminar-formato-id').val(id);
  $('#eliminar-formato-nombre').text(nombre);
  $('#eliminar-formato-modal').modal('show');
});

// Confirmar eliminar
$(document).on('click', '#eliminar-formato-aceptar-btn', function(e) {
  e.preventDefault();

  var id = $('#eliminar-formato-id').val();

  console.log('🗑️ [FORMATOS] Eliminando formato:', id);

  $.post('./ajax/ajax_eliminarFormatoDeEnvases.php', { csrf_token: csrfToken, id: id }, function(response) {
    console.log('📥 [FORMATOS] Respuesta:', response);
    if(response.status == 'OK') {
      window.location.href = './?s=formatos-de-envases&msg=2';
    } else {
      alert('Error: ' + response.mensaje);
    }
  }, 'json').fail(function(xhr, status, error) {
    console.error('❌ [FORMATOS] Error:', error);
    alert('Error al eliminar el formato');
  });
});

</script>
