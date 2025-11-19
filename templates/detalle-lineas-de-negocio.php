<?php


if(!validaIdExists($_GET,'id')) {
    die();
}

$usuario = $GLOBALS['usuario'];
$obj = new LineaDeNegocio($_GET['id']);

?>
<style>
.tr-gasto {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800">
      <b>
        Detalle Linea de Negocio
      </b>
    </h1>
  </div>
  <div>
    <div>
      <?php $usuario->printReturnBtn(); ?>
    </div>
  </div>
</div>
<hr />

<form id="lineas-de-negocio-form">
  <input type="hidden" name="id" value="">
  <input type="hidden" name="entidad" value="lineas_de_negocio">
  <div class="row">
    <div class="col-md-6 mb-5">
      <div class="row">
        <div class="col-6 mb-1">
          Nombre:
        </div>
        <div class="col-6 mb-1">
          <input type="text" class="form-control" name="nombre">
        </div>
        <div class="col-12 mt-3 mb-1 d-flex justify-content-between">
        <button class="btn btn-sm btn-danger eliminar-obj-btn"><i class="fas fa-fw fa-trash"></i> Eliminar</button>
          <button class="btn btn-sm btn-primary" id="guardar-btn"><i class="fas fa-fw fa-save"></i> Guardar</button>
        </div>
      </div>
    </div>
    <div class="col-md-6">
 
    </div>
  </div>
</form>

  <div class="modal fade" tabindex="-1" role="dialog" id="eliminar-obj-modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Eliminar Linea de Negocio</h5>
          <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
            <i class="ki-duotone ki-cross fs-1">
              <span class="path1"></span>
              <span class="path2"></span>
            </i>
          </div>
        </div>
        <div class="modal-body">
          <center><h5>Desea eliminar esta Linea de Negocio?<br/>Este paso no es reversible.</h5></center>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" id="eliminar-obj-aceptar" data-bs-dismiss="modal">Eliminar</button>
        </div>
      </div>
    </div>
  </div>

<script>

var obj = <?= json_encode($obj,JSON_PRETTY_PRINT); ?>;

$(document).ready(function(){

  if(obj.id!="") {
    $.each(obj,function(key,value){
      console.log(key);
      if(key!="table_name"&&key!="table_fields"){
        $('input[name="'+key+'"]').val(value);
        $('textarea[name="'+key+'"]').val(value);
        $('select[name="'+key+'"]').val(value);
      }
    });
  }
});

$(document).on('click','#guardar-btn',function(e){

  e.preventDefault();

  if($('input[name="nombre"]').val().length < 2) {
    alert("El nombre de tener mas de 2 caracteres.");
    return false;
  }

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("lineas-de-negocio");

  $.post(url,data,function(raw){
    console.log(raw);
    var response = JSON.parse(raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-lineas-de-negocio&id=" + response.obj.id + "&msg=1";
    }
  }).fail(function(){
    alert("No funciono");
  });
});

$(document).on('click','.eliminar-obj-btn',function(e){
  e.preventDefault();
  $('#eliminar-obj-modal').modal('toggle');
})

$(document).on('click','#eliminar-obj-aceptar',function(e){

  e.preventDefault();

  var data = {
    'id': obj.id,
    'modo': obj.table_name
  }
  var url = './ajax/ajax_eliminarEntidad.php';
  $.post(url,data,function(raw){
    console.log(raw);
    var response = JSON.parse(raw);
    if(response.status!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=lineas-de-negocio&msg=3";
    }
  }).fail(function(){
    alert("No funciono");
  });
});

</script>
