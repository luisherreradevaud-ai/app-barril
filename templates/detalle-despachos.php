<?php

  $msg = 0;
  if(isset($_GET['msg'])) {
    $msg = $_GET['msg'];
  }

  $id = isset($_GET['id']) ? $_GET['id'] : 0;
  $despacho = new Despacho($id);

  if(empty($despacho->id)) {
    header("Location: ./?s=central-despacho");
    exit;
  }

  $cliente = new Cliente($despacho->id_clientes);
  $repartidor = new Usuario($despacho->id_usuarios_repartidor);
  $productos = DespachoProducto::getAll("WHERE id_despachos='".$despacho->id."'");
  $usuario = $GLOBALS['usuario'];

  // Contar productos por tipo
  $total_barriles = 0;
  $total_cajas = 0;
  $total_envases = 0;
  foreach($productos as $p) {
    if($p->tipo == 'Barril') $total_barriles++;
    elseif($p->tipo == 'Caja') $total_cajas++;
    elseif($p->tipo == 'CajaEnvases') $total_envases++;
  }

?>

<div class="container-fluid p-0">

  <!-- Header -->
  <div class="d-sm-flex align-items-center justify-content-between mb-3">
    <div class="mb-2">
      <h1 class="h3 mb-0 text-gray-800">
        <b><i class="fas fa-fw fa-truck"></i> Despacho #<?= $despacho->id; ?></b>
      </h1>
    </div>
    <div>
      <?php $usuario->printReturnBtn(); ?>
    </div>
  </div>
  <hr />

  <?php if($msg == 1): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i> Despacho actualizado con exito.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  <?php endif; ?>

  <!-- Info Cards Row -->
  <div class="row mb-4">
    <!-- Estado -->
    <div class="col-12 col-md-6 col-xl-3 mb-3">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
              <?php if($despacho->estado == 'En despacho'): ?>
              <div class="bg-warning bg-opacity-10 rounded p-3">
                <i class="fas fa-truck fa-2x text-warning"></i>
              </div>
              <?php elseif($despacho->estado == 'Entregado'): ?>
              <div class="bg-success bg-opacity-10 rounded p-3">
                <i class="fas fa-check-circle fa-2x text-success"></i>
              </div>
              <?php else: ?>
              <div class="bg-secondary bg-opacity-10 rounded p-3">
                <i class="fas fa-info-circle fa-2x text-secondary"></i>
              </div>
              <?php endif; ?>
            </div>
            <div class="flex-grow-1 ms-3">
              <h6 class="text-muted mb-1">Estado</h6>
              <?php if($despacho->estado == 'En despacho'): ?>
              <span class="badge bg-warning text-dark fs-6"><?= $despacho->estado; ?></span>
              <?php elseif($despacho->estado == 'Entregado'): ?>
              <span class="badge bg-success fs-6"><?= $despacho->estado; ?></span>
              <?php else: ?>
              <span class="badge bg-secondary fs-6"><?= $despacho->estado; ?></span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Cliente -->
    <div class="col-12 col-md-6 col-xl-3 mb-3">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
              <div class="bg-primary bg-opacity-10 rounded p-3">
                <i class="fas fa-store fa-2x text-primary"></i>
              </div>
            </div>
            <div class="flex-grow-1 ms-3">
              <h6 class="text-muted mb-1">Cliente</h6>
              <p class="mb-0 fw-bold">
                <?= $despacho->id_clientes > 0 ? $cliente->nombre : 'Sin asignar'; ?>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Repartidor -->
    <div class="col-12 col-md-6 col-xl-3 mb-3">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
              <div class="bg-info bg-opacity-10 rounded p-3">
                <i class="fas fa-user-tie fa-2x text-info"></i>
              </div>
            </div>
            <div class="flex-grow-1 ms-3">
              <h6 class="text-muted mb-1">Repartidor</h6>
              <p class="mb-0 fw-bold">
                <?= $despacho->id_usuarios_repartidor > 0 ? $repartidor->nombre : 'Sin asignar'; ?>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Fecha -->
    <div class="col-12 col-md-6 col-xl-3 mb-3">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
              <div class="bg-secondary bg-opacity-10 rounded p-3">
                <i class="fas fa-calendar fa-2x text-secondary"></i>
              </div>
            </div>
            <div class="flex-grow-1 ms-3">
              <h6 class="text-muted mb-1">Fecha de Creacion</h6>
              <p class="mb-0 fw-bold"><?= datetime2fechayhora($despacho->creada); ?></p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Summary Badges -->
  <div class="card mb-4">
    <div class="card-body">
      <div class="d-flex flex-wrap gap-3">
        <div class="d-flex align-items-center">
          <i class="fas fa-boxes me-2 text-muted"></i>
          <span class="text-muted me-2">Total Productos:</span>
          <span class="badge bg-dark fs-6"><?= count($productos); ?></span>
        </div>
        <?php if($total_barriles > 0): ?>
        <div class="d-flex align-items-center">
          <i class="fas fa-beer me-2 text-secondary"></i>
          <span class="text-muted me-2">Barriles:</span>
          <span class="badge bg-secondary fs-6"><?= $total_barriles; ?></span>
        </div>
        <?php endif; ?>
        <?php if($total_cajas > 0): ?>
        <div class="d-flex align-items-center">
          <i class="fas fa-box me-2 text-info"></i>
          <span class="text-muted me-2">Cajas:</span>
          <span class="badge bg-info fs-6"><?= $total_cajas; ?></span>
        </div>
        <?php endif; ?>
        <?php if($total_envases > 0): ?>
        <div class="d-flex align-items-center">
          <i class="fas fa-boxes me-2 text-success"></i>
          <span class="text-muted me-2">Cajas de Envases:</span>
          <span class="badge bg-success fs-6"><?= $total_envases; ?></span>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Products Table -->
  <div>
    <div>
      <?php if(count($productos) == 0): ?>
      <div class="text-center py-5">
        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
        <h4 class="text-muted">No hay productos en este despacho</h4>
      </div>
      <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover" id="productos-table">
          <thead class="table-light">
            <tr>
              <th class="align-middle">Producto</th>
              <th class="align-middle">Tipo</th>
              <th class="align-middle d-none d-md-table-cell">Cantidad/Litraje</th>
              <th class="align-middle d-none d-lg-table-cell">Cerveza</th>
              <th class="align-middle">Estado</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($productos as $producto):
              $icon_class = '';
              $icon_bg = '';
              $nombre_producto = '';
              $cantidad_info = '';
              $cerveza_info = '-';

              if($producto->tipo == 'Barril') {
                $barril = new Barril($producto->id_barriles);
                $icon_class = 'fa-beer';
                $icon_bg = 'bg-secondary';
                $nombre_producto = 'Barril ' . $barril->codigo;
                $cantidad_info = $barril->litraje . 'L';
                if($barril->id_batches > 0) {
                  $batch = new Batch($barril->id_batches);
                  $receta = new Receta($batch->id_recetas);
                  $cerveza_info = $receta->nombre;
                }
              } elseif($producto->tipo == 'Caja') {
                $icon_class = 'fa-box';
                $icon_bg = 'bg-info';
                $nombre_producto = 'Caja - ' . $producto->codigo;
                $cantidad_info = $producto->cantidad . ' unidades';
                $cerveza_info = $producto->tipos_cerveza ?: '-';
              } elseif($producto->tipo == 'CajaEnvases') {
                $caja_envases = $producto->getCajaDeEnvases();
                $icon_class = 'fa-boxes';
                $icon_bg = 'bg-success';
                $nombre_producto = 'Caja de Envases';
                if($caja_envases) {
                  $nombre_producto .= ' ' . $caja_envases->codigo;
                  $cantidad_info = $caja_envases->cantidad_envases . ' envases';
                } else {
                  $cantidad_info = $producto->cantidad . ' envases';
                }
              }
            ?>
            <tr>
              <td>
                <div class="d-flex align-items-center">
                  <div class="p-2 rounded <?= $icon_bg; ?> bg-opacity-10 d-flex justify-content-center align-items-center me-3" style="width: 50px; height: 50px;">
                    <i class="fas <?= $icon_class; ?> fa-lg <?= str_replace('bg-', 'text-', $icon_bg); ?>"></i>
                  </div>
                  <div>
                    <p class="mb-0 fw-bold"><?= $nombre_producto; ?></p>
                    <small class="text-muted"><?= $producto->codigo; ?></small>
                  </div>
                </div>
              </td>
              <td class="align-middle">
                <?php if($producto->tipo == 'Barril'): ?>
                <span class="badge bg-secondary"><?= $producto->tipo; ?></span>
                <?php elseif($producto->tipo == 'Caja'): ?>
                <span class="badge bg-info"><?= $producto->tipo; ?></span>
                <?php elseif($producto->tipo == 'CajaEnvases'): ?>
                <span class="badge bg-success">Envases</span>
                <?php else: ?>
                <span class="badge bg-dark"><?= $producto->tipo; ?></span>
                <?php endif; ?>
              </td>
              <td class="align-middle d-none d-md-table-cell">
                <strong><?= $cantidad_info; ?></strong>
              </td>
              <td class="align-middle d-none d-lg-table-cell">
                <?= $cerveza_info; ?>
              </td>
              <td class="align-middle">
                <?php if($despacho->estado == 'Entregado'): ?>
                <span class="badge bg-success"><i class="fas fa-check me-1"></i>Entregado</span>
                <?php else: ?>
                <span class="badge bg-warning text-dark"><i class="fas fa-truck me-1"></i>En camino</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Boton Eliminar -->
  <?php if($usuario->nivel == "Administrador"): ?>
  <div class="mt-4">
    <button class="btn btn-outline-danger" id="eliminar-despacho-btn">
      <i class="fas fa-trash me-1"></i> Eliminar Despacho
    </button>
  </div>
  <?php endif; ?>

