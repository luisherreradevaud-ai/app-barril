<?php
    $objs = Proyecto::getAll();
?>
<style>
.tr-obj {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-handshake"></i> <b>Proyectos</b></h1>
  </div>
  <a href="./?s=nuevo-proyectos" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i class="fas fa-fw fa-plus"></i> Nuevo Proyecto</a>
</div>
<hr />
<?php 
Msg::show(2,'Proyecto eliminado con &eacute;xito','danger');
?>
<table class="table table-hover table-striped table-sm" id="objs-table">
  <thead class="thead-dark">
    <tr>
      <th>
        Nombre
      </th>
      <th>
        Clasificaci&oacute;n
      </th>
      <th>
        Fecha inicio
      </th>
      <th>
        Fecha finalizaci&oacute;n
      </th>
      <th>
        Estado
      </th>
    </tr>
  </thead>
  <tbody>
    <?php
      $total = 0;
      foreach($objs as $obj) {
    ?>
    <tr class="tr-obj" data-idobj="<?= $obj->id; ?>">
      <td>
        <?= $obj->nombre; ?>
      </td>
      <td>
        <?= $obj->clasificacion; ?>
      </td>
      <td>
        <?= date2fecha($obj->date_inicio); ?>
      </td>
      <td>
        <?= date2fecha($obj->date_finalizacion); ?>
      </td>
      <td>
        <?= $obj->estado; ?>
      </td>
    </tr>
    <?php
      }
    ?>
  </tbody>
</table>
<script>
$(document).on('click','.tr-obj',function(e) {
    window.location.href = "./?s=detalle-proyectos&id=" + $(e.currentTarget).data('idobj');
});
</script>
