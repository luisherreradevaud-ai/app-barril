<?php

  //checkAutorizacion(["Jefe de Planta","Administrador","Visita"]);

  $msg = 0;
  if(isset($_GET['msg'])) {
    $msg = $_GET['msg'];
  }

  $pedidos = Pedido::getAll("WHERE estado!='Entregado' ORDER BY id desc");
  $clientes = Cliente::getAll();

?>
<style>
.tr-pedidos {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-truck"></i> <b>Pedidos de Clientes</b></h1>
  </div>
  <div>
  </div>
</div>
<div class="row mb-4 d-none">
  <div class="col-lg-2">
    <select class="form-control">
      <option>Solicitados</option>
      <option>Entregados</option>
    </select>
  </div>
  <div class="col-lg-3">
    <select class="form-control">
      <option>Todos los clientes</option>
      <option>Entregados</option>
    </select>
  </div>
  <div class="col-md-3">
  </div>
  <div class="col-md-3">
  </div>


</div>
<hr />
<?php
  if($msg == 2) {
?>
<div class="alert alert-danger" role="alert" >Pedido eliminado con &eacute;xito.</div>
<?php
  }
  foreach($pedidos as $pedido) {
    $cliente = new Cliente($pedido->id_clientes);
    $productos = PedidoProducto::getAll("WHERE id_pedidos='".$pedido->id."'");
?>
<div class="card w-100 shadow mb-3">
  <div class="card-body tr-pedidos" data-idpedidos="<?= $pedido->id; ?>">
  <div class="row">
    <div class="col-3">
      <h5 class="card-title mb-3 h5"><i class="fas fa-fw fa-truck"></i> PEDIDO #<?= $pedido->id; ?></h5>
    </div>
    <div class="col-3">
      Cliente: <b><?= $cliente->nombre; ?></b>
    </div>
    <div class="col-3">
      Creado <b><?= datetime2fechayhora($pedido->creada); ?></b>
    </div>
    <div class="col-2">
      Estado: <b><?= $pedido->estado; ?></b>
    </div>
    <div class="col-1">
      <button data-idpedidos="<?= $pedido->id; ?>" class="eliminar-pedido-btn btn btn-sm btn-danger">Eliminar</button>
    </div>
  </div>
    <table class="table table-striped mt-4" id="tr-desplegable-<?= $pedido->id; ?>" style="display: none;">
      <?php
      foreach($productos as $pp) {
        ?>
        <tr>
          <td>
            <?= $pp->tipo; ?>
          </td>
          <td>
            <?= $pp->cantidad; ?>
          </td>
          <td>
            <?= $pp->tipos_cerveza; ?>
          </td>
          <td>
            <?= $pp->estado; ?>
          </td>
          <td>
            <?php
            if($pp->estado == "Solicitado") {
            ?>
            <button class="btn btn-sm btn-secondary pedido-producto-btn" data-idpedidosproductos="<?= $pp->id; ?>" data-estado="En despacho">Marcar como En Despacho</button>
            <?php
          } else
          if($pp->estado == "En despacho") {
          ?>
          <button class="btn btn-sm btn-secondary pedido-producto-btn" data-idpedidosproductos="<?= $pp->id; ?>" data-estado="Entregado">Marcar como Entregado</button>
          <?php
        }
            ?>
          </td>
        <?php
      }
       ?>
    </table>
  </div>
</div>
<?php
  }
?>

<div class="modal fade" tabindex="-1" role="dialog" id="eliminar-modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Eliminar Pedido</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <center><h5>Desea eliminar este Pedido?<br/>Este paso no es reversible.</h5></center>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" id="eliminar-aceptar" data-bs-dismiss="modal">Eliminar</button>
        </div>
      </div>
    </div>
  </div>
<script>

  var id_eliminar;

$(document).on('click','.tr-pedidos',function(e){
  var id_pedidos = $(e.currentTarget).data('idpedidos');
  var id = '#tr-desplegable-' + id_pedidos;

  $(id).slideToggle(200);
});

$(document).on('click','.pedido-producto-btn',function(e){
  e.stopPropagation();
  var data = {
    'id': $(e.currentTarget).data('idpedidosproductos'),
    'estado': $(e.currentTarget).data('estado')
  }
  var url = './ajax/ajax_marcarDespachado.php';
  $.post(url,data,function(response){
    if(response.status!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      location.reload();
    }
  },"json").fail(function(){
    alert("No funciono");
  });
});

$(document).on('click','.eliminar-pedido-btn',function(e){
  e.preventDefault();
  id_eliminar = $(e.currentTarget).data('idpedidos');
  $('#eliminar-modal').modal('toggle');
})

$(document).on('click','#eliminar-aceptar',function(e){
  var data = {
    'id': id_eliminar,
    'modo': 'pedidos'
  }
  var url = './ajax/ajax_eliminarEntidad.php';
  $.post(url,data,function(response){
    if(response.status!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=pedidos&msg=2";
    }
  },"json").fail(function(){
    alert("No funciono");
  });
});

</script>
