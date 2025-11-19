<?php

$id = "";

if(validaIdExists($_GET,'id')) {
  $id = $_GET['id'];
}

$msg = 0;

if(isset($_GET['msg'])) {
  $msg = $_GET['msg'];
}

$obj = new Usuario($id);

$tipos_barril = $GLOBALS['tipos_barril'];
$tipos_barril_cerveza = $GLOBALS['tipos_barril_cerveza'];
$vendedores = Vendedor::getAll("WHERE estado='Activo'");
$usuario = $GLOBALS['usuario'];


?>
<style>
.tr-vendedores {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800">
      <b>
        <?php
        if($obj->id=="") {
          print '<i class="fas fa-fw fa-plus"></i> Nuevo';
        } else {
          print '<i class="fas fa-fw fa-handshake"></i> Detalle';
        }
        ?> Vendedor
      </b>
    </h1>
  </div>
  <div>
    <div>
    <a href="./?s=pedidos-vendedores&id=<?= $obj->id; ?>" class="d-sm-inline-block btn btn-sm btn-secondary shadow-sm">Pedidos</a>
    <a href="./?s=entregas-vendedores&id=<?= $obj->id; ?>" class="d-sm-inline-block btn btn-sm btn-secondary shadow-sm">Entregas</a>
      <?php $usuario->printReturnBtn(); ?>
    </div>
  </div>
</div>
<hr />
<?php
if($msg == 1) {
?>
<div class="alert alert-info" role="alert" >Vendedor guardado con &eacute;xito.</div>
<?php
}
?>

<form id="usuarios-form">
  <input type="hidden" name="id" value="">
  <input type="hidden" name="entidad" value="usuarios">
  <div class="row">
    <div class="col-md-6 row">
      <div class="col-6 mb-1">
        Nombre:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" name="nombre">
      </div>
      <div class="col-6 mb-1">
        Email:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" name="email">
      </div>
      <div class="col-6 mb-1">
        Telefono:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" name="telefono">
      </div>
      <div class="col-6 mb-1">
        Estado:
      </div>
      <div class="col-6 mb-1">
        <select name="estado" class="form-control">
          <option>Activo</option>
          <option>Bloqueado</option>
        </select>
      </div>
      <div class="col-6 mb-1">
        Meta barriles mensuales:
      </div>
      <div class="col-6 mb-1">
        <input type="number" min="0" value="0" name="vendedor_meta_barriles" class="form-control acero">
      </div>
      <div class="col-6 mb-1">
        Meta cajas mensuales:
      </div>
      <div class="col-6 mb-1">
        <input type="number" min="0" value="0" name="vendedor_meta_cajas" class="form-control acero">
      </div>
      <div class="col-12 mt-3 mb-1 d-flex justify-content-between">
        <?php
        if($obj->id != "") {
          ?>
          <button class="btn btn-sm btn-danger eliminar-obj-btn">Eliminar</button>
          <?php
        } else {
          print "&nbsp;";
        }
        ?>
        <button class="btn btn-sm btn-primary" id="guardar-btn">Guardar</button>
      </div>
    </div>
  </div>
</form>

<div class="modal fade" tabindex="-1" role="dialog" id="eliminar-vendedor-modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Eliminar Vendedor</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <center><h5>Desea eliminar este vendedor?<br/>Este paso no es reversible.</h5></center>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" id="eliminar-vendedor-aceptar" data-bs-dismiss="modal">Eliminar</button>
        </div>
      </div>
    </div>
  </div>

<script>
$(document).on('click','#guardar-btn',function(e){

  e.preventDefault();

  if($('input[name="nombre"]').val().length < 2) {
    alert("El nombre debe tener mas de 2 caracteres.");
    return false;
  }

  if(!$('input[name="email"]').val().includes('@')) {
    alert("Ingrese un correo valido.");
    return false;
  }

  if(!$('input[name="email"]').val().includes('.')) {
    alert("Ingrese un correo valido.");
    return false;
  }

  if($('input[name="email"]').val().length < 5) {
    alert("El email debe tener mas de 5 caracteres.");
    return false;
  }

  if($('input[name="telefono"]').val().length < 5) {
    alert("El telefono debe tener mas de 5 caracteres.");
    return false;
  }

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("usuarios");

  $.post(url,data,function(response_raw){
    console.log(response_raw);
    var response = JSON.parse(response_raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-vendedores&id=" + response.obj.id + "&msg=1";
    }
  }).fail(function(){
    alert("No funciono");
  });
});

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

  if(obj.emite_factura == 0) {
    $('.emite-factura').hide();
  }
});

$(document).on('click','.eliminar-obj-btn',function(e){
  e.preventDefault();
  $('#eliminar-vendedor-modal').modal('toggle');
})

$(document).on('click','#eliminar-vendedor-aceptar',function(e){

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

$(document).on('keyup','.acero',function(){
  $(this).val($(this).val().replace(/\D/g,''));
});

$(document).on('change','.acero',function(){
  if($(this).val() == "") {
    $(this).val(0);
  }
  $(this).val(parseInt($(this).val()));
});



</script>
