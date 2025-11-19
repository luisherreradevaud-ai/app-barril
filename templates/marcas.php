<?php

  $marcas = Marca::getAll();

?>
<style>
.tr-marcas {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-coins"></i> <b>Marcas</b></h1>
  </div>
  <div>
    <div>
      <a href="./?s=nuevo-marcas" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm mb-2"><i class="fas fa-fw fa-plus"></i> Nueva Marca</a>
    </div>
  </div>
</div>
<hr />
<table class="table table-hover table-striped table-sm">
  <thead class="thead-dark">
    <tr>
      <th>
        Nombre
      </th>
      <th>
        Visible
      </th>
    </tr>
  </thead>
  <tbody>
    <?php
      foreach($marcas as $marca) {
    ?>
    <tr class="tr-marcas" data-idmarcas="<?= $marca->id; ?>">
      <td>
        <?= $marca->nombre; ?>
      </td>
      <td>
        <?php
          print $marca->visible ? "Si" : "No";
        ?>
      </td>
    </tr>
    <?php
      }
    ?>
  </tbody>
</table>
<script>
$(document).on('click','.tr-marcas',function(e){
  window.location.href = "./?s=detalle-marcas&id=" + $(e.currentTarget).data('idmarcas');
})
</script>
