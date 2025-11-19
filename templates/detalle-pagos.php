<?php


if(validaIdExists($_GET,'id')) {
  $id = $_GET['id'];
} else {
    die();
}

$obj = new Pago($id);
$clientes = Cliente::getAll();
$usuario_ingreso = new Usuario($obj->id_usuarios);

$usuario = $GLOBALS['usuario'];

?>
<style>
.tr-pagos {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800">
      <b>
        Detalle Pago
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
<form id="pagos-form">
  <input type="hidden" name="id" value="">
  <input type="hidden" name="entidad" value="pagos">
  <div class="row">
    <div class="col-md-6 row">
    <div class="col-6 mb-1">
        Ingresado por:
      </div>
      <div class="col-6 mb-1">
        <input type="text" value="<?= $usuario_ingreso->nombre; ?>" class="form-control" DISABLED>
      </div>
      <div class="col-6 mb-1">
        Fecha:
      </div>
      <div class="col-6 mb-1">
        <input type="text" name="creada" class="form-control" DISABLED>
      </div>
      <div class="col-6 mb-2">
        Cliente:
      </div>
      <div class="col-6 mb-2">
        <select class="form-control" name="id_clientes" DISABLED>
            <?php
            foreach($clientes as $cliente_2) {
                print "<option value='".$cliente_2->id."'>".$cliente_2->nombre."</option>";
            }
            ?>
        </select>
      </div>
      <div class="col-6 mb-2">
        Monto:
      </div>
      <div class="col-6 mb-2">
        <div class="input-group">
                <span class="input-group-text" id="basic-addon1" style="border-radius: 5px 0px 0px 5px">$</span>
                <input type="text" class="form-control acero" name="amount" DISABLED>
              </div>
      </div>
      <div class="col-6 mb-1">
        Forma de pago:
      </div>
      <div class="col-6 mb-1">
        <input type="text" name="forma_de_pago" class="form-control" DISABLED>
      </div>
      <?php
      if($obj->forma_de_pago == "Documento" && $obj->id_documentos != 0) {
        ?>
      <div class="col-6 mb-1">
      </div>
      <div class="col-6 mb-1">
          <a class="btn btn-info btn-sm w-100" href="./?s=detalle-documentos&id=<?= $obj->id_documentos; ?>"><i class="fas fa-fw fa-eye"></i> Ver Documento</a>
      </div>
        <?php
      }
      ?>
      
      <div class="col-12 mt-3 mb-1 d-flex justify-content-between">
            <button class="btn btn-sm btn-danger eliminar-obj-btn"><i class="fas fa-fw fa-trash"></i> Eliminar y Revertir</button>
            <button class="btn btn-sm btn-primary" id="guardar-btn" DISABLED><i class="fas fa-fw fa-save"></i> Guardar</button>
      </div>
    </div>
  </div>
</form>

<div class="modal fade" tabindex="-1" role="dialog" id="eliminar-obj-modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Eliminar y Revertir Pago</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <center><h5>Desea eliminar y revertir este Pago?<br/>Este paso no es reversible.</h5></center>
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

    $.each(obj,function(key,value){
      console.log(key);
      if(key!="table_name"&&key!="table_fields"){
        $('input[name="'+key+'"]').val(value);
        $('textarea[name="'+key+'"]').val(value);
        $('select[name="'+key+'"]').val(value);
      }
    });


});

$(document).on('click','#guardar-btn',function(e){

  e.preventDefault();

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("pagos");

  $.post(url,data,function(response){
    console.log(response);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-pagos&id=" + response.obj.id + "&msg=1";
    }
  },'json').fail(function(){
    alert("No funciono");
  });
});



$(document).on('click','.eliminar-obj-btn',function(e){

  e.preventDefault();
  $('#eliminar-obj-modal').modal('toggle');

});

$(document).on('click','#eliminar-obj-aceptar',function(e){

  e.preventDefault();

  var data = {
    'id': obj.id
  }
  var url = './ajax/ajax_eliminarRevertirPago.php';
  $.post(url,data,function(raw){
    console.log(raw);
    var response = JSON.parse(raw);
    if(response.status!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "<?= $usuario->getReturnLink(); ?>&msg=3";
    }
  }).fail(function(){
    alert("No funciono");
  });
});


</script>