<?php

  $msg = 0;
  if(isset($_GET['msg'])) {
    $msg = $_GET['msg'];
  }

  $usuario = $GLOBALS['usuario'];
  $cliente = new Cliente($usuario->id_clientes);
  $pedidos = Pedido::getAll("WHERE id_clientes='".$cliente->id."' ORDER BY id desc");

?>
<style>
.tr-pedidos {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h4 mb-0 text-gray-800"><b><?= $cliente->nombre; ?></b></h1>
    <h1 class="h5 mb-0 text-gray-800">Pedidos</h1>
  </div>
  <div>
    <div>
      <a href="./?s=central-clientes-nuevo-pedido&id=<?= $cliente->id; ?>" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm mb-2"><i class="fas fa-fw fa-plus"></i> Nuevo Pedido</a>
    </div>
  </div>
</div>
<hr />
<?php
if($msg == 1) {
?>
<div class="alert alert-info" role="alert" >Pedido ingresado con &eacute;xito.</div>
<?php
}
  foreach($pedidos as $pedido) {
    $productos = PedidoProducto::getAll("WHERE id_pedidos='".$pedido->id."'");
?>
<div class="card w-100 shadow mb-5">
  <div class="card-body">
    <h5 class="card-title mb-3 h5"><i class="fas fa-fw fa-truck"></i> PEDIDO #<?= $pedido->id; ?></h5>
    <table>
      <tr>
        <td>
          Creado:
        </td>
        <td>
          <b>
            <?= datetime2fechayhora($pedido->creada); ?>
          </b>
        </td>
      </tr>
      <tr>
        <td>
          Estado:
        </td>
        <td>
          <b>
            <?= $pedido->estado; ?>
          </b>
        </td>
      </tr>
    </table>
    <br />
    <table class="table table-striped mt-3">
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
        <?php

      }
       ?>
    </table>
  </div>
</div>
<?php
  }
?>
<script>
$(document).on('click','.tr-pedidos',function(e){
  window.location.href = "./?s=detalle-pedidos&id=" + $(e.currentTarget).data('idpedidos');
})
</script>
