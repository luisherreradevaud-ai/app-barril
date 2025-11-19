<?php

  $msg = 0;
  if(isset($_GET['msg'])) {
  $msg = $_GET['msg'];
  }

  $tdi = TipoDeInsumo::getAll("ORDER BY nombre asc");

  $query = '';

  if(validaIdExists($_GET,'id_tipos_de_insumos')) {
    $tipo_de_insumo_seleccionado = $_GET['id_tipos_de_insumos'];
  } else {
    $tipo_de_insumo_seleccionado = $tdi[0]->id;
  }
    
  if($query == '') {
    $query .= 'WHERE id_tipos_de_insumos="'.$tipo_de_insumo_seleccionado.'"';
  } else {
    $query .= 'AND id_tipos_de_insumos="'.$tipo_de_insumo_seleccionado.'"';
  }

  $insumos = Insumo::getAll($query);


?>
<style>
.tr-insumos {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-handshake"></i> <b>Inventario de Insumos</b></h1>
  </div>
  <?php
    Widget::printWidget("insumos-menu-btn");
  ?>
</div>
<div class="d-flex justify-content-start w-100 mb-4"  style="overflow-x: scroll">
<?php

  foreach($tdi as $tipo_de_insumo) {
    ?>
    <a  href="./?s=insumos&id_tipos_de_insumos=<?= $tipo_de_insumo->id; ?>" class="btn btn-outline-secondary btn-sm me-2 <?= ($tipo_de_insumo_seleccionado == $tipo_de_insumo->id) ? 'active' : ''; ?>">
      <?= $tipo_de_insumo->nombre; ?>
    </a>
    <?php
  }

?>
</div>
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
<table class="table table-hover table-striped table-sm" id="insumos-table">
  <thead class="thead-dark">
    <tr>
      <th>
        Insumo
      </th>
      <th>
        Tipo de Insumo
      </th>
      <th>
        Bodega
      </th>
    </tr>
  </thead>
  <tbody>
    <?php
      $total = 0;
      foreach($insumos as $insumo) {
        $tdi = new TipoDeInsumo($insumo->id_tipos_de_insumos);
    ?>
    <tr class="tr-insumos" data-idinsumos="<?= $insumo->id; ?>">
      <td>
        <?= $insumo->nombre; ?>
      </td>
      <td>
        <?= $tdi->nombre; ?>
      </td>
      <td>
        <?= $insumo->bodega." ".$insumo->unidad_de_medida; ?>
      </td>
    </tr>
    <?php
      }
    ?>
  </tbody>
</table>

<script>

new DataTable('#insumos-table', {
    language: {
        url: '//cdn.datatables.net/plug-ins/2.1.3/i18n/es-CL.json'
    },
    pageLength: 50,
    stateSave: true,
    paging: false
});

$(document).on('click','.tr-insumos',function(e) {
    window.location.href = "./?s=detalle-insumos&id=" + $(e.currentTarget).data('idinsumos');
});



</script>
