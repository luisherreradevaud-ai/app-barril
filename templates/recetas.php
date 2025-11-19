<?php
    $recetas = Receta::getAll("ORDER BY nombre asc");
?>
<style>
.tr-recetas {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-handshake"></i> <b>Recetas</b></h1>
  </div>
  <div>
    <a href="./?s=batches" class="d-sm-inline-block btn btn-sm btn-secondary shadow-sm mb-2">Batches</a>
    <a href="./?s=nuevo-recetas" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm mb-2"><i class="fas fa-fw fa-plus"></i> Nueva Receta</a>
  </div>
</div>
<hr />
<?php 
Msg::show(2,'Receta eliminada con &eacute;xito','danger');
?>
<table class="table table-hover table-striped table-sm" id="recetas-table">
  <thead class="thead-dark">
    <tr>
      <th>
        Nombre
      </th>
      <th>
        Clasificación
      </th>
      <th>
        Cantidad Resultante
      </th>
    </tr>
  </thead>
  <tbody>
    <?php
      $total = 0;
      foreach($recetas as $receta) {
    ?>
    <tr class="tr-recetas" data-idrecetas="<?= $receta->id; ?>">
      <td>
        <b><?= $receta->nombre; ?></b>
      </td><td>
        <?= $receta->clasificacion; ?>
      </td><td>
        <?= $receta->litros; ?> Litros
      </td>
    </tr>
    <?php
      }
    ?>
  </tbody>
</table>
<script>
$(document).on('click','.tr-recetas',function(e) {
    window.location.href = "./?s=detalle-recetas&id=" + $(e.currentTarget).data('idrecetas');
});
</script>