</div>

<!-- Modal Confirmar Eliminacion -->
<div class="modal fade" id="eliminar-despacho-modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Confirmar Eliminacion</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>¿Esta seguro de eliminar este despacho?</p>
        <p class="text-muted mb-0">Esta accion no se puede deshacer. Los barriles volveran a estado "En planta".</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="eliminar-despacho-confirmar-btn">
          <i class="fas fa-trash me-1"></i> Eliminar
        </button>
      </div>
    </div>
  </div>
</div>

<script>

// Abrir modal de eliminacion
$(document).on('click','#eliminar-despacho-btn',function(e){
  e.preventDefault();
  $('#eliminar-despacho-modal').modal('show');
});

// Confirmar eliminacion
$(document).on('click','#eliminar-despacho-confirmar-btn',function(e){
  var idDespacho = '<?= $despacho->id; ?>';
  var data = {
    'id': idDespacho,
    'modo': 'despachos'
  };
  var url = './ajax/ajax_eliminarEntidad.php';
  $.post(url, data, function(response){
    response = JSON.parse(response);
    if(response.status == "OK") {
      window.location.href = "./?s=central-despacho&msg=2";
    } else {
      alert("Error al eliminar el despacho");
      $('#eliminar-despacho-modal').modal('hide');
    }
  }).fail(function(){
    alert("Error de conexion");
    $('#eliminar-despacho-modal').modal('hide');
  });
});

</script>
