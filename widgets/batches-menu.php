<div class="d-sm-flex align-items-center justify-content-between mb-2">
  <div>
    <a href="./?s=nuevo-batches" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm mb-2"><i class="fas fa-fw fa-plus"></i> Generar Batch</a>
    <button class="d-sm-inline-block btn btn-sm btn-info shadow-sm mb-2" data-bs-toggle="modal" data-bs-target="#agregar-lupulizacion-modal"><i class="fas fa-fw fa-plus"></i> Agregar Lupulización</button>
    <button class="d-sm-inline-block btn btn-sm btn-info shadow-sm mb-2" data-bs-toggle="modal" data-bs-target="#agregar-traspasos-modal"><i class="fas fa-fw fa-plus"></i> Agregar Traspasos</button>
  </div>
  <?php
  if($_GET['s'] == 'batches') {
    ?>
    <a href="./?s=batches-finalizados" class="d-sm-inline-block btn btn-sm btn-secondary shadow-sm mb-2"><i class="fas fa-fw fa-check"></i> Batches Finalizados</a>
    <?php
  } else {
    ?>
    <a href="./?s=batches" class="d-sm-inline-block btn btn-sm btn-secondary shadow-sm mb-2">Batches en Proceso</a>
    <?php
  }
  ?>
</div>