<?php

if(!validaIdExists($_GET,'id')) {
  die();
}

$obj = new Barril($_GET['id']);

$msg = 0;

if(isset($_GET['msg'])) {
  $msg = $_GET['msg'];
}

$repartidores = Usuario::getAll("WHERE nivel='repartidor'");
$clientes = Cliente::getAll();
$tipos_barril = $GLOBALS['tipos_barril'];

$ids_batches = $obj->getRelations("batches");
$entregas_productos = EntregaProducto::getAll("WHERE id_barriles='".$obj->id."' ORDER BY id desc");
$usuario = $GLOBALS['usuario'];
?>
<style>
.tr-barriles {
  cursor: pointer;
}
.tr-batches {
  cursor: pointer;
}
.tr-entregas {
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
          print '<i class="fas fa-fw fa-coins"></i> Detalle';
        }
        ?> Barril CO2
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
<?php
if($msg == 1) {
?>
<div class="alert alert-info" role="alert" >Barril guardado con &eacute;xito.</div>
<?php
}
?>
<?php
if($msg == 6) {
?>
<div class="alert alert-info" role="alert" >Barril marcado como <b>Perdido</b>.</div>
<?php
}
?>
<form id="barriles-form">
  <input type="hidden" name="id" value="">
  <input type="hidden" name="entidad" value="barriles">
  <input type="hidden" name="clasificacion" value="CO2">
  <div class="row">
    <div class="col-md-6 row">
      <div class="col-6 mb-2">
        C&oacute;digo de barril:
      </div>
      <div class="col-6 mb-2">
        <input type="text" class="form-control" name="codigo">
      </div>
      <div class="col-6 mb-2">
        Litraje:
      </div>
      <div class="col-6 mb-2">
        <select class="form-control" name="tipo_barril">
            <option>7L</option>
            <option>9L</option>
            <option>25L</option>
        </select>
      </div>
      <div class="col-12 mt-3 mb-1 d-flex justify-content-between">
        <?php
        if($obj->id != "") {
          ?>
          <button class="btn btn-sm btn-danger eliminar-obj-btn"><i class="fas fa-fw fa-trash"></i> Eliminar</button>
          <?php
        } else {
          print "&nbsp;";
        }
        ?>
        <div>
          <button class="btn btn-sm btn-secondary" id="marcar-como-perdido-btn">Marcar como Perdido</button>
          <button class="btn btn-sm btn-primary" id="guardar-btn"><i class="fas fa-fw fa-save"></i> Guardar</button>
        </div>
      </div>
    </div>
  </div>
</form>
<?php
  if($obj->id!="") {
?>


<div class="d-sm-flex align-items-center justify-content-between mt-5 mb-3">
  <div class="mb-2">
    <h1 class="h4 mb-0 text-gray-800">
      <b>
        Historial de Entregas
      </b>
    </h1>
  </div>
</div>

<table class="table table-hover table-striped table-sm" id="batches-table">
  <thead class="thead-dark">
    <tr>
        <th>
        <a href="#" class="sort" data-show="batches" data-sort="id">ID</a>
      </th>
      <th>
        <a href="#" class="sort" data-show="batches" data-sort="recetas">Cliente</a>
      </th>
      <th>
        <a href="#" class="sort" data-show="batches" data-sort="fecha_inicio">Fecha creaci&oacute;n</a>
      </th>
      <th>
        <a href="#" class="sort" data-show="batches" data-sort="estado">Estado</a>
      </th>
    </tr>
  </thead>
  <tbody>
    <?php
     
      foreach($entregas_productos as $ep) {
        $entrega = new Entrega($ep->id_entregas);
        $cliente = new Cliente($entrega->id_clientes);
    ?>
    <tr class="tr-entregas" data-identregas="<?= $entrega->id; ?>">
      <td>
        #<?= $entrega->id; ?>
      </td>
      <td>
        <?= $cliente->nombre; ?>
      </td>
      <td>
        <?= $entrega->creada; ?>
      </td>
      <td>
        <?= $entrega->estado; ?>
      </td>
    </tr>
    <?php
      }
    ?>
  </tbody>
</table>

<?php
  }
?>

<div class="modal fade" tabindex="-1" role="dialog" id="eliminar-barril-modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Eliminar Barril</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <center><h5>Desea eliminar este barril?<br/>Este paso no es reversible.</h5></center>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" id="eliminar-barril-aceptar" data-bs-dismiss="modal">Eliminar</button>
        </div>
      </div>
    </div>
  </div>

<script>

$(document).on('click','#guardar-btn',function(e){

  e.preventDefault();

  if($('input[name="codigo"]').val().length < 1) {
    alert("El codigo de barril debe tener al menos 1 caracter.");
    return false;
  }

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("barriles");

  $.post(url,data,function(response){
    console.log(response);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-barriles-co2&id=" + response.obj.id + "&msg=1";
    }
  },'json').fail(function(){
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
});

$(document).on('click','.eliminar-obj-btn',function(e){

  e.preventDefault();
  $('#eliminar-barril-modal').modal('toggle');

});

$(document).on('click','#eliminar-barril-aceptar',function(e){

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
      window.location.href = "./?s=barriles-co2&msg=2";
    }
  },"json").fail(function(){
    alert("No funciono");
  });
});

$(document).on('click','#marcar-como-perdido-btn',function(e){

  e.preventDefault();

  var data = {
    'id': obj.id,
    'modo': obj.table_name
  }
  var url = './ajax/ajax_marcarBarrilesComoPerdido.php';
  $.post(url,data,function(response){
    if(response.status!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-barriles-co2&id=" + obj.id + "&msg=6";
    }
  },"json").fail(function(){
    alert("No funciono");
  });
});


</script>
