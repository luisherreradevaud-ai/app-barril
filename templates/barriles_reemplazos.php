<?php

    $barriles_reemplazos = BarrilReemplazo::getAll();

?>
<style>
.tr-barriles_reemplazos {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-coins"></i> <b>Reemplazos de Barriles</b></h1>
  </div>
  <a href="./?s=reemplazo-barriles" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i class="fas fa-fw fa-plus"></i> Ingresar Reemplazo de Barril</a>
</div>
<hr />
<?php 
    Msg::show(1,'Reemplazo de Barril generado con &eacute;xito','success');
    Msg::show(2,'Reemplazo de Barril revertido y eliminado con &eacute;xito','danger');
?>
<table class="table table-hover table-striped table-sm mt-3">
  <thead class="thead-dark">
    <tr>
      <th>
        ID
      </th>
      <th>
        Fecha
      </th>
      <th>
        Barril Devuelto
      </th>
      <th>
        Barril Reemplazo
      </th>
      <th>
        Motivo
      </th>
    </tr>
  </thead>
  <tbody>
    <?php
      foreach($barriles_reemplazos as $reemplazo) {
        $barril_devuelto = new Barril($reemplazo->id_barriles_devuelto);
        $barril_reemplazo = new Barril($reemplazo->id_barriles_reemplazo);
    ?>
    <tr class="tr-barriles_reemplazos" data-idbarrilesreemplazos="<?= $reemplazo->id; ?>">
      <td>
        #<?= $reemplazo->id; ?>
      </td>
      <td>
        <?= datetime2fechayhora($reemplazo->creada); ?>
      </td>
      <td>
        <?= $barril_devuelto->codigo; ?>
      </td>
      <td>
        <?= $barril_reemplazo->codigo; ?>
      </td>
      <td>
        <?= $reemplazo->motivo; ?>
      </td>
    </tr>
    <?php
      }
    ?>
  </tbody>
</table>


<script>
$(document).on('click','.tr-barriles_reemplazos',function(e) {
    window.location.href = "./?s=detalle-barriles_reemplazos&id=" + $(e.currentTarget).data('idbarrilesreemplazos');
});

</script>
