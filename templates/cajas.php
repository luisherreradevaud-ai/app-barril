<?php

  $cajas = Caja::getAll();

?>
<style>
.tr-cajas {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-folder"></i> <b>Cajas</b></h1>
  </div>
  <div>
    <div>
      <a href="./?s=nuevo-cajas" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm mb-2"><i class="fas fa-fw fa-plus"></i> Nueva Caja</a>
    </div>
  </div>
</div>
<hr />
<table class="table table-hover table-striped table-sm">
  <thead class="thead-dark">
    <tr>
      <th>
        ID
      </th>
      <th>
        Nombre
      </th>
      <th>
        Clasificaci&oacute;n
      </th>
      <th>
        Cantidad
      </th>
      <th>
        Estado
      </th>
    </tr>
  </thead>
  <tbody>
    <?php
      foreach($cajas as $caja) {
        $producto = new Producto($caja->id_productos);
    ?>
    <tr class="tr-cajas" data-idcajas="<?= $caja->id; ?>">
      <td>
        <?= $caja->id; ?>
      </td>
      <td>
        <?= $producto->nombre; ?>
      </td>
      <td>
        <?= $producto->clasificacion; ?>
      </td>
      <td>
        <?= $producto->cantidad; ?>
      </td>
      <td>
        <?= $caja->estado; ?>
      </td>
    </tr>
    <?php
      }
    ?>
  </tbody>
</table>
<script>
$(document).on('click','.tr-cajas',function(e){
  window.location.href = "./?s=detalle-cajas&id=" + $(e.currentTarget).data('idcajas');
});
</script>
