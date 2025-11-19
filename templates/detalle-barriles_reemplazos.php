<?php

  if(!validaIdExists($_GET,'id')) {
    die();
  }

  $obj = new BarrilReemplazo($_GET['id']);
  $barril_devuelto = new Barril($obj->id_barriles_devuelto);
  $barril_reemplazo = new Barril($obj->id_barriles_reemplazo);
  $cliente = new Cliente($obj->id_clientes);
  $entrega = new Entrega($obj->id_entregas);
  $entrega_producto = new EntregaProducto($obj->id_entregas_productos);
  $usuario = $GLOBALS['usuario'];
  
?>
<style>
.tr-barriles {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800">
      <b>
        Detalle Reemplazo de Barriles #<?= $obj->id; ?>
      </b>
    </h1>
  </div>
  <div>
        <?php $usuario->printReturnBtn(); ?>
  </div>
</div>
<hr />

<form id="devolucion-barriles-form">
<input type="hidden" name="accion" value="Reemplazar">
  <div class="row">
    <div class="col-md-6 row">
        <div class="col-6 mb-1">
        Creada:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" name="creada" value="<?= datetime2fechayhora($obj->creada); ?>" DISABLED>
      </div>
      <div class="col-6 mb-1">
        Barril devuelto:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" value="<?= $barril_devuelto->codigo; ?>" DISABLED>
      </div>
      <div class="col-6 mb-1">
        Barril reemplazo:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" value="<?= $barril_reemplazo->codigo; ?>" DISABLED>
      </div>
      <div class="col-6 mb-1">
        Cliente
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" value="<?= $cliente->nombre; ?>" DISABLED>
      </div>
      <div class="col-6 mb-1">
        Motivo de reemplazo:
      </div>
      <div class="col-6 mb-1">
        <textarea name="motivo" class="form-control" DISABLED><?= $obj->motivo; ?></textarea>
      </div>
      <div class="col-12 mt-3 mb-1 text-right">
        <?php
        if($usuario->nivel == "Administrador") {
        ?>
        <button class="btn btn-danger btn-sm" id="barriles_reemplazos-revertir-btn"><i class="fas fa-fw fa-backward"></i> Revertir Reemplazo de Barril</button>
        <?php
        }
        ?>
      </div>
    </div>
  </div>
</form>


<script>


var obj = <?= json_encode($obj,JSON_PRETTY_PRINT); ?>;

$(document).on('click','#barriles_reemplazos-revertir-btn',function(e){

  e.preventDefault();

  var url = "./ajax/ajax_revertirReemplazoBarril.php";
  var data = {
    'id': obj.id
  };

  console.log(data);

  $.post(url,data,function(response_raw){
    console.log(response_raw);
    var response = JSON.parse(response_raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=barriles_reemplazos&msg=2";
    }
  }).fail(function(){
    alert("No funciono");
  });
});


</script>
