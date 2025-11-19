<?php

    if(!isset($_GET['id'])) {
        die();
    }

    $id = is_numeric($_GET['id']) && $_GET['id'] > 0 ? $_GET['id'] : null;
    $obj = new Seccion($id);
    $menus = Menu::getAll("ORDER BY nombre ASC");

?>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-file"></i> <b><?= $obj->id ? 'Detalle Sección' : 'Nueva Sección'; ?></b></h1>
  </div>
  <div>
    <div>
      <?php $GLOBALS['usuario']->printReturnBtn(); ?>
    </div>
  </div>
</div>
<hr />
<?php
  Msg::show(1,'Sección guardada con &eacute;xito','primary');
?>
<div class="row">
  <div class="col-md-6">
    <div class="card">
      <div class="card-body">
        <form id="secciones-form">
          <input type="hidden" name="id" value="">
          <input type="hidden" name="entidad" value="secciones">
          <div class="row">
            <div class="col-6 mb-1">
              Nombre:
            </div>
            <div class="col-6 mb-1">
              <input type="text" name="nombre" class="form-control" required>
            </div>
            <div class="col-6 mb-1">
              Template File:
            </div>
            <div class="col-6 mb-1">
              <input type="text" name="template_file" class="form-control" required placeholder="nombre-seccion">
              <small class="text-muted">Sin extensión .php</small>
            </div>
            <div class="col-6 mb-1">
              Menú:
            </div>
            <div class="col-6 mb-1">
              <select name="id_menus" class="form-control">
                <option value="0">Sin menú</option>
                <?php
                if(is_array($menus)) {
                  foreach($menus as $menu) {
                ?>
                <option value="<?= $menu->id; ?>"><?= $menu->nombre; ?></option>
                <?php
                  }
                }
                ?>
              </select>
            </div>
            <div class="col-6 mb-1">
              Clasificación:
            </div>
            <div class="col-6 mb-1">
              <input type="text" name="clasificacion" class="form-control" placeholder="General">
            </div>
            <div class="col-6 mb-1">
              Visible:
            </div>
            <div class="col-6 mb-1">
              <select name="visible" class="form-control">
                <option value="1">Sí</option>
                <option value="0">No</option>
              </select>
            </div>
            <div class="col-6 mb-1">
              Permisos Editables:
            </div>
            <div class="col-6 mb-1">
              <select name="permisos_editables" class="form-control">
                <option value="1">Sí</option>
                <option value="0">No</option>
              </select>
            </div>
            <div class="col-6 mb-1">
              Create Path:
            </div>
            <div class="col-6 mb-3">
              <select name="create_path" class="form-control">
                <option value="0">No</option>
                <option value="1">Sí</option>
              </select>
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
</div>

<?php if($obj->id) { ?>
<div class="mt-3">
  <a href="./?s=<?= $obj->template_file; ?>" class="btn btn-success" target="_blank">
    <i class="fas fa-fw fa-external-link-alt"></i> Abrir Sección
  </a>
</div>
<?php } ?>

<div class="modal fade" tabindex="-1" role="dialog" id="eliminar-obj-modal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Eliminar Sección</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <center><h5>¿Desea eliminar esta Sección?<br/>Este paso no es reversible.</h5></center>
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

$(function() {
  $.each(obj,function(key,value){
    if(key!="table_name"&&key!="table_fields"){
      $('#secciones-form input[name="'+key+'"]').val(value);
      $('#secciones-form textarea[name="'+key+'"]').val(value);
      $('#secciones-form select[name="'+key+'"]').val(value);
    }
  });
});

$(document).on('click','#guardar-btn',function(e){

  e.preventDefault();

  if($('input[name="nombre"]').val().length < 2) {
    alert("El nombre debe tener mas de 2 caracteres.");
    return false;
  }

  if($('input[name="template_file"]').val().length < 2) {
    alert("El template file debe tener mas de 2 caracteres.");
    return false;
  }

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("secciones");

  $.post(url,data,function(response){
    if(response.mensaje !== "OK") {
      alert("Error al guardar: " + (response.mensaje || "Error desconocido"));
      return false;
    } else {
      window.location.href = "./?s=detalle-secciones&id=" + response.obj.id + "&msg=1";
    }
  },"json").fail(function(xhr, status, error){
    alert("No funcionó la conexión");
  });
});

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
