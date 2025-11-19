<?php

    if(!isset($_GET['id'])) {
        die();
    }

    $id = is_numeric($_GET['id']) && $_GET['id'] > 0 ? $_GET['id'] : null;
    $obj = new Tablero($id);
    $todos_usuarios = Usuario::getAll("WHERE nivel != 'Invitado' ORDER BY nombre ASC");
    $usuarios_asignados = array();
    $usuarios_asignados_ids = array();

    if($obj->id) {
      $usuarios_asignados = $obj->getUsuarios();
      foreach($usuarios_asignados as $u) {
        $usuarios_asignados_ids[] = $u->id;
      }
    }

?>
<style>
.user-item {
  cursor: pointer;
  padding: 8px;
  border-radius: 4px;
  transition: background-color 0.2s;
}
.user-item:hover {
  background-color: #f8f9fa;
}
.user-item.selected {
  background-color: #e7f3ff;
  border-left: 3px solid #0d6efd;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-columns"></i> <b><?= $obj->id ? 'Detalle Tablero' : 'Nuevo Tablero'; ?></b></h1>
  </div>
  <div>
    <div>
      <?php $GLOBALS['usuario']->printReturnBtn(); ?>
    </div>
  </div>
</div>
<hr />
<?php
  Msg::show(1,'Tablero guardado con &eacute;xito','primary');
?>
<div class="row">
  <div class="col-md-6">
    <div class="card">
      <div class="card-body">
        <form id="tableros-form">
          <input type="hidden" name="id" value="">
          <input type="hidden" name="entidad" value="tableros">
          <input type="hidden" name="id_entidad" value="<?= isset($_GET['id_entidad']) ? $_GET['id_entidad'] : $GLOBALS['usuario']->id; ?>">
          <div class="row">
            <div class="col-6 mb-1">
              Nombre:
            </div>
            <div class="col-6 mb-1">
              <input type="text" name="nombre" class="form-control" required>
            </div>
            <div class="col-12 mb-1">
              Descripci&oacute;n:
            </div>
            <div class="col-12 mb-3">
              <textarea name="descripcion" class="form-control" rows="4"></textarea>
            </div>
            <div class="col-6 mb-1">
              Orden:
            </div>
            <div class="col-6 mb-1">
              <input type="number" name="orden" class="form-control" value="0" min="0">
            </div>
            <div class="col-12 mb-1 mt-3 d-flex justify-content-between">
              <?php if($obj->id) { ?>
              <button class="btn btn-danger btn-sm eliminar-obj-btn"><i class="fas fa-fw fa-trash"></i> Eliminar</button>
              <?php } ?>
              <button class="btn btn-primary btn-sm ms-auto" id="guardar-btn"><i class="fas fa-fw fa-save"></i> Guardar</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card">
      <div class="card-header">
        <h6 class="mb-0"><i class="fas fa-fw fa-users"></i> Usuarios Asignados</h6>
      </div>
      <div class="card-body" style="max-height: 400px; overflow-y: auto;">
        <?php if(count($todos_usuarios) > 0) { ?>
          <div id="usuarios-list">
            <?php foreach($todos_usuarios as $usuario_item) {
              $is_assigned = in_array($usuario_item->id, $usuarios_asignados_ids);
            ?>
            <div class="user-item form-check <?= $is_assigned ? 'selected' : ''; ?>" data-userid="<?= $usuario_item->id; ?>">
              <input class="form-check-input user-checkbox" type="checkbox" value="<?= $usuario_item->id; ?>" id="user-<?= $usuario_item->id; ?>" <?= $is_assigned ? 'checked' : ''; ?>>
              <label class="form-check-label w-100" for="user-<?= $usuario_item->id; ?>">
                <?= $usuario_item->nombre; ?> <?= $usuario_item->apellido; ?>
                <small class="text-muted d-block"><?= $usuario_item->email; ?></small>
              </label>
            </div>
            <?php } ?>
          </div>
        <?php } else { ?>
          <p class="text-muted mb-0">No hay usuarios disponibles</p>
        <?php } ?>
      </div>
    </div>
  </div>
</div>

<?php if($obj->id) { ?>
<div class="mt-4">
  <a href="./?s=tablero-kanban&id=<?= $obj->id; ?>" class="btn btn-success">
    <i class="fas fa-fw fa-eye"></i> Ver Tablero Kanban
  </a>
</div>
<?php } ?>

<div class="modal fade" tabindex="-1" role="dialog" id="eliminar-obj-modal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Eliminar Tablero</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <center><h5>¿Desea eliminar este Tablero?<br/>Este paso no es reversible.</h5></center>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="eliminar-obj-aceptar" data-bs-dismiss="modal">Eliminar</button>
      </div>
    </div>
  </div>
</div>

<script>

var obj = <?= json_encode($obj,JSON_PRETTY_PRINT); ?>;
var selectedUsers = <?= json_encode($usuarios_asignados_ids); ?>;

$(function() {
  $.each(obj,function(key,value){
    if(key!="table_name"&&key!="table_fields"){
      $('#tableros-form input[name="'+key+'"]').val(value);
      $('#tableros-form textarea[name="'+key+'"]').val(value);
      $('#tableros-form select[name="'+key+'"]').val(value);
    }
  });
});

// Toggle user selection
$(document).on('change', '.user-checkbox', function() {
  var userId = $(this).val();
  var isChecked = $(this).is(':checked');
  var userItem = $(this).closest('.user-item');

  if(isChecked) {
    userItem.addClass('selected');
    if(selectedUsers.indexOf(userId) === -1) {
      selectedUsers.push(userId);
    }
  } else {
    userItem.removeClass('selected');
    var index = selectedUsers.indexOf(userId);
    if(index > -1) {
      selectedUsers.splice(index, 1);
    }
  }
});

$(document).on('click','.user-item',function(e) {
  if(!$(e.target).is('input')) {
    var checkbox = $(this).find('.user-checkbox');
    checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
  }
});

$(document).on('click','#guardar-btn',function(e){

  e.preventDefault();

  if($('input[name="nombre"]').val().length < 2) {
    alert("El nombre debe tener mas de 2 caracteres.");
    return false;
  }

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("tableros");

  console.log('Saving tablero with data:', data);

  $.post(url,data,function(response){
    console.log('Save response:', response);
    if(response.mensaje !== "OK") {
      alert("Error al guardar: " + (response.mensaje || "Error desconocido"));
      return false;
    } else {
      var tableroId = response.obj.id;

      // Save user assignments
      saveUserAssignments(tableroId, function() {
        window.location.href = "./?s=detalle-tableros&id=" + tableroId + "&msg=1";
      });
    }
  },"json").fail(function(xhr, status, error){
    console.error('Save failed:', status, error);
    alert("No funcionó la conexión");
  });
});

function saveUserAssignments(tableroId, callback) {
  if(!obj.id && selectedUsers.length === 0) {
    callback();
    return;
  }

  var promises = [];
  var currentUsers = <?= json_encode($usuarios_asignados_ids); ?>;

  // Determine which users to add and remove
  var usersToAdd = selectedUsers.filter(function(id) {
    return currentUsers.indexOf(id) === -1;
  });

  var usersToRemove = currentUsers.filter(function(id) {
    return selectedUsers.indexOf(id) === -1;
  });

  console.log('Users to add:', usersToAdd);
  console.log('Users to remove:', usersToRemove);

  // Add users
  usersToAdd.forEach(function(userId) {
    var promise = $.ajax({
      url: './ajax/ajax_toggleTableroUsuario.php',
      type: 'POST',
      data: {
        id_tablero: tableroId,
        id_usuario: userId,
        action: 'agregar'
      },
      dataType: 'json'
    });
    promises.push(promise);
  });

  // Remove users
  usersToRemove.forEach(function(userId) {
    var promise = $.ajax({
      url: './ajax/ajax_toggleTableroUsuario.php',
      type: 'POST',
      data: {
        id_tablero: tableroId,
        id_usuario: userId,
        action: 'quitar'
      },
      dataType: 'json'
    });
    promises.push(promise);
  });

  if(promises.length > 0) {
    Promise.all(promises).then(function() {
      console.log('All user assignments saved');
      callback();
    }).catch(function(error) {
      console.error('Error saving user assignments:', error);
      callback();
    });
  } else {
    callback();
  }
}

$(document).on('click','.eliminar-obj-btn',function(e){
  e.preventDefault();
  $('#eliminar-obj-modal').modal('show');
})

$(document).on('click','#eliminar-obj-aceptar',function(e){

  e.preventDefault();

  var data = {
    'id': obj.id,
    'modo': obj.table_name
  }
  var url = './ajax/ajax_eliminarEntidad.php';
  $.post(url,data,function(response){
    if(response.status!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=" + response.table_name + "&msg=2";
    }
  },"json").fail(function(){
    alert("No funciono");
  });
});

</script>
