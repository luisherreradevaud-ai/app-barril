<?php

  $despachos = Despacho::getAll("WHERE estado != 'eliminado' ORDER BY id desc");
  $usuario = $GLOBALS['usuario'];

  // Agrupar despachos por repartidor
  $despachos_por_repartidor = array();
  foreach($despachos as $despacho) {
    $id_repartidor = $despacho->id_usuarios_repartidor;
    if(!isset($despachos_por_repartidor[$id_repartidor])) {
      $despachos_por_repartidor[$id_repartidor] = array();
    }
    $despachos_por_repartidor[$id_repartidor][] = $despacho;
  }

?>

<style>
.toggle-icon {
  transition: transform 0.2s ease;
}
.despacho-row:hover {
  background-color: rgba(0,0,0,0.02);
}
.collapse-row td {
  background-color: transparent !important;
}
</style>

<div class="container-fluid p-0">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Central de Despacho</h1>
    <a href="./?s=nuevo-despachos" class="btn btn-primary">
      <i class="fas fa-plus me-1"></i> Nuevo Despacho
    </a>
  </div>

  <?php
  Msg::show(1, '<i class="fas fa-check-circle me-2"></i> Despacho creado con exito.', 'success');
  Msg::show(2, '<i class="fas fa-trash me-2"></i> Despacho eliminado con exito.', 'danger');
  ?>

  <?php if(count($despachos) == 0): ?>
  <div class="card">
    <div class="card-body text-center py-5">
      <i class="fas fa-truck fa-3x text-muted mb-3"></i>
      <h4 class="text-muted">No hay despachos registrados</h4>
      <p class="text-muted mb-3">Crea un nuevo despacho para comenzar</p>
      <a href="./?s=nuevo-despachos" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Crear Despacho
      </a>
    </div>
  </div>
  <?php else: ?>

  <?php foreach($despachos_por_repartidor as $id_repartidor => $despachos_repartidor):
    $repartidor = new Usuario($id_repartidor);
    $nombre_repartidor = $id_repartidor > 0 ? $repartidor->nombre : 'Sin Repartidor Asignado';
  ?>
  <div class="card mb-4">
    <div class="card-header">
      <div class="d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
          <i class="fas fa-user-tie me-2"></i><?= $nombre_repartidor; ?>
        </h5>
        <span class="badge bg-primary"><?= count($despachos_repartidor); ?> despacho(s)</span>
      </div>
    </div>
    <div class="card-body p-0">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-3"># Despacho</th>
            <th>Cliente</th>
            <th class="d-none d-md-table-cell">Productos</th>
            <th class="d-none d-lg-table-cell">Fecha</th>
            <th>Estado</th>
            <th class="text-end pe-3">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($despachos_repartidor as $despacho):
            $cliente = new Cliente($despacho->id_clientes);
            $productos = DespachoProducto::getAll("WHERE id_despachos='".$despacho->id."'");
            $total_productos = count($productos);
            $barriles = 0;
            $cajas = 0;
            $cajas_envases = 0;
            foreach($productos as $dp) {
              if($dp->tipo == 'Barril') $barriles++;
              elseif($dp->tipo == 'Caja') $cajas++;
              elseif($dp->tipo == 'CajaEnvases') $cajas_envases++;
            }
          ?>
          <tr class="despacho-row" data-bs-toggle="collapse" data-bs-target="#detalle-despacho-<?= $despacho->id; ?>" style="cursor: pointer;">
            <td class="ps-3">
              <i class="fas fa-chevron-right me-2 toggle-icon text-muted"></i>
              <strong>#<?= $despacho->id; ?></strong>
            </td>
            <td>
              <?php if($despacho->id_clientes > 0): ?>
                <i class="fas fa-store me-1 text-muted"></i><?= $cliente->nombre; ?>
              <?php else: ?>
                <span class="text-muted"><i class="fas fa-store me-1"></i>Sin asignar</span>
              <?php endif; ?>
            </td>
            <td class="d-none d-md-table-cell">
              <?php if($barriles > 0): ?>
                <span class="badge bg-secondary me-1"><i class="fas fa-beer me-1"></i><?= $barriles; ?> <?= $barriles == 1 ? 'Barril' : 'Barriles'; ?></span>
              <?php endif; ?>
              <?php if($cajas > 0): ?>
                <span class="badge bg-info me-1"><i class="fas fa-box me-1"></i><?= $cajas; ?> <?= $cajas == 1 ? 'Caja' : 'Cajas'; ?></span>
              <?php endif; ?>
              <?php if($cajas_envases > 0): ?>
                <span class="badge bg-success me-1"><i class="fas fa-boxes me-1"></i><?= $cajas_envases; ?> <?= $cajas_envases == 1 ? 'Caja Envases' : 'Cajas Envases'; ?></span>
              <?php endif; ?>
              <?php if($total_productos == 0): ?>
                <span class="text-muted">Sin productos</span>
              <?php endif; ?>
            </td>
            <td class="d-none d-lg-table-cell">
              <i class="fas fa-calendar me-1 text-muted"></i><?= datetime2fechayhora($despacho->creada); ?>
            </td>
            <td>
              <?php if($despacho->estado == 'En despacho'): ?>
                <span class="badge bg-warning text-dark">En despacho</span>
              <?php elseif($despacho->estado == 'Entregado'): ?>
                <span class="badge bg-success">Entregado</span>
              <?php else: ?>
                <span class="badge bg-secondary"><?= $despacho->estado; ?></span>
              <?php endif; ?>
            </td>
            <td class="text-end pe-3 acciones-cell">
              <a href="./?s=detalle-despachos&id=<?= $despacho->id; ?>" class="btn btn-sm btn-outline-primary me-1 btn-ver-detalle" title="Ver detalle">
                <i class="fas fa-eye"></i>
              </a>
              <?php if($usuario->nivel == "Administrador"): ?>
              <button data-iddespachos="<?= $despacho->id; ?>" class="eliminar-despacho-btn btn btn-sm btn-outline-danger" title="Eliminar">
                <i class="fas fa-trash"></i>
              </button>
              <?php endif; ?>
            </td>
          </tr>
          <!-- Fila expandible con detalle de productos -->
          <tr class="collapse-row">
            <td colspan="6" class="p-0 border-0">
              <div class="collapse" id="detalle-despacho-<?= $despacho->id; ?>">
                <div class="bg-light p-3">
                  <?php if($total_productos == 0): ?>
                  <p class="text-muted mb-0 text-center"><i class="fas fa-box-open me-2"></i>No hay productos en este despacho</p>
                  <?php else: ?>
                  <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0 bg-white">
                      <thead class="table-secondary">
                        <tr>
                          <th style="width: 50px;"></th>
                          <th>Producto</th>
                          <th>Tipo</th>
                          <th class="d-none d-md-table-cell">Cantidad/Litraje</th>
                          <th class="d-none d-lg-table-cell">Cerveza</th>
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
                            $icon_bg = 'text-secondary';
                            $nombre_producto = 'Barril ' . $barril->codigo;
                            $cantidad_info = $barril->litraje . 'L';
                            if($barril->id_batches > 0) {
                              $batch = new Batch($barril->id_batches);
                              $receta = new Receta($batch->id_recetas);
                              $cerveza_info = $receta->nombre;
                            }
                          } elseif($producto->tipo == 'Caja') {
                            $icon_class = 'fa-box';
                            $icon_bg = 'text-info';
                            $nombre_producto = 'Caja - ' . $producto->codigo;
                            $cantidad_info = $producto->cantidad . ' unidades';
                            $cerveza_info = $producto->tipos_cerveza ?: '-';
                          } elseif($producto->tipo == 'CajaEnvases') {
                            $caja_envases = $producto->getCajaDeEnvases();
                            $icon_class = 'fa-boxes';
                            $icon_bg = 'text-success';
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
                          <td class="text-center align-middle">
                            <i class="fas <?= $icon_class; ?> <?= $icon_bg; ?>"></i>
                          </td>
                          <td class="align-middle">
                            <strong><?= $nombre_producto; ?></strong>
                            <small class="text-muted d-block"><?= $producto->codigo; ?></small>
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
                          <td class="align-middle d-none d-md-table-cell"><?= $cantidad_info; ?></td>
                          <td class="align-middle d-none d-lg-table-cell"><?= $cerveza_info; ?></td>
                        </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                  <?php endif; ?>
                </div>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endforeach; ?>

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
        <p class="text-muted mb-0">Esta accion no se puede deshacer.</p>
        <input type="hidden" id="eliminar-despacho-id" value="">
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

// Rotar icono al expandir/colapsar
$(document).on('show.bs.collapse', '.collapse', function() {
  var targetId = $(this).attr('id');
  $('[data-bs-target="#' + targetId + '"] .toggle-icon').css('transform', 'rotate(90deg)');
});
$(document).on('hide.bs.collapse', '.collapse', function() {
  var targetId = $(this).attr('id');
  $('[data-bs-target="#' + targetId + '"] .toggle-icon').css('transform', 'rotate(0deg)');
});

// Evitar propagacion en celda de acciones
$(document).on('click', '.acciones-cell', function(e) {
  e.stopPropagation();
});

// Evitar propagacion en boton ver detalle
$(document).on('click', '.btn-ver-detalle', function(e) {
  e.stopPropagation();
});

// Abrir modal de confirmacion
$(document).on('click','.eliminar-despacho-btn',function(e){
  e.preventDefault();
  e.stopPropagation();
  var idDespacho = $(this).data('iddespachos');
  $('#eliminar-despacho-id').val(idDespacho);
  $('#eliminar-despacho-modal').modal('show');
});

// Confirmar eliminacion
$(document).on('click','#eliminar-despacho-confirmar-btn',function(e){
  var idDespacho = $('#eliminar-despacho-id').val();
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
