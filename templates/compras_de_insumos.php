<?php

    $msg = 0;

    if(isset($_GET['msg'])) {
    $msg = $_GET['msg'];
    }

    $order = "desc";
    $order_link = "asc";
    if(isset($_GET['order'])) {
        if($_GET['order'] == "asc") {
        $order = "asc";
        $order_link = "desc";
        }
    }

    $show = "insumos";
    $table_hide = "items";
    if(isset($_GET['show'])) {
        if($_GET['show'] == "items") {
        $show = "items";
        $table_hide = "tipos";
        }
    }

    $cdi = CompraDeInsumo::getAll();


?>
<style>
.tr-compras_de_insumos {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-handshake"></i> <b>Compras de Insumos</b></h1>
  </div>
  <?php
    Widget::printWidget("insumos-menu-btn");
  ?>
</div>
<hr />
<?php
if($msg == 1) {
?>
<div class="alert alert-info" role="alert" >Insumo agregado con &eacute;xito.</div>
<?php
}
?>
<?php
if($msg == 2) {
?>
<div class="alert alert-danger" role="alert" >Insumo eliminado.</div>
<?php
}
?>
<table class="table table-hover table-striped table-sm" id="compras_de_insumos-table">
  <thead class="thead-dark">
    <tr>
      <th>
        ID
      </th>
      <th>
        Proveedor
      </th>
      <th>
        Monto
      </th>
      <th>
        Fecha
      </th>
    </tr>
  </thead>
  <tbody>
    <?php
      $total = 0;
      foreach($cdi as $compra_de_insumo) {
        $proveedor = new Proveedor($compra_de_insumo->id_proveedores);
    ?>
    <tr class="tr-compras_de_insumos" data-idcomprasdeinsumos="<?= $compra_de_insumo->id; ?>">
      <td>
        #<?= $compra_de_insumo->id; ?>
      </td>
      <td>
        <?= $proveedor->nombre; ?>
      </td>
      <td>
        $<?= number_format($compra_de_insumo->monto); ?>
      </td>
      <td>
        <?= date2fecha($compra_de_insumo->date); ?>
      </td>
    </tr>
    <?php
      }
    ?>
  </tbody>
</table>

<script>
var order_link = '<?= $order_link; ?>';
var show = '<?= $show; ?>';
var table_hide = '<?= $table_hide; ?>';

new DataTable('#compras_de_insumos-table', {
    language: {
        url: '//cdn.datatables.net/plug-ins/2.1.3/i18n/es-CL.json'
    },
    pageLength: 50,
    stateSave: true,
    paging: false
});

$(document).on('change','#vista', function(e) {
  $('#' + $(e.currentTarget).val() + '-table').show(200);
  show = $(e.currentTarget).val();
});



$(document).on('click','.tr-compras_de_insumos',function(e) {
    window.location.href = "./?s=detalle-compras_de_insumos&id=" + $(e.currentTarget).data('idcomprasdeinsumos');
});

$(document).on('click','.sort',function(e){
  var show = $(e.currentTarget).data('show');
  var sort = $(e.currentTarget).data('sort');
  window.location.href = "./?s=insumos&show=" + show + "&sort=" + sort + "&order=" + order_link;
});

</script>
