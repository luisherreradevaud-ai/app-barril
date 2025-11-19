<?php

    $mes = date('m');
    if(validaIdExists($_GET,'mes')) {
    $mes = $_GET['mes'];
    }

    $ano = date('Y');
    if(validaIdExists($_GET,'ano')) {
    $ano = $_GET['ano'];
    }

    $batches = Batch::getAll("WHERE tipo='Batch' AND finalizacion_date!='0000-00-00'");

?>
<style>
.tr-batches {
  cursor: pointer;
}
</style>
<div class="mb-4">
  <h1 class="h2 mb-0 text-gray-800"><b>Batches en Proceso</b></h1>
</div>
<?php Widget::printWidget("batches-menu"); ?>
<?php 
Msg::show(2,'Batch eliminado con &eacute;xito','danger');
?>
<table class="table table-hover table-striped table-sm" id="batches-table">
  <thead class="thead-dark">
    <tr>
        <th>
        ID
      </th>
      <th>
        Nombre
      </th>
      <th>
        Etapa
      </th>
      <th>
        Creado
      </th>
      <th>
        Finalizado
      </th>
    </tr>
  </thead>
  <tbody>
    <?php
      $total = 0;
      foreach($batches as $batch) {
        $receta = new Receta($batch->id_recetas);
    ?>
    <tr class="tr-batches" data-idbatches="<?= $batch->id; ?>">
      <td>
        #<?= $batch->id; ?>
      </td>
      <td>
        <?= $batch->batch_nombre; ?>
      </td>
      <td>
        <?= $receta->nombre; ?>
      </td>
      <td>
        <?= datetime2fechayhora($batch->creada); ?>
      </td>
      <td>
        <?= date2fecha($batch->finalizacion_date); ?>
      </td>
    </tr>
    <?php
      }
    ?>
  </tbody>
</table>



<script>

$(document).on('click','.tr-batches',function(e) {
    window.location.href = "./?s=informe-batches&id=" + $(e.currentTarget).data('idbatches');
});


</script>
