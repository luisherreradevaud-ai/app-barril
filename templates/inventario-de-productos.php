<?php

    $usuario = $GLOBALS['usuario'];

  $batches_activos_fermentacion = BatchActivo::getAll("WHERE estado='Fermentación' AND litraje!=0");
  $ba_fermentacion = [];
  foreach($batches_activos_fermentacion as $baf) {
    $ba_fermentacion[$baf->id_batches][$baf->id] = $baf;
  }

  $batches_activos_maduracion = BatchActivo::getAll("WHERE estado='Maduración' AND litraje>0 ORDER BY id_batches asc");
  $ba_maduracion = [];
  foreach($batches_activos_maduracion as $baf) {
    $ba_maduracion[$baf->id_batches][$baf->id] = $baf;
  }

  $batches_activos_ferminacion_inox = BatchActivo::getAll("INNER JOIN activos ON activos.id = batches_activos.id_activos WHERE (LOWER(activos.nombre) LIKE '%inox%' OR LOWER(activos.marca) LIKE '%inox%') AND batches_activos.litraje>0 ORDER BY batches_activos.id_batches asc");
  $ba_ferminacion_inox = [];
  foreach($batches_activos_ferminacion_inox as $bafi) {
    $ba_ferminacion_inox[$bafi->id_batches][$bafi->id] = $bafi;
  }

  $batches_para_carga = array_merge($batches_activos_maduracion, $batches_activos_ferminacion_inox);

  $barriles_en_planta = Barril::getAll("WHERE id_batches!=0 AND estado='En planta' AND clasificacion='Cerveza' ORDER BY id_batches desc, codigo asc");
  $barriles_en_terreno = Barril::getAll("WHERE id_batches!=0 AND estado='En despacho' AND clasificacion='Cerveza' ORDER BY codigo asc");
  $barriles_disponibles = Barril::getAll("WHERE litros_cargados!=litraje AND estado='En planta' AND clasificacion='Cerveza' ORDER BY codigo asc");

  $activos_traspaso = [];
  $activos_traspaso_disponibles = Activo::getAll("WHERE activos.id_batches=0 AND activos.clase='Fermentador' ORDER BY activos.codigo asc");

  // Cargar traspasos para poder revertirlos (indexado por id_activos destino)
  $traspasos_por_destino = array();
  $todos_traspasos = BatchTraspaso::getAll("ORDER BY id DESC");
  foreach($todos_traspasos as $tr) {
    if(!isset($traspasos_por_destino[$tr->id_fermentadores_final])) {
      $traspasos_por_destino[$tr->id_fermentadores_final] = $tr;
    }
  }

  $batches = Batch::getAll('ORDER BY batch_nombre desc');

  $activos_disponibles = Activo::getAll("WHERE clase='Fermentador' AND id_batches='0' order by nombre asc");
  $activos = Activo::getAll("WHERE clase='Fermentador' order by nombre asc");

  $despachos = Despacho::getAll("ORDER BY id desc");

  // Datos para sistema de envases (latas y botellas)
  $formatos_de_envases = FormatoDeEnvases::getAllActivos();
  $formatos_latas = FormatoDeEnvases::getAllByTipo('Lata');
  $formatos_botellas = FormatoDeEnvases::getAllByTipo('Botella');

  $batches_de_envases = BatchDeEnvases::getAllConDisponibles();
  $batches_de_latas = BatchDeEnvases::getAllConDisponiblesByTipo('Lata');
  $batches_de_botellas = BatchDeEnvases::getAllConDisponiblesByTipo('Botella');

  $cajas_de_envases_en_planta = CajaDeEnvases::getCajasEnPlanta();
  $cajas_de_latas_en_planta = CajaDeEnvases::getCajasEnPlantaByTipo('Lata');
  $cajas_de_botellas_en_planta = CajaDeEnvases::getCajasEnPlantaByTipo('Botella');

  $productos_de_envases = Producto::getProductosDeEnvases();
  $productos_de_latas = Producto::getProductosDeEnvases('Lata');
  $productos_de_botellas = Producto::getProductosDeEnvases('Botella');

  // Barriles llenos disponibles para envasar
  $barriles_para_envasar = Barril::getAll("WHERE id_batches!=0 AND estado='En planta' AND clasificacion='Cerveza' AND litros_cargados>0 ORDER BY id_batches desc, codigo asc");

  $msg_content = '';
  if(isset($_GET['msg_content'])) {
    $msg_content = $_GET['msg_content'];
  }

?>
<style>
.tr-insumos {
  cursor: pointer;
}
</style>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
  <div class="mb-2">
    <h1 class="h2 mb-0 text-gray-800"><b>Inventario de Productos</b></h1>
  </div>
</div>

<?php 
    Msg::show(1,$msg_content,'info');
    Msg::show(2,'Traspaso realizado con éxito.','info');
?>

<div class="row mt-3">
    <div class="col-md-6 mb-3">
        <div class="card shadow mb-3">
            <div class="card-header">
                <div class="card-title d-flex justify-content-between">
                    <h1 class="h3 mb-0 text-gray-800">Fermentación</h1>
                    <button class="btn btn-sm btn-outline-primary"  data-bs-target="#agregar-fermentadores-modal" data-bs-toggle="modal" id="agregar-fermentadores-btn">
                        Agregar Fermentador
                    </button>
                </div>
            </div>
            <div class="card-body">
        <?php
        foreach($ba_fermentacion as $bas) {
            $batch = new Batch(end($bas)->id_batches);
            $receta = new Receta($batch->id_recetas);
            ?>
            
                    <div class="accordion mb-3" id="accordionExample1<?= $batch->id; ?>">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingOne1<?= $batch->id; ?>">
                                <button class="accordion-button collapsed fs-5" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne-1-<?= $batch->id; ?>" aria-expanded="true" aria-controls="collapseOne-1-<?= $batch->id; ?>" style="height: 30px">
                                    <div class="fw-bold me-2">
                                        Batch #<?= $batch->batch_nombre; ?> <?= $receta->nombre; ?>
                                    </div>
                                    <div class="ms-3">
                                        <?= datetime2fechayhora($batch->creada); ?>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapseOne-1-<?= $batch->id; ?>" class="accordion-collapse collapse" aria-labelledby="headingOne1<?= $batch->id; ?>" data-bs-parent="#accordionExample1<?= $batch->id; ?>">
                                <div class="accordion-body p-0">
                                    <table class="table table-striped table-sm my-0">
                                        <thead>
                                            <th class="ps-3">
                                                Fermentador
                                            </th>
                                            <th></th>
                                        </thead>
                                        <tbody>
                                        <?php
                                        foreach($bas as $ba) {
                                            $activo = new Activo($ba->id_activos);
                                            $activos_traspaso[] = $activo;
                                            ?>
                                            <tr>
                                                <td class="px-3">
                                                    <div class="w-100 d-flex justify-content-between">
                                                        <div>
                                                            <?= $activo->codigo; ?>
                                                        </div>
                                                        <div>
                                                            <?= $ba->litraje; ?> L
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-end">
                                                    <?php
                                                    if($usuario->nivel == 'Administrador') {
                                                    ?>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-danger eliminar-fermentador-btn"
                                                        data-idbatches="<?= $ba->id_batches; ?>"
                                                        data-idactivos="<?= $ba->id_activos; ?>"
                                                        data-idbatchesactivos="<?= $ba->id; ?>"
                                                        title="Eliminar fermentador"
                                                    >
                                                        <i class="fas fa-undo"></i>
                                                    </button>
                                                    <?php
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
                }
                ?>
                </div>
            </div>
    
    </div>
    <div class="col-md-6 mb-3">
       <div class="card shadow mb-3">
            <div class="card-header">
                <div class="card-title d-flex justify-content-between">
                    <h1 class="h3 mb-0 text-gray-800">Maduración</h1>
                    <button class="btn btn-sm btn-outline-primary" id="traspasar-btn">
                        Traspasar
                    </button>
                </div>
            </div>
            <div class="card-body">
        <?php
        foreach($ba_maduracion as $bas) {
            $batch = new Batch(end($bas)->id_batches);
            $receta = new Receta($batch->id_recetas);
            ?>
            
                    <div class="accordion mb-3" id="accordionExample2<?= $batch->id; ?>">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingOne2<?= $batch->id; ?>">
                                <button class="accordion-button collapsed fs-5" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne-2-<?= $batch->id; ?>" aria-expanded="true" aria-controls="collapseOne-2-<?= $batch->id; ?>" style="height: 30px">
                                    <div class="fw-bold">
                                        Batch #<?= $batch->batch_nombre; ?> <?= $receta->nombre; ?>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapseOne-2-<?= $batch->id; ?>" class="accordion-collapse collapse" aria-labelledby="headingOne2<?= $batch->id; ?>" data-bs-parent="#accordionExample2<?= $batch->id; ?>">
                                <div class="accordion-body p-0">
                                    <table class="table table-striped table-sm my-0">
                                        <thead>
                                            <th class="ps-3">
                                                Fermentador
                                            </th>
                                            <th>
                                                Fecha
                                            </th>
                                            <th>
                                                
                                            </th>
                                        </thead>
                                        <tbody>
                                        <?php
                                        foreach($bas as $ba) {
                                            $activo = new Activo($ba->id_activos);
                                            $activos_traspaso[] = $activo;
                                            ?>
                                            <tr>
                                                <td class="ps-3">
                                                    <?= $activo->codigo; ?>
                                                </td>
                                                <td class="ps-3">
                                                    <?= $ba->creada; ?>
                                                </td>
                                                <td class="text-end">
                                                    <?php
                                                    if($usuario->nivel == 'Administrador') {
                                                        // Verificar si hay un traspaso que se pueda revertir
                                                        $traspaso_revertible = isset($traspasos_por_destino[$ba->id_activos]) ? $traspasos_por_destino[$ba->id_activos] : null;
                                                        if($traspaso_revertible) {
                                                            $activo_origen_traspaso = new Activo($traspaso_revertible->id_fermentadores_inicio);
                                                        ?>
                                                        <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-danger revertir-traspaso-btn"
                                                        data-idtraspaso="<?= $traspaso_revertible->id; ?>"
                                                        data-origen="<?= $activo_origen_traspaso->codigo; ?>"
                                                        data-destino="<?= $activo->codigo; ?>"
                                                        title="Revertir traspaso desde <?= $activo_origen_traspaso->codigo; ?>"
                                                        >
                                                            <i class="fas fa-undo"></i>
                                                        </button>
                                                        <?php
                                                        }
                                                        ?>
                                                        <?php
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
                }
                ?>
                </div>
            </div>
    </div>
    <div class="col-md-6 mb-3">
        <?php
        if(strtolower($GLOBALS['usuario']->nivel) == strtolower('Administrador') || strtolower($GLOBALS['usuario']->nivel) == strtolower('Jefe de Planta')) {
        ?>
        <div class="card">
            <div class="card-header">
                <div class="card-title d-flex justify-content-between">
                    <h1 class="h3 mb-0 text-gray-800">Barriles llenos en Planta</h1>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#llenar-barriles-modal" id="llenar-barriles-btn"> 
                        Llenar Barril
                    </button>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>
                                Barril
                            </th>
                            <th>
                                Batch
                            </th>
                            <th>
                                Cargado
                            </th>
                            <th>
                                Activo
                            </th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach($barriles_en_planta as $ba) {
                            $batch = new Batch($ba->id_batches);
                            $receta = new Receta($batch->id_recetas);
                            $activo = new Activo($ba->id_activos);
                            ?>
                            <tr>
                                <td>
                                    <?= $ba->codigo; ?>
                                </td>
                                <td>
                                    <?= $receta->nombre; ?> #<?= $batch->batch_nombre; ?> <?= date2fecha($batch->batch_date); ?>
                                </td>
                                <td>
                                    <?= $ba->litros_cargados; ?>L
                                </td>
                                <td>
                                    <?= $activo->nombre; ?>
                                </td>
                                <td class="text-end">
                                    <?php
                                    if($usuario->nivel == 'Administrador') {
                                    ?>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-danger revertir-llenado-barril-btn"
                                        data-idbarril="<?= $ba->id; ?>"
                                        data-codigo="<?= $ba->codigo; ?>"
                                        data-litros="<?= $ba->litros_cargados; ?>"
                                        title="Revertir llenado"
                                    >
                                        <i class="fas fa-undo"></i>
                                    </button>
                                    <?php
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
        }
        ?>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header">
                <div class="card-title d-flex justify-content-between">
                    <h1 class="h3 mb-0 text-gray-800">Despachos</h1>
                    <a href="./?s=nuevo-despachos" class="d-sm-inline-block btn btn-sm btn-outline-primary shadow-sm mb-2"><i class="fas fa-fw fa-plus"></i> Nuevo Despacho</a>

                </div>
            </div>
            <div class="card-body">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>
                                Batch
                            </th>
                            <th>
                                Barril
                            </th>
                            <th>
                                Ubicación
                            </th>
                            <th>
                                Estado
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach($barriles_en_terreno as $ba) {
                            $batch = new Batch($ba->id_batches);
                            $receta = new Receta($batch->id_recetas);
                            $cliente = new Cliente($ba->id_clientes);
                            ?>
                            <tr>
                                <td>
                                    <?= $receta->nombre; ?> #<?= $batch->batch_nombre; ?> <?= date2fecha($batch->batch_date); ?>
                                </td>
                                <td>
                                    <?= $ba->codigo; ?>
                                </td>
                                <td>
                                    <?= $cliente->nombre; ?>
                                </td>
                                <td>
                                    <?= $ba->estado; ?>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Card: Envases en Planta (Latas y Botellas) -->
    <div class="col-md-6 mb-3">
        <?php
        if(strtolower($GLOBALS['usuario']->nivel) == strtolower('Administrador') || strtolower($GLOBALS['usuario']->nivel) == strtolower('Jefe de Planta')) {
        ?>
        <div class="card shadow">
            <div class="card-header">
                <div class="card-title d-flex justify-content-between">
                    <h1 class="h3 mb-0 text-gray-800">Envases en Planta</h1>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#envasar-modal" id="envasar-btn">
                        Envasar
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Tabs Latas/Botellas -->
                <ul class="nav nav-tabs nav-fill mb-3" id="envases-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="envases-latas-tab" data-bs-toggle="tab" data-bs-target="#envases-latas-content" type="button" role="tab">
                            <i class="fas fa-fw fa-wine-bottle"></i> Latas (<?= count($batches_de_latas); ?>)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="envases-botellas-tab" data-bs-toggle="tab" data-bs-target="#envases-botellas-content" type="button" role="tab">
                            <i class="fas fa-fw fa-wine-glass"></i> Botellas (<?= count($batches_de_botellas); ?>)
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="envases-tabs-content">
                    <!-- Tab Latas -->
                    <div class="tab-pane fade show active" id="envases-latas-content" role="tabpanel">
                        <?php if(count($batches_de_latas) > 0) { ?>
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Disp.</th>
                                    <th>Batch</th>
                                    <th>Formato</th>
                                    <th>Origen</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach($batches_de_latas as $bdl) {
                                    $batch = $bdl->getBatch();
                                    $receta = $bdl->getReceta();
                                    $formato = $bdl->getFormatoDeEnvases();
                                    $activo = $bdl->getActivo();
                                    $barril = $bdl->getBarril();
                                    $origen = $activo ? $activo->codigo : ($barril ? $barril->codigo : '-');
                                    $origen_tipo = $activo ? 'fermentador' : 'barril';
                                ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-success fs-6"><?= $bdl->envases_disponibles; ?></span>
                                    </td>
                                    <td>
                                        <?= $receta ? $receta->nombre : ''; ?> #<?= $batch ? $batch->batch_nombre : ''; ?>
                                    </td>
                                    <td>
                                        <?= $formato ? $formato->nombre : ''; ?>
                                    </td>
                                    <td>
                                        <?= $origen; ?>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-danger revertir-envasado-btn"
                                            data-id="<?= $bdl->id; ?>"
                                            data-tipo="Lata"
                                            data-envases="<?= $bdl->envases_disponibles; ?>"
                                            data-total="<?= $bdl->cantidad_de_envases; ?>"
                                            data-origen="<?= $origen; ?>"
                                            data-origen-tipo="<?= $origen_tipo; ?>"
                                            data-volumen="<?= $bdl->volumen_origen_ml; ?>"
                                            data-batch="<?= $receta ? $receta->nombre : ''; ?> #<?= $batch ? $batch->batch_nombre : ''; ?>">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                        <?php } else { ?>
                        <p class="text-muted mb-0">No hay latas disponibles.</p>
                        <?php } ?>
                        <hr class="my-3">
                        <button class="btn btn-primary btn-sm w-100" data-bs-toggle="modal" data-bs-target="#crear-cajas-modal" id="crear-cajas-latas-btn" data-tipo="Lata" <?= count($batches_de_latas) == 0 ? 'disabled' : ''; ?>>
                            <i class="fas fa-fw fa-box"></i> Crear Cajas de Latas
                        </button>
                    </div>

                    <!-- Tab Botellas -->
                    <div class="tab-pane fade" id="envases-botellas-content" role="tabpanel">
                        <?php if(count($batches_de_botellas) > 0) { ?>
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Disp.</th>
                                    <th>Batch</th>
                                    <th>Formato</th>
                                    <th>Origen</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach($batches_de_botellas as $bdb) {
                                    $batch = $bdb->getBatch();
                                    $receta = $bdb->getReceta();
                                    $formato = $bdb->getFormatoDeEnvases();
                                    $activo = $bdb->getActivo();
                                    $barril = $bdb->getBarril();
                                    $origen = $activo ? $activo->codigo : ($barril ? $barril->codigo : '-');
                                    $origen_tipo = $activo ? 'fermentador' : 'barril';
                                ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-info fs-6"><?= $bdb->envases_disponibles; ?></span>
                                    </td>
                                    <td>
                                        <?= $receta ? $receta->nombre : ''; ?> #<?= $batch ? $batch->batch_nombre : ''; ?>
                                    </td>
                                    <td>
                                        <?= $formato ? $formato->nombre : ''; ?>
                                    </td>
                                    <td>
                                        <?= $origen; ?>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-danger revertir-envasado-btn"
                                            data-id="<?= $bdb->id; ?>"
                                            data-tipo="Botella"
                                            data-envases="<?= $bdb->envases_disponibles; ?>"
                                            data-total="<?= $bdb->cantidad_de_envases; ?>"
                                            data-origen="<?= $origen; ?>"
                                            data-origen-tipo="<?= $origen_tipo; ?>"
                                            data-volumen="<?= $bdb->volumen_origen_ml; ?>"
                                            data-batch="<?= $receta ? $receta->nombre : ''; ?> #<?= $batch ? $batch->batch_nombre : ''; ?>">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                        <?php } else { ?>
                        <p class="text-muted mb-0">No hay botellas disponibles.</p>
                        <?php } ?>
                        <hr class="my-3">
                        <button class="btn btn-info btn-sm w-100" data-bs-toggle="modal" data-bs-target="#crear-cajas-modal" id="crear-cajas-botellas-btn" data-tipo="Botella" <?= count($batches_de_botellas) == 0 ? 'disabled' : ''; ?>>
                            <i class="fas fa-fw fa-box"></i> Crear Cajas de Botellas
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>

    <!-- Card: Cajas de Envases en Planta -->
    <div class="col-md-6 mb-3">
        <?php
        if(strtolower($GLOBALS['usuario']->nivel) == strtolower('Administrador') || strtolower($GLOBALS['usuario']->nivel) == strtolower('Jefe de Planta')) {
        ?>
        <div class="card shadow">
            <div class="card-header">
                <div class="card-title d-flex justify-content-between">
                    <h1 class="h3 mb-0 text-gray-800">Cajas en Planta</h1>
                    <span class="badge bg-primary fs-6"><?= count($cajas_de_envases_en_planta); ?></span>
                </div>
            </div>
            <div class="card-body">
                <!-- Tabs Latas/Botellas -->
                <ul class="nav nav-tabs nav-fill mb-3" id="cajas-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="cajas-latas-tab" data-bs-toggle="tab" data-bs-target="#cajas-latas-content" type="button" role="tab">
                            <i class="fas fa-fw fa-wine-bottle"></i> Latas (<?= count($cajas_de_latas_en_planta); ?>)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="cajas-botellas-tab" data-bs-toggle="tab" data-bs-target="#cajas-botellas-content" type="button" role="tab">
                            <i class="fas fa-fw fa-wine-glass"></i> Botellas (<?= count($cajas_de_botellas_en_planta); ?>)
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="cajas-tabs-content">
                    <!-- Tab Cajas Latas -->
                    <div class="tab-pane fade show active" id="cajas-latas-content" role="tabpanel">
                        <?php if(count($cajas_de_latas_en_planta) > 0) { ?>
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Codigo</th>
                                    <th>Producto</th>
                                    <th>Cant.</th>
                                    <th>Fecha</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach($cajas_de_latas_en_planta as $caja) {
                                    $producto = $caja->getProducto();
                                ?>
                                <tr>
                                    <td><?= $caja->codigo; ?></td>
                                    <td><?= $producto ? $producto->nombre : '-'; ?></td>
                                    <td><?= $caja->cantidad_envases; ?></td>
                                    <td><?= datetime2fechayhora($caja->creada); ?></td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-danger eliminar-caja-btn"
                                            data-id="<?= $caja->id; ?>"
                                            data-codigo="<?= $caja->codigo; ?>"
                                            data-producto="<?= $producto ? $producto->nombre : '-'; ?>"
                                            data-cantidad="<?= $caja->cantidad_envases; ?>"
                                            title="Revertir caja">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                        <?php } else { ?>
                        <p class="text-muted mb-0">No hay cajas de latas en planta.</p>
                        <?php } ?>
                    </div>

                    <!-- Tab Cajas Botellas -->
                    <div class="tab-pane fade" id="cajas-botellas-content" role="tabpanel">
                        <?php if(count($cajas_de_botellas_en_planta) > 0) { ?>
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Codigo</th>
                                    <th>Producto</th>
                                    <th>Cant.</th>
                                    <th>Fecha</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach($cajas_de_botellas_en_planta as $caja) {
                                    $producto = $caja->getProducto();
                                ?>
                                <tr>
                                    <td><?= $caja->codigo; ?></td>
                                    <td><?= $producto ? $producto->nombre : '-'; ?></td>
                                    <td><?= $caja->cantidad_envases; ?></td>
                                    <td><?= datetime2fechayhora($caja->creada); ?></td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-danger eliminar-caja-btn"
                                            data-id="<?= $caja->id; ?>"
                                            data-codigo="<?= $caja->codigo; ?>"
                                            data-producto="<?= $producto ? $producto->nombre : '-'; ?>"
                                            data-cantidad="<?= $caja->cantidad_envases; ?>"
                                            title="Revertir caja">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                        <?php } else { ?>
                        <p class="text-muted mb-0">No hay cajas de botellas en planta.</p>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
</div>

<!-- Modal Revertir Caja -->
<div class="modal modal-fade" tabindex="-1" role="dialog" id="eliminar-caja-modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Revertir Caja</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Esta accion revertira la caja y liberara los envases:</p>
                <ul class="mb-3">
                    <li>Caja: <strong id="eliminar-caja-codigo">-</strong></li>
                    <li>Producto: <strong id="eliminar-caja-producto">-</strong></li>
                    <li>Se liberaran <strong id="eliminar-caja-cantidad">0</strong> envases</li>
                </ul>
                <div class="alert alert-info">
                    <i class="fas fa-fw fa-info-circle"></i>
                    Los envases volveran a estar disponibles para ser asignados a otra caja.
                </div>
                <input type="hidden" id="eliminar-caja-id" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="eliminar-caja-confirmar-btn">
                    <i class="fas fa-undo me-1"></i> Confirmar Reversion
                </button>
            </div>
        </div>
    </div>
</div>



<div class="modal modal-fade" tabindex="-1" role="dialog" id="llenar-barriles-modal">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Cargar Barril
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row align-items-bottom">
                    <div class="col-4 mb-1 border p-3 bg-light" style="border-radius: 10px">
                        <div class="mt-2 text-center fs-3 fw-bold">
                            Fermentador
                        </div>
                        <select class="form-control mt-2" id="llenar-barriles_id_batches_activos-select">
                            <?php
                            foreach($batches_para_carga as $bam) {
                                $activo = new Activo($bam->id_activos);
                                $batch = new Batch($bam->id_batches);
                                $receta = new Receta($batch->id_recetas);
                            ?>
                            <option value="<?= $bam->id; ?>"><?= $activo->codigo; ?> (<?= $activo->litraje; ?>L - <?= $receta->nombre; ?>)</option>
                            <?php
                            }
                            ?>
                        </select>
                        <label class="mt-2">
                            Disponible:
                        </label>
                        <div class="input-group">
                            <input type="number" class="form-control fw-bold fs-3" id="llenar-barriles-fermentador-cantidad-disponible" value="0" READONLY>
                            <span class="input-group-text fs-3" id="basic-addon1" style="border-radius: 0px 10px 10px 0px">Litros</span>
                        </div>
                    </div>
                    <div class="col-4 mb-1 align-middle text-center fw-bold px-3">
                        <div class="w-100 text-center">
                        </div>
                        <label class="mt-2">
                            Cantidad a cargar:
                        </label>
                        <div class="input-group">
                            <input type="number" class="form-control fw-bold fs-3" id="llenar-barriles-cantidad-a-cargar" value="0">
                            <span class="input-group-text fs-3" id="basic-addon1" style="border-radius: 0px 10px 10px 0px">Litros</span>
                        </div>
                        <button type="button" class="btn btn-primary mt-3 w-100" id="llenar-barril-aceptar-btn" disabled>
                            <i class='fas fa-fw fa-forward'></i> Cargar <i class='fas fa-fw fa-forward'></i>
                        </button>

                    </div>
                    <div class="col-4 mb-1 border p-3 bg-light" style="border-radius: 10px">
                        <div class="mt-2 text-center fs-3 fw-bold">
                            Barril
                        </div>
                        <select class="form-control mt-2" id="llenar-barriles_id_barriles-select">
                            <?php
                            foreach($barriles_disponibles as $bd) {
                            ?>
                            <option value="<?= $bd->id; ?>" data-litros-cargados="<?= $bd->litros_cargados; ?>" data-capacidad="<?= $bd->litraje; ?>"><?= $bd->codigo; ?> (<?= $bd->litraje; ?>L)</option>
                            <?php
                            }
                            ?>
                        </select>
                        <label class="mt-2">
                            Cargado:
                        </label>
                        <div class="input-group">
                            <input type="number" class="form-control fw-bold fs-3" id="llenar-barriles-barril-litros-cargados" READONLY>
                            <span class="input-group-text fs-3" style="border-radius: 0px 10px 10px 0px">Litros</span>
                        </div>
                        <label class="mt-2">
                            Disponible:
                        </label>
                        <div class="input-group">
                            <input type="number" class="form-control fw-bold fs-3" id="llenar-barriles-barril-espacio-disponible" READONLY>
                            <span class="input-group-text fs-3" style="border-radius: 0px 10px 10px 0px">Litros</span>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="alert alert-danger" id="llenar-barriles-error" style="display: none;">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <span id="llenar-barriles-error-mensaje"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<div class="modal modal-fade" tabindex="-1" role="dialog" id="traspaso-modal">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Traspaso
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                <div class="col-6 mb-1">
                        Desde:
                    </div>
                    <div class="col-6 mb-1">
                        <select class="form-control" id="traspasos-desde-select">
                            <?php
                            foreach($activos_traspaso as $at) {
                            ?>
                            <option value="<?= $at->id; ?>"><?= $at->codigo; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-6 mb-1">
                        Hasta:
                    </div>
                    <div class="col-6 mb-1">
                        <select class="form-control" id="traspasos-hasta-select">
                            <?php
                            foreach($activos_traspaso_disponibles as $atd) {
                            ?>
                            <option value="<?= $atd->id; ?>"><?= $atd->codigo; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-12 my-2 mt-4 text-center text-danger" id="traspaso-warning-label">
                        Los fermentadores deben tener el mismo litraje.
                    </div>
                    <div class="col-12 my-2">
                        <hr/>
                    </div>
                    <div class="col-6 mb-1">
                        Batch:
                    </div>
                    <div class="col-6 mb-1">
                        <input type="text" class="form-control" id="traspasos-batch" READONLY>
                    </div>
                    <div class="col-6 mb-1">
                        Fecha:
                    </div>
                    <div class="col-6 mb-1">
                        <input type="date" name="date" id="nuevo-traspasos-date-input" class="form-control" READONLY>
                    </div>
                    <div class="col-6 mb-1">
                        Hora:
                    </div>
                    <div class="col-6 mb-1">
                        <input type="time" name="hora" id="nuevo-traspasos-hora-input" class="form-control" READONLY>
                    </div>
                    <div class="col-12 my-2">
                        <hr/>
                    </div>
                    <div class="col-6 mb-1">
                        Merma (opcional):
                    </div>
                    <div class="col-6 mb-1">
                        <div class="input-group">
                            <input type="number" step="0.1" min="0" class="form-control" id="nuevo-traspasos-merma-input" value="0">
                            <span class="input-group-text">L</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="traspasar-aceptar-btn">
                    Traspasar
                </button>
            </div>
        </div>
    </div>
</div>


<div class="modal modal-fade" tabindex="-1" role="dialog" id="agregar-fermentadores-modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Agregar Fermentador
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-6 mb-1">
                        Batch:
                    </div>
                    <div class="col-6 mb-1">
                        <select class="form-control" id="agregar-fermentadores_id_batches-select">
                            <?php
                            foreach($batches as $batch) {
                            ?>
                            <option value="<?= $batch->id; ?>">#<?= $batch->batch_nombre; ?></option>
                            <?php
                            }

                            ?>
                        </select>
                    </div>
                    <div class="col-6 mb-1">
                        Fermentador:
                    </div>
                    <div class="col-6 mb-1">
                        <select class="form-control" id="agregar-fermentadores_id_activos-select">
                            <?php
                            foreach($activos_disponibles as $activo) {
                            ?>
                            <option value="<?= $activo->id; ?>"><?= $activo->codigo; ?></option>
                            <?php
                            }

                            ?>
                        </select>
                    </div>
                    <div class="col-6 mb-1">
                        Cantidad:
                    </div>
                    <div class="col-6 mb-1">
                        <div class="input-group">
                            <input type="number" class="form-control acero-float" name="cantidad" id="agregar-fermentadores-cantidad-input" value="0" step="0.1" min="0" DISABLED>
                            <span class="input-group-text" style="border-radius: 0px 10px 10px 0px" id="agregar-fermentadores-unidad-de-medida">L</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="agregar-fermentadores-aceptar-btn" data-bs-dismiss="modal">Agregar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal modal-fade" tabindex="-1" role="dialog" id="eliminar-fermentadores-modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Quitar Fermentador
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body justify-content-center">
                ¿Deseas quitar este fermentador?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="eliminar-fermentadores-aceptar-btn" data-bs-dismiss="modal">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Envasar (Latas y Botellas - Multiple) -->
<div class="modal modal-fade" tabindex="-1" role="dialog" id="envasar-modal">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="envasar-modal-title">Envasar desde Fermentador o Barril</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row">
                    <!-- Columna Izquierda: Origen -->
                    <div class="col-4 border-end pe-4">
                        <div class="fs-5 fw-bold mb-3"><i class="fas fa-fw fa-database"></i> Origen</div>
                        <div class="mb-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="envasar-origen-tipo" id="envasar-origen-fermentador" value="fermentador" checked>
                                <label class="form-check-label" for="envasar-origen-fermentador">Fermentador</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="envasar-origen-tipo" id="envasar-origen-barril" value="barril">
                                <label class="form-check-label" for="envasar-origen-barril">Barril</label>
                            </div>
                        </div>
                        <select class="form-control" id="envasar-origen-fermentador-select">
                            <?php foreach($batches_activos_ferminacion_inox as $bam) {
                                $activo = new Activo($bam->id_activos);
                                $batch = new Batch($bam->id_batches);
                                $receta = new Receta($batch->id_recetas);
                            ?>
                            <option value="<?= $bam->id; ?>"
                                    data-litraje="<?= $bam->litraje; ?>"
                                    data-id-batches="<?= $bam->id_batches; ?>"
                                    data-id-activos="<?= $bam->id_activos; ?>"
                                    data-id-recetas="<?= $batch->id_recetas; ?>">
                                <?= $activo->codigo; ?> (<?= $bam->litraje; ?>L - <?= $receta->nombre; ?>)
                            </option>
                            <?php } ?>
                        </select>
                        <select class="form-control" id="envasar-origen-barril-select" style="display: none;">
                            <?php foreach($barriles_para_envasar as $barril) {
                                $batch = new Batch($barril->id_batches);
                                $receta = new Receta($batch->id_recetas);
                            ?>
                            <option value="<?= $barril->id; ?>"
                                    data-litraje="<?= $barril->litros_cargados; ?>"
                                    data-id-batches="<?= $barril->id_batches; ?>"
                                    data-id-activos="<?= $barril->id_activos; ?>"
                                    data-id-recetas="<?= $batch->id_recetas; ?>">
                                <?= $barril->codigo; ?> (<?= $barril->litros_cargados; ?>L - <?= $receta->nombre; ?>)
                            </option>
                            <?php } ?>
                        </select>
                        <div class="mt-3 p-3 bg-light rounded">
                            <div class="text-muted small">Volumen disponible:</div>
                            <div class="fs-2 fw-bold text-primary" id="envasar-origen-disponible-display">0 L</div>
                            <input type="hidden" id="envasar-origen-disponible" value="0">
                        </div>
                        <div class="mt-3 p-3 bg-warning-subtle rounded">
                            <div class="text-muted small">Volumen restante:</div>
                            <div class="fs-2 fw-bold" id="envasar-volumen-restante-display">0 L</div>
                        </div>
                    </div>
                    <!-- Columna Derecha: Lineas de Envasado -->
                    <div class="col-8 ps-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="fs-5 fw-bold"><i class="fas fa-fw fa-boxes"></i> Lineas de Envasado</div>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="envasar-agregar-lata-btn">
                                    <i class="fas fa-fw fa-plus"></i> Latas
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-info" id="envasar-agregar-botella-btn">
                                    <i class="fas fa-fw fa-plus"></i> Botellas
                                </button>
                            </div>
                        </div>
                        <div id="envasar-lineas-container">
                            <!-- Las lineas se agregan dinamicamente aqui -->
                            <div class="text-center text-muted py-4" id="envasar-lineas-empty">
                                <i class="fas fa-fw fa-info-circle"></i> Agregue lineas de latas o botellas usando los botones de arriba
                            </div>
                        </div>
                        <hr class="my-3">
                        <div class="row">
                            <div class="col-6">
                                <div class="p-3 bg-success-subtle rounded text-center">
                                    <div class="text-muted small">Total a envasar:</div>
                                    <div class="fs-4 fw-bold text-success" id="envasar-total-volumen">0 L</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 bg-danger-subtle rounded text-center">
                                    <div class="text-muted small">Merma estimada:</div>
                                    <div class="fs-4 fw-bold text-danger" id="envasar-total-merma">0 L</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="envasar-aceptar-btn" disabled>
                    <i class="fas fa-fw fa-check"></i> Confirmar Envasado
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Template para linea de envasado (oculto) -->
<template id="envasar-linea-template">
    <div class="envasar-linea card mb-2" data-linea-id="">
        <div class="card-body py-2 px-3">
            <div class="row align-items-center">
                <div class="col-auto">
                    <span class="badge bg-primary envasar-linea-tipo-badge">Lata</span>
                </div>
                <div class="col-3">
                    <select class="form-control form-control-sm envasar-linea-formato">
                    </select>
                </div>
                <div class="col-2">
                    <div class="input-group input-group-sm">
                        <input type="number" class="form-control envasar-linea-cantidad" value="0" min="0">
                        <span class="input-group-text">uds</span>
                    </div>
                </div>
                <div class="col-2 text-center">
                    <small class="text-muted envasar-linea-max">Max: 0</small>
                </div>
                <div class="col-2 text-end">
                    <span class="fw-bold envasar-linea-volumen">0 L</span>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-sm btn-outline-danger envasar-linea-eliminar">
                        <i class="fas fa-fw fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<!-- Modal Crear Cajas (Wizard) -->
<div class="modal modal-fade" tabindex="-1" role="dialog" id="crear-cajas-modal">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="crear-cajas-modal-title">Crear Cajas de Envases</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="crear-cajas-tipo-envase" value="Lata">
                <!-- Step 1: Seleccionar Producto -->
                <div id="crear-cajas-step-1">
                    <h5 class="mb-3">Paso 1: Seleccionar Producto</h5>
                    <p class="text-muted" id="crear-cajas-step1-desc">Seleccione el producto para el cual desea crear cajas.</p>
                    <select class="form-control form-control-lg" id="crear-cajas-producto-select">
                        <option value="">-- Seleccione un producto --</option>
                        <?php foreach($productos_de_latas as $producto) {
                            $formato = $producto->getFormatoDeEnvases();
                        ?>
                        <option value="<?= $producto->id; ?>"
                                data-tipo="Lata"
                                data-formato-id="<?= $producto->id_formatos_de_envases; ?>"
                                data-formato-nombre="<?= $formato ? $formato->nombre : ''; ?>"
                                data-cantidad-envases="<?= $producto->cantidad_de_envases; ?>"
                                data-es-mixto="<?= $producto->es_mixto; ?>">
                            <?= $producto->nombre; ?><?= $producto->es_mixto ? ' [MIXTO]' : ''; ?> (<?= $formato ? $formato->nombre : ''; ?> x <?= $producto->cantidad_de_envases; ?>)
                        </option>
                        <?php } ?>
                        <?php foreach($productos_de_botellas as $producto) {
                            $formato = $producto->getFormatoDeEnvases();
                        ?>
                        <option value="<?= $producto->id; ?>"
                                data-tipo="Botella"
                                data-formato-id="<?= $producto->id_formatos_de_envases; ?>"
                                data-formato-nombre="<?= $formato ? $formato->nombre : ''; ?>"
                                data-cantidad-envases="<?= $producto->cantidad_de_envases; ?>"
                                data-es-mixto="<?= $producto->es_mixto; ?>">
                            <?= $producto->nombre; ?><?= $producto->es_mixto ? ' [MIXTO]' : ''; ?> (<?= $formato ? $formato->nombre : ''; ?> x <?= $producto->cantidad_de_envases; ?>)
                        </option>
                        <?php } ?>
                    </select>
                    <div class="mt-3 d-none" id="crear-cajas-producto-info">
                        <div class="alert alert-info">
                            <strong>Formato:</strong> <span id="crear-cajas-info-formato"></span><br>
                            <strong>Envases por caja:</strong> <span id="crear-cajas-info-cantidad"></span>
                        </div>
                    </div>
                    <div class="mt-3 text-end">
                        <button type="button" class="btn btn-primary" id="crear-cajas-siguiente-btn" disabled>
                            Siguiente <i class="fas fa-fw fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
                <!-- Step 2: Asignar Envases -->
                <div id="crear-cajas-step-2" style="display: none;">
                    <h5 class="mb-3" id="crear-cajas-step2-title">Paso 2: Asignar Envases de Batches</h5>
                    <p class="text-muted">Ingrese la cantidad de envases a tomar de cada batch. Total requerido: <strong id="crear-cajas-total-requerido">0</strong></p>
                    <table class="table table-sm table-striped" id="crear-cajas-batches-table">
                        <thead>
                            <tr>
                                <th>Batch</th>
                                <th>Disponibles</th>
                                <th>Cantidad a usar</th>
                            </tr>
                        </thead>
                        <tbody id="crear-cajas-batches-tbody">
                        </tbody>
                    </table>
                    <div class="alert alert-warning d-none" id="crear-cajas-warning">
                        <i class="fas fa-fw fa-exclamation-triangle"></i> <span id="crear-cajas-warning-text"></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <strong>Total asignado:</strong> <span id="crear-cajas-total-asignado" class="fs-5">0</span> / <span id="crear-cajas-total-requerido-2">0</span>
                        </div>
                        <div>
                            <button type="button" class="btn btn-secondary me-2" id="crear-cajas-atras-btn">
                                <i class="fas fa-fw fa-arrow-left"></i> Atras
                            </button>
                            <button type="button" class="btn btn-success" id="crear-cajas-crear-btn" disabled>
                                <i class="fas fa-fw fa-box"></i> Crear Caja
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Revertir Envasado -->
<div class="modal modal-fade" tabindex="-1" role="dialog" id="revertir-envasado-modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i><span id="revertir-modal-title">Revertir Envasado</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Esta accion revertira completamente el proceso de envasado:</p>
                <ul class="mb-3">
                    <li>Se eliminaran <strong id="revertir-envases-count">0</strong> <span id="revertir-envases-tipo-label">envases</span></li>
                    <li>Se eliminara el batch de envases</li>
                    <li>Se devolvera el volumen (<strong id="revertir-volumen">0</strong> L) al origen: <strong id="revertir-origen">-</strong></li>
                </ul>
                <div class="alert alert-warning">
                    <i class="fas fa-fw fa-info-circle"></i>
                    <strong>Batch:</strong> <span id="revertir-batch-nombre"></span><br>
                    <small class="text-muted">Solo se puede revertir si todos los envases estan disponibles (no asignados a cajas).</small>
                </div>
                <input type="hidden" id="revertir-batch-id" value="">
                <input type="hidden" id="revertir-tipo" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="revertir-envasado-confirmar-btn">
                    <i class="fas fa-undo me-1"></i> Confirmar Reversion
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal modal-fade" tabindex="-1" role="dialog" id="eliminar-linea-envasado-modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Confirmar eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de eliminar esta línea de envasado?</p>
                <input type="hidden" id="eliminar-linea-envasado-id" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="eliminar-linea-envasado-confirmar-btn">
                    <i class="fas fa-trash me-1"></i> Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Revertir Llenado Barril -->
<div class="modal modal-fade" tabindex="-1" role="dialog" id="revertir-llenado-barril-modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-undo me-2"></i>Revertir Llenado de Barril</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de revertir el llenado de este barril?</p>
                <p>Se devolverán <strong id="revertir-llenado-litros"></strong>L al fermentador.</p>
                <p>Barril: <strong id="revertir-llenado-codigo"></strong></p>
                <input type="hidden" id="revertir-llenado-id" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" id="revertir-llenado-confirmar-btn">
                    <i class="fas fa-undo me-1"></i> Revertir
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Revertir Traspaso -->
<div class="modal modal-fade" tabindex="-1" role="dialog" id="revertir-traspaso-modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-undo me-2"></i>Revertir Traspaso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de revertir este traspaso?</p>
                <p>La cerveza será devuelta de <strong id="revertir-traspaso-destino"></strong> a <strong id="revertir-traspaso-origen"></strong>.</p>
                <input type="hidden" id="revertir-traspaso-id" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" id="revertir-traspaso-confirmar-btn">
                    <i class="fas fa-undo me-1"></i> Revertir
                </button>
            </div>
        </div>
    </div>
</div>

<script>

console.log('[SCRIPT] Inicio del script de inventario-de-productos');

const activos_traspaso = <?= json_encode($activos_traspaso,JSON_PRETTY_PRINT); ?>;
const activos_traspaso_disponibles = <?= json_encode($activos_traspaso_disponibles,JSON_PRETTY_PRINT); ?>;
const batches = <?= json_encode($batches, JSON_PRETTY_PRINT); ?>;
const recetas = <?= json_encode(Receta::getAll(), JSON_PRETTY_PRINT); ?>;
const barriles_disponibles = <?= json_encode($barriles_disponibles, JSON_PRETTY_PRINT); ?>;
const batches_para_carga = <?= json_encode($batches_para_carga, JSON_PRETTY_PRINT); ?>;

var fermentadores_disponibles = <?= json_encode($activos_disponibles,JSON_PRETTY_PRINT); ?>;
var fermentadores = <?= json_encode($activos,JSON_PRETTY_PRINT); ?>;


var id_fermentadores_inicio = 0;
var id_fermentadores_final = 0;
var id_batches = 0;
var date = '';
var hora = '';

$(document).ready(function(){
    armarFermentadoresSelect();
})

$(document).on('click','#traspasar-btn',function(e){

    console.log('activos traspaso length', activos_traspaso.length)
    console.log('activos traspaso disponibles length', activos_traspaso_disponibles.length)

    if(activos_traspaso_disponibles.length == 0) {
        alert('No hay fermentadores cargados para realizar el traspaso.');
        return false
    }

    e.preventDefault();
    $('#traspasos-desde-select').val(activos_traspaso[0].id);
    $('#traspasos-hasta-select').val(activos_traspaso_disponibles[0].id);
    $('#traspaso-warning-label').hide();

    var activo_1 = activos_traspaso.find((a) => a.id == $('#traspasos-desde-select').val());
    const batch = batches.find((b) => b.id == activo_1.id_batches);
    if(batch) {
        id_batches = batch.id;
    }
    

    var receta_nombre = ''
    if(id_batches > 0) {
        const receta = recetas.find((r) => r.id == batch.id_recetas);
        receta_nombre = receta.nombre
        $('#traspasos-batch').val(receta_nombre + ' #' + batch.batch_nombre);
    } else {
        alert('No hay fermentadores cargados para realizar el traspaso.');
        return false
    }
    

    const fecha = new Date();
    const anio = fecha.getFullYear();
    const mes = String(fecha.getMonth() + 1).padStart(2, '0'); // Los meses van de 0 a 11
    const dia = String(fecha.getDate()).padStart(2, '0');
    const horas = String(fecha.getHours()).padStart(2, '0');
    const minutos = String(fecha.getMinutes()).padStart(2, '0');
    const segundos = String(fecha.getSeconds()).padStart(2, '0');

    $('#nuevo-traspasos-date-input').val(anio + '-' + mes + '-' + dia);
    $('#nuevo-traspasos-hora-input').val(horas + ':' + minutos + ':' + segundos);

    $('#traspaso-modal').modal('toggle');

});

$(document).on('change','#traspasos-desde-select',function(e){


    

    var activo_1 = activos_traspaso.find((a) => a.id == $('#traspasos-desde-select').val());
    const batch = batches.find((b) => b.id == activo_1.id_batches);
    const receta = recetas.find((r) => r.id == batch.id_recetas);
    $('#traspasos-batch').val(receta.nombre + ' #' + batch.id);

    id_batches = batch.id;

    var activo_2 = activos_traspaso_disponibles.find((a) => a.id == $('#traspasos-hasta-select').val());

    if(activo_1.litraje != activo_2.litraje) {
        $('#traspaso-warning-label').show(200);
        $('#traspasar-btn').attr('DISABLED',true);
    } else {
        $('#traspaso-warning-label').hide(200);
        $('#traspasar-btn').attr('DISABLED',false);
    }

    const fecha = new Date();
    const anio = fecha.getFullYear();
    const mes = String(fecha.getMonth() + 1).padStart(2, '0'); // Los meses van de 0 a 11
    const dia = String(fecha.getDate()).padStart(2, '0');
    const horas = String(fecha.getHours()).padStart(2, '0');
    const minutos = String(fecha.getMinutes()).padStart(2, '0');
    const segundos = String(fecha.getSeconds()).padStart(2, '0');

    $('#nuevo-traspasos-date-input').val(anio + '-' + mes + '-' + dia);
    $('#nuevo-traspasos-hora-input').val(horas + ':' + minutos + ':' + segundos);

});

$(document).on('change','#traspasos-hasta-select',function(e){
    var activo_1 = activos_traspaso.find((a) => a.id == $('#traspasos-desde-select').val());
    var activo_2 = activos_traspaso_disponibles.find((a) => a.id == $('#traspasos-hasta-select').val());
    if(activo_1.litraje != activo_2.litraje) {
        $('#traspaso-warning-label').show(200);
        $('#traspasar-btn').attr('DISABLED',true);
    } else {
        $('#traspaso-warning-label').hide(200);
        $('#traspasar-btn').attr('DISABLED',false);
    }
})

$(document).on('click','#traspasar-aceptar-btn',function(e){

    e.preventDefault();

    var activo_1 = activos_traspaso.find((a) => a.id == $('#traspasos-desde-select').val());
    var activo_2 = activos_traspaso_disponibles.find((a) => a.id == $('#traspasos-hasta-select').val());

    if(activo_1.litraje != activo_2.litraje) {
        $('#traspaso-warning-label').show(200);
        return false;
    }

    var data = {
        'id_batches': id_batches,
        'id_fermentadores_inicio': $('#traspasos-desde-select').val(),
        'id_fermentadores_final': $('#traspasos-hasta-select').val(),
        'date': $('#nuevo-traspasos-date-input').val(),
        'hora': $('#nuevo-traspasos-hora-input').val(),
        'merma_litros': $('#nuevo-traspasos-merma-input').val() || 0
    };

    var url = './ajax/ajax_agregarTraspasosInventarioProductos.php';

    $.post(url,data,function(response){
        console.log(response);
        response = JSON.parse(response);
        if(response.status == 'ERROR') {
            alert(response.mensaje);
            return false;
        }
        window.location.href = './?s=inventario-de-productos&msg=2';
    });

});

$(document).on('click','#llenar-barriles-btn',function(e){
    renderLlenarBarrilesDisponibles();
    renderLlenarBarrilesFermentadores();
});

$(document).on('change','#llenar-barriles_id_barriles-select',renderLlenarBarrilesDisponibles);
function renderLlenarBarrilesDisponibles() {
    const barril = barriles_disponibles.find((b) => b.id == $('#llenar-barriles_id_barriles-select').val());
    $('#llenar-barriles-barril-litros-cargados').val(barril.litros_cargados);
    var espacioDisponible = parseFloat(barril.litraje) - parseFloat(barril.litros_cargados);
    $('#llenar-barriles-barril-espacio-disponible').val(espacioDisponible);
    // Ocultar error al cambiar barril
    $('#llenar-barriles-error').hide();
    validarCantidadACargar();
}

$(document).on('change','#llenar-barriles_id_batches_activos-select',renderLlenarBarrilesFermentadores);
function renderLlenarBarrilesFermentadores() {
    const bam = batches_para_carga.find((b) => b.id == $('#llenar-barriles_id_batches_activos-select').val());
    $('#llenar-barriles-fermentador-cantidad-disponible').val(bam.litraje);
    // Ocultar error al cambiar fermentador
    $('#llenar-barriles-error').hide();
    validarCantidadACargar();
}

// Validar cantidad a cargar cuando cambia el input
$(document).on('input change', '#llenar-barriles-cantidad-a-cargar', validarCantidadACargar);

function validarCantidadACargar() {
    var cantidad = parseFloat($('#llenar-barriles-cantidad-a-cargar').val()) || 0;
    var espacioBarril = parseFloat($('#llenar-barriles-barril-espacio-disponible').val()) || 0;
    var litrosFermentador = parseFloat($('#llenar-barriles-fermentador-cantidad-disponible').val()) || 0;

    var esValido = cantidad > 0 && cantidad <= espacioBarril && cantidad <= litrosFermentador;

    $('#llenar-barril-aceptar-btn').prop('disabled', !esValido);

    // Ocultar error si cambia la cantidad
    $('#llenar-barriles-error').hide();
}

$(document).on('click','#llenar-barril-aceptar-btn',function(e){

    e.preventDefault();

    var data = {
        'id_batches_activos': $('#llenar-barriles_id_batches_activos-select').val(),
        'id_barriles': $('#llenar-barriles_id_barriles-select').val(),
        'cantidad_a_cargar': $('#llenar-barriles-cantidad-a-cargar').val()
    };

    console.log(data);

    // Ocultar error previo
    $('#llenar-barriles-error').hide();

    var url = './ajax/ajax_llenarBarriles.php';
    $.post(url,data,function(response){
        console.log(response);
        response = JSON.parse(response);
        if(response.status == 'OK') {
            window.location.href = './?s=inventario-de-productos&msg=1&msg_content=' + encodeURIComponent(response.msg_content);
        } else {
            $('#llenar-barriles-error-mensaje').text(response.mensaje);
            $('#llenar-barriles-error').show();
        }
    });

});








function armarFermentadoresSelect() {
    var fermentadores_select_html = '';

    if(!fermentadores_disponibles || fermentadores_disponibles.length == 0) {
        console.log('[FERMENTADORES] No hay fermentadores disponibles');
        $('#agregar-fermentadores_id_activos-select').html('<option value="">No hay fermentadores disponibles</option>');
        return;
    }

    fermentadores_disponibles.sort();
    fermentadores_disponibles.forEach(function(f){
        fermentadores_select_html += '<option value="' + f.id + '">' + f.codigo + '</option>';
    });
    console.log('[FERMENTADORES] Select HTML:', fermentadores_select_html);
    console.log('[FERMENTADORES] Primer fermentador ID:', fermentadores_disponibles[0].id);
    $('#agregar-fermentadores_id_activos-select').html(fermentadores_select_html);
    $('#agregar-fermentadores_id_activos-select').val(fermentadores_disponibles[0].id);
    console.log('[FERMENTADORES] Valor seleccionado:', $('#agregar-fermentadores_id_activos-select').val());
}


$(document).on('click','#agregar-fermentadores-btn',function(e){
    e.preventDefault();
    armarFermentadoresSelect();

    if(!fermentadores_disponibles || fermentadores_disponibles.length == 0) {
        console.log('[FERMENTADORES] No hay fermentadores disponibles para agregar');
        return;
    }

    var id_activos = fermentadores_disponibles[0].id;
    var fermentador = fermentadores.find((f) => f.id == id_activos);
    console.log('[FERMENTADORES] id_activos:', id_activos);
    console.log('[FERMENTADORES] fermentadores:', fermentadores);
    console.log('[FERMENTADORES] fermentador:', fermentador);
    if(fermentador) {
        $('#agregar-fermentadores-cantidad-input').val(fermentador.litraje);
    }
});

$(document).on('change','#agregar-fermentadores_id_activos-select',function(e){

    e.preventDefault();

    var id_activos = $('#agregar-fermentadores_id_activos-select').val();
    var fermentador = fermentadores.find((f) => f.id == id_activos);
    $('#agregar-fermentadores-cantidad-input').val(fermentador.litraje)

});


$(document).on('click','#agregar-fermentadores-aceptar-btn',function(e){

    e.preventDefault();

    var url = './ajax/ajax_inventarioProductosBatchActivoAgregar.php';
    var data = {
        'id': '',
        'id_batches': $('#agregar-fermentadores_id_batches-select').val(),
        'id_activos': $('#agregar-fermentadores_id_activos-select').val(),
        'estado': 'Fermentación'
    };

    $.post(url,data,function(response){
        console.log(response);
        response = JSON.parse(response);
        window.location.href = './?s=inventario-de-productos&msg=1&msg_content=' + response.msg_content;
    });

});

$(document).on('click','.maduracion-eliminar-fermentadores-aceptar-btn',function(e){

    e.preventDefault();

    var url = './ajax/ajax_inventarioProductosBatchActivoEliminar.php';
    var data = {
        'id_batches': $(e.currentTarget).data('idbatches'),
        'id_batches_activos': $(e.currentTarget).data('idbatchesactivos')
    };

    console.log('data',data)

    $.post(url,data,function(response){
        console.log(response);
        response = JSON.parse(response);
        window.location.href = './?s=inventario-de-productos&msg=1&msg_content=' + response.msg_content;
    });

});

// Abrir modal de revertir llenado de barril
$(document).on('click', '.revertir-llenado-barril-btn', function(e) {
    e.preventDefault();
    var idBarril = $(this).data('idbarril');
    var codigo = $(this).data('codigo');
    var litros = $(this).data('litros');

    $('#revertir-llenado-id').val(idBarril);
    $('#revertir-llenado-codigo').text(codigo);
    $('#revertir-llenado-litros').text(litros);
    $('#revertir-llenado-barril-modal').modal('show');
});

// Confirmar revertir llenado de barril
$(document).on('click', '#revertir-llenado-confirmar-btn', function(e) {
    e.preventDefault();

    var idBarril = $('#revertir-llenado-id').val();
    var url = './ajax/ajax_revertirLlenadoBarril.php';
    var data = {
        'id_barril': idBarril
    };

    $.post(url, data, function(response) {
        console.log(response);
        response = JSON.parse(response);
        if(response.status == 'OK') {
            window.location.href = './?s=inventario-de-productos&msg=1&msg_content=' + encodeURIComponent(response.mensaje);
        } else {
            alert(response.mensaje);
            $('#revertir-llenado-barril-modal').modal('hide');
        }
    });
});

// Abrir modal de revertir traspaso
$(document).on('click', '.revertir-traspaso-btn', function(e) {
    e.preventDefault();
    var idTraspaso = $(this).data('idtraspaso');
    var origen = $(this).data('origen');
    var destino = $(this).data('destino');

    $('#revertir-traspaso-id').val(idTraspaso);
    $('#revertir-traspaso-origen').text(origen);
    $('#revertir-traspaso-destino').text(destino);
    $('#revertir-traspaso-modal').modal('show');
});

// Confirmar revertir traspaso
$(document).on('click', '#revertir-traspaso-confirmar-btn', function(e) {
    e.preventDefault();

    var idTraspaso = $('#revertir-traspaso-id').val();
    var url = './ajax/ajax_revertirTraspaso.php';
    var data = {
        'id_traspaso': idTraspaso
    };

    $.post(url, data, function(response) {
        console.log(response);
        response = JSON.parse(response);
        if(response.status == 'OK') {
            window.location.href = './?s=inventario-de-productos&msg=1&msg_content=' + encodeURIComponent(response.mensaje);
        } else {
            alert(response.mensaje);
            $('#revertir-traspaso-modal').modal('hide');
        }
    });
});

var data_eliminar = null

// Click en botón eliminar fermentador (card Fermentación)
$(document).on('click', '.eliminar-fermentador-btn', function(e) {
    e.preventDefault();
    e.stopPropagation();

    data_eliminar = {
        'id_batches_activos': $(this).data('idbatchesactivos'),
        'id_batches': $(this).data('idbatches'),
        'id_activos': $(this).data('idactivos'),
        'estado': 'Fermentación'
    };

    $('#eliminar-fermentadores-modal').modal('toggle');
})

$(document).on('click','#eliminar-fermentadores-aceptar-btn',function(e){

    e.preventDefault();

    var url = './ajax/ajax_inventarioProductosBatchActivoEliminar.php';

    $.post(url,data_eliminar,function(response){
        console.log(response);
        response = JSON.parse(response);
        window.location.href = './?s=inventario-de-productos&msg=1&msg_content=' + response.msg_content;
    });

})


// ===========================
// SISTEMA DE ENVASES (Latas y Botellas)
// ===========================

console.log('[INIT] Sistema de Envases cargado');

// CSRF Token para llamadas AJAX seguras
const csrfToken = '<?= Security::getCSRFToken(); ?>';
console.log('[CSRF] Token generado:', csrfToken.substring(0, 10) + '...');

// Datos de envases por tipo
const formatos_latas = <?= json_encode($formatos_latas, JSON_PRETTY_PRINT); ?>;
const formatos_botellas = <?= json_encode($formatos_botellas, JSON_PRETTY_PRINT); ?>;
const batches_de_latas = <?= json_encode($batches_de_latas, JSON_PRETTY_PRINT); ?>;
const batches_de_botellas = <?= json_encode($batches_de_botellas, JSON_PRETTY_PRINT); ?>;
const productos_de_latas = <?= json_encode($productos_de_latas, JSON_PRETTY_PRINT); ?>;
const productos_de_botellas = <?= json_encode($productos_de_botellas, JSON_PRETTY_PRINT); ?>;
const barriles_para_envasar = <?= json_encode($barriles_para_envasar, JSON_PRETTY_PRINT); ?>;

console.log('[DATA] Formatos latas:', formatos_latas);
console.log('[DATA] Formatos botellas:', formatos_botellas);
console.log('[DATA] Batches de latas:', batches_de_latas);
console.log('[DATA] Batches de botellas:', batches_de_botellas);
console.log('[DATA] Barriles para envasar:', barriles_para_envasar);

// Variables para el wizard de crear cajas
var crearCajasProductoId = null;
var crearCajasFormatoId = null;
var crearCajasCantidadRequerida = 0;
var crearCajasEsMixto = false;

// ===========================
// MODAL ENVASAR (Multiples lineas: Latas y Botellas)
// ===========================

// Variables para el sistema de envasado
var envasarLineas = [];
var envasarLineaIdCounter = 0;

// Cambiar entre origen fermentador/barril
$(document).on('change', 'input[name="envasar-origen-tipo"]', function() {
    var origen = $(this).val();
    console.log('[ENVASAR] Cambio tipo origen:', origen);
    if(origen == 'fermentador') {
        $('#envasar-origen-fermentador-select').show();
        $('#envasar-origen-barril-select').hide();
    } else {
        $('#envasar-origen-fermentador-select').hide();
        $('#envasar-origen-barril-select').show();
    }
    actualizarEnvasarDisponible();
    actualizarEnvasarTotales();
});

// Al abrir modal envasar
$(document).on('click', '#envasar-btn', function() {
    console.log('[ENVASAR] Modal abierto');
    // Limpiar lineas
    envasarLineas = [];
    envasarLineaIdCounter = 0;
    $('#envasar-lineas-container').html('<div class="text-center text-muted py-4" id="envasar-lineas-empty"><i class="fas fa-fw fa-info-circle"></i> Agregue lineas de latas o botellas usando los botones de arriba</div>');
    actualizarEnvasarDisponible();
    actualizarEnvasarTotales();
    $('#envasar-aceptar-btn').prop('disabled', true);
});

// Al cambiar origen
$(document).on('change', '#envasar-origen-fermentador-select, #envasar-origen-barril-select', function() {
    console.log('[ENVASAR] Cambio origen seleccionado:', $(this).val());
    actualizarEnvasarDisponible();
    actualizarEnvasarTotales();
});

// Agregar linea de latas
$(document).on('click', '#envasar-agregar-lata-btn', function() {
    agregarLineaEnvasado('Lata');
});

// Agregar linea de botellas
$(document).on('click', '#envasar-agregar-botella-btn', function() {
    agregarLineaEnvasado('Botella');
});

// Eliminar linea - mostrar modal de confirmación
$(document).on('click', '.envasar-linea-eliminar', function() {
    var $linea = $(this).closest('.envasar-linea');
    var lineaId = $linea.data('linea-id');
    $('#eliminar-linea-envasado-id').val(lineaId);
    $('#eliminar-linea-envasado-modal').modal('show');
});

// Confirmar eliminación de línea
$(document).on('click', '#eliminar-linea-envasado-confirmar-btn', function() {
    var lineaId = $('#eliminar-linea-envasado-id').val();
    var $linea = $('.envasar-linea[data-linea-id="' + lineaId + '"]');
    envasarLineas = envasarLineas.filter(function(l) { return l.id != lineaId; });
    $linea.remove();
    if(envasarLineas.length == 0) {
        $('#envasar-lineas-empty').show();
    }
    actualizarEnvasarTotales();
    $('#eliminar-linea-envasado-modal').modal('hide');
});

// Al cambiar formato de una linea
$(document).on('change', '.envasar-linea-formato', function() {
    var $linea = $(this).closest('.envasar-linea');
    var lineaId = $linea.data('linea-id');
    var volumen = parseInt($(this).find('option:selected').data('volumen')) || 0;
    var formatoId = $(this).val();

    // Actualizar en array
    var linea = envasarLineas.find(function(l) { return l.id == lineaId; });
    if(linea) {
        linea.id_formatos_de_envases = formatoId;
        linea.volumen_ml = volumen;
    }

    actualizarLineaMax($linea);
    actualizarEnvasarTotales();
});

// Al cambiar cantidad de una linea
$(document).on('change keyup', '.envasar-linea-cantidad', function() {
    var $linea = $(this).closest('.envasar-linea');
    var lineaId = $linea.data('linea-id');
    var cantidad = parseInt($(this).val()) || 0;

    // No permitir negativos
    if(cantidad < 0) {
        cantidad = 0;
        $(this).val(0);
    }

    // Actualizar en array
    var linea = envasarLineas.find(function(l) { return l.id == lineaId; });
    if(linea) {
        linea.cantidad = cantidad;
    }

    actualizarLineaVolumen($linea);
    actualizarEnvasarTotales();
});

function agregarLineaEnvasado(tipo) {
    $('#envasar-lineas-empty').hide();

    envasarLineaIdCounter++;
    var lineaId = envasarLineaIdCounter;
    var formatos = tipo == 'Lata' ? formatos_latas : formatos_botellas;

    if(formatos.length == 0) {
        alert('No hay formatos de ' + tipo.toLowerCase() + 's disponibles');
        return;
    }

    // Crear HTML de la linea
    var html = '<div class="envasar-linea card mb-2" data-linea-id="' + lineaId + '" data-tipo="' + tipo + '">';
    html += '<div class="card-body py-2 px-3">';
    html += '<div class="row align-items-center">';
    html += '<div class="col-4"><select class="form-control form-control-sm envasar-linea-formato">';
    formatos.forEach(function(f) {
        html += '<option value="' + f.id + '" data-volumen="' + f.volumen_ml + '">' + f.nombre + ' (' + f.volumen_ml + 'ml)</option>';
    });
    html += '</select></div>';
    html += '<div class="col-2"><div class="input-group input-group-sm"><input type="number" class="form-control envasar-linea-cantidad" value="0" min="0"><span class="input-group-text">uds</span></div></div>';
    html += '<div class="col-2 text-center"><small class="text-muted envasar-linea-max">Max: 0</small></div>';
    html += '<div class="col-2 text-end"><span class="fw-bold envasar-linea-volumen">0 L</span></div>';
    html += '<div class="col-auto"><button type="button" class="btn btn-sm btn-outline-danger envasar-linea-eliminar"><i class="fas fa-fw fa-times"></i></button></div>';
    html += '</div></div></div>';

    $('#envasar-lineas-container').append(html);

    // Agregar al array
    var primerFormato = formatos[0];
    envasarLineas.push({
        id: lineaId,
        tipo: tipo,
        id_formatos_de_envases: primerFormato.id,
        volumen_ml: primerFormato.volumen_ml,
        cantidad: 0
    });

    // Actualizar max de la nueva linea
    var $linea = $('.envasar-linea[data-linea-id="' + lineaId + '"]');
    actualizarLineaMax($linea);
    actualizarEnvasarTotales();

    console.log('[ENVASAR] Linea agregada:', { id: lineaId, tipo: tipo });
}

function actualizarEnvasarDisponible() {
    var origen = $('input[name="envasar-origen-tipo"]:checked').val();
    var $select = origen == 'fermentador' ? $('#envasar-origen-fermentador-select') : $('#envasar-origen-barril-select');
    var $option = $select.find('option:selected');
    var litraje = parseFloat($option.data('litraje')) || 0;

    $('#envasar-origen-disponible').val(litraje);
    $('#envasar-origen-disponible-display').text(litraje.toFixed(2) + ' L');

    console.log('[ENVASAR] Disponible actualizado:', litraje + 'L');
}

function actualizarLineaMax($linea) {
    var disponibleMl = parseFloat($('#envasar-origen-disponible').val()) * 1000;
    var volumenEnvase = parseInt($linea.find('.envasar-linea-formato option:selected').data('volumen')) || 0;

    // Calcular volumen usado por otras lineas
    var lineaId = $linea.data('linea-id');
    var volumenOtrasLineas = 0;
    envasarLineas.forEach(function(l) {
        if(l.id != lineaId) {
            volumenOtrasLineas += l.cantidad * l.volumen_ml;
        }
    });

    var disponibleParaEstaLinea = disponibleMl - volumenOtrasLineas;
    var maxEnvases = volumenEnvase > 0 ? Math.floor(disponibleParaEstaLinea / volumenEnvase) : 0;
    if(maxEnvases < 0) maxEnvases = 0;

    $linea.find('.envasar-linea-max').text('Max: ' + maxEnvases);
}

function actualizarLineaVolumen($linea) {
    var lineaId = $linea.data('linea-id');
    var linea = envasarLineas.find(function(l) { return l.id == lineaId; });
    if(linea) {
        var volumenL = (linea.cantidad * linea.volumen_ml / 1000).toFixed(2);
        $linea.find('.envasar-linea-volumen').text(volumenL + ' L');
    }
}

function actualizarEnvasarTotales() {
    var disponibleMl = parseFloat($('#envasar-origen-disponible').val()) * 1000;
    var totalUsadoMl = 0;

    envasarLineas.forEach(function(l) {
        totalUsadoMl += l.cantidad * l.volumen_ml;
        // Actualizar volumen visual de cada linea
        var $linea = $('.envasar-linea[data-linea-id="' + l.id + '"]');
        actualizarLineaVolumen($linea);
        actualizarLineaMax($linea);
    });

    var mermaMl = disponibleMl - totalUsadoMl;
    var restanteMl = disponibleMl - totalUsadoMl;

    $('#envasar-total-volumen').text((totalUsadoMl / 1000).toFixed(2) + ' L');
    $('#envasar-total-merma').text((mermaMl / 1000).toFixed(2) + ' L');
    $('#envasar-volumen-restante-display').text((restanteMl / 1000).toFixed(2) + ' L');

    // Cambiar color del restante si es negativo
    if(restanteMl < 0) {
        $('#envasar-volumen-restante-display').removeClass('text-success').addClass('text-danger');
    } else {
        $('#envasar-volumen-restante-display').removeClass('text-danger');
    }

    // Habilitar/deshabilitar boton
    var hayLineasValidas = envasarLineas.some(function(l) { return l.cantidad > 0; });
    var volumenValido = totalUsadoMl > 0 && totalUsadoMl <= disponibleMl;
    $('#envasar-aceptar-btn').prop('disabled', !hayLineasValidas || !volumenValido);

    console.log('[ENVASAR] Totales:', { totalUsadoMl: totalUsadoMl, mermaMl: mermaMl, restanteMl: restanteMl });
}

// Ejecutar envasado
$(document).on('click', '#envasar-aceptar-btn', function(e) {
    e.preventDefault();
    console.log('[ENVASAR] Boton Confirmar clickeado');

    var origen = $('input[name="envasar-origen-tipo"]:checked').val();
    var $select = origen == 'fermentador' ? $('#envasar-origen-fermentador-select') : $('#envasar-origen-barril-select');
    var $option = $select.find('option:selected');

    // Filtrar lineas con cantidad > 0
    var lineasValidas = envasarLineas.filter(function(l) { return l.cantidad > 0; });

    if(lineasValidas.length == 0) {
        alert('Debe agregar al menos una linea con cantidad mayor a 0');
        return false;
    }

    // Calcular totales
    var volumenOrigenMl = parseFloat($('#envasar-origen-disponible').val()) * 1000;
    var totalUsadoMl = 0;
    lineasValidas.forEach(function(l) {
        totalUsadoMl += l.cantidad * l.volumen_ml;
    });
    var mermaMl = volumenOrigenMl - totalUsadoMl;

    var data = {
        csrf_token: csrfToken,
        origen_tipo: origen,
        id_batches_activos: origen == 'fermentador' ? $select.val() : 0,
        id_barriles: origen == 'barril' ? $select.val() : 0,
        id_batches: $option.data('id-batches'),
        id_activos: $option.data('id-activos'),
        id_recetas: $option.data('id-recetas'),
        volumen_origen_ml: volumenOrigenMl,
        volumen_total_usado_ml: totalUsadoMl,
        merma_total_ml: mermaMl,
        lineas: JSON.stringify(lineasValidas)
    };

    console.log('[ENVASAR] Enviando a ajax_envasar.php:', data);

    $.post('./ajax/ajax_envasar.php', data, function(response) {
        console.log('[ENVASAR] Respuesta recibida:', response);
        if(response.status == 'OK') {
            console.log('[ENVASAR] Exito:', response.mensaje);
            window.location.href = './?s=inventario-de-productos&msg=1&msg_content=' + encodeURIComponent(response.mensaje);
        } else {
            console.error('[ENVASAR] Error:', response.mensaje);
            alert('Error: ' + response.mensaje);
        }
    }, 'json').fail(function(xhr, status, error) {
        console.error('[ENVASAR] AJAX Error:', { xhr: xhr, status: status, error: error });
        console.error('[ENVASAR] Response Text:', xhr.responseText);
        alert('Error al procesar el envasado');
    });
});

// ===========================
// MODAL CREAR CAJAS (WIZARD)
// ===========================

console.log('[CAJAS] Wizard inicializado');

// Variable para tipo de envase en cajas
var crearCajasTipoEnvase = 'Lata';

// Al hacer click en boton crear cajas (latas o botellas)
$(document).on('click', '#crear-cajas-latas-btn, #crear-cajas-botellas-btn', function() {
    crearCajasTipoEnvase = $(this).data('tipo');
    $('#crear-cajas-tipo-envase').val(crearCajasTipoEnvase);

    var nombreTipoPlural = crearCajasTipoEnvase == 'Lata' ? 'Latas' : 'Botellas';
    $('#crear-cajas-modal-title').text('Crear Cajas de ' + nombreTipoPlural);
    $('#crear-cajas-step1-desc').text('Seleccione el producto de ' + nombreTipoPlural.toLowerCase() + ' para el cual desea crear cajas.');
    $('#crear-cajas-step2-title').text('Paso 2: Asignar ' + nombreTipoPlural + ' de Batches');

    // Filtrar productos por tipo
    filtrarProductosPorTipo();

    console.log('[CAJAS] Modal abierto para tipo:', crearCajasTipoEnvase);
});

function filtrarProductosPorTipo() {
    var $select = $('#crear-cajas-producto-select');
    $select.find('option').each(function() {
        var $opt = $(this);
        var tipo = $opt.data('tipo');
        if(!tipo || tipo == crearCajasTipoEnvase) {
            $opt.show();
        } else {
            $opt.hide();
        }
    });
    $select.val('');
}

// Al cambiar producto seleccionado
$(document).on('change', '#crear-cajas-producto-select', function() {
    var $option = $(this).find('option:selected');
    var productoId = $(this).val();

    console.log('[CAJAS] Producto seleccionado:', productoId);

    if(productoId) {
        crearCajasProductoId = productoId;
        crearCajasFormatoId = $option.data('formato-id');
        crearCajasCantidadRequerida = parseInt($option.data('cantidad-envases'));
        crearCajasTipoEnvase = $option.data('tipo');
        crearCajasEsMixto = $option.data('es-mixto') == 1;

        console.log('[CAJAS] Config producto:', {
            productoId: crearCajasProductoId,
            formatoId: crearCajasFormatoId,
            cantidadRequerida: crearCajasCantidadRequerida,
            tipo: crearCajasTipoEnvase,
            esMixto: crearCajasEsMixto
        });

        $('#crear-cajas-info-formato').text($option.data('formato-nombre'));
        $('#crear-cajas-info-cantidad').text(crearCajasCantidadRequerida);
        $('#crear-cajas-producto-info').removeClass('d-none');
        $('#crear-cajas-siguiente-btn').prop('disabled', false);
    } else {
        crearCajasProductoId = null;
        $('#crear-cajas-producto-info').addClass('d-none');
        $('#crear-cajas-siguiente-btn').prop('disabled', true);
    }
});

// Paso siguiente
$(document).on('click', '#crear-cajas-siguiente-btn', function() {
    console.log('[CAJAS] Avanzando a paso 2');
    $('#crear-cajas-step-1').hide();
    $('#crear-cajas-step-2').show();

    // Cargar batches del mismo formato y tipo
    cargarBatchesParaCajas();
});

// Paso atras
$(document).on('click', '#crear-cajas-atras-btn', function() {
    console.log('[CAJAS] Volviendo a paso 1');
    $('#crear-cajas-step-2').hide();
    $('#crear-cajas-step-1').show();
});

function cargarBatchesParaCajas() {
    console.log('[CAJAS] Cargando batches para formato:', crearCajasFormatoId, 'tipo:', crearCajasTipoEnvase, 'mixto:', crearCajasEsMixto);

    var html = '';
    var nombreTipoPlural = crearCajasTipoEnvase == 'Lata' ? 'latas' : 'botellas';
    var batchesData = crearCajasTipoEnvase == 'Lata' ? batches_de_latas : batches_de_botellas;

    console.log('[CAJAS] Batches disponibles:', batchesData);
    console.log('[CAJAS] Formato buscado (tipo ' + typeof crearCajasFormatoId + '):', crearCajasFormatoId);

    var batchesFiltrados = batchesData.filter(function(b) {
        // Convertir a string para comparacion segura
        var formatoMatch = String(b.id_formatos_de_envases) == String(crearCajasFormatoId);
        console.log('[CAJAS] Batch', b.id, 'formato:', b.id_formatos_de_envases, '(tipo ' + typeof b.id_formatos_de_envases + ') match:', formatoMatch, 'disponibles:', b.envases_disponibles);
        return formatoMatch && b.envases_disponibles > 0;
    });

    console.log('[CAJAS] Batches filtrados:', batchesFiltrados);

    // Actualizar headers de tabla segun si es mixto
    var colSpan = crearCajasEsMixto ? 4 : 3;
    var headerHtml = '<tr><th>Batch</th>';
    if(crearCajasEsMixto) {
        headerHtml += '<th>Receta</th>';
    }
    headerHtml += '<th>Disponibles</th><th>Cantidad a usar</th></tr>';
    $('#crear-cajas-batches-table thead').html(headerHtml);

    if(batchesFiltrados.length == 0) {
        console.warn('[CAJAS] No hay batches disponibles para este formato');
        html = '<tr><td colspan="' + colSpan + '" class="text-center text-muted">No hay batches con ' + nombreTipoPlural + ' disponibles de este formato</td></tr>';
        $('#crear-cajas-crear-btn').prop('disabled', true);
    } else {
        batchesFiltrados.forEach(function(batch) {
            // Buscar info del batch de cerveza
            var batchInfo = batches.find(function(b) { return b.id == batch.id_batches; });
            var recetaInfo = recetas.find(function(r) { return r.id == batch.id_recetas; });
            var recetaNombre = recetaInfo ? recetaInfo.nombre : 'Sin receta';
            var nombre = recetaNombre + ' #' + (batchInfo ? batchInfo.batch_nombre : batch.id);

            html += '<tr data-batch-id="' + batch.id + '" data-disponibles="' + batch.envases_disponibles + '">';
            if(crearCajasEsMixto) {
                // Para mixtos, mostrar batch y receta separados
                html += '<td>#' + (batchInfo ? batchInfo.batch_nombre : batch.id) + '</td>';
                html += '<td><span class="badge bg-info">' + recetaNombre + '</span></td>';
            } else {
                html += '<td>' + nombre + '</td>';
            }
            html += '<td><span class="badge bg-success">' + batch.envases_disponibles + '</span></td>';
            html += '<td><input type="number" class="form-control form-control-sm crear-cajas-input" data-batch-id="' + batch.id + '" value="0" min="0" max="' + batch.envases_disponibles + '"></td>';
            html += '</tr>';
        });
    }

    $('#crear-cajas-batches-tbody').html(html);
    $('#crear-cajas-total-requerido').text(crearCajasCantidadRequerida);
    $('#crear-cajas-total-requerido-2').text(crearCajasCantidadRequerida);
    actualizarTotalAsignado();
}

// Al cambiar cantidades en inputs
$(document).on('change keyup', '.crear-cajas-input', function() {
    var $input = $(this);
    var max = parseInt($input.attr('max'));
    var val = parseInt($input.val()) || 0;

    // No permitir mas que el disponible
    if(val > max) {
        $input.val(max);
    }
    if(val < 0) {
        $input.val(0);
    }

    actualizarTotalAsignado();
});

function actualizarTotalAsignado() {
    var total = 0;
    var nombreTipoPlural = crearCajasTipoEnvase == 'Lata' ? 'latas' : 'botellas';

    $('.crear-cajas-input').each(function() {
        total += parseInt($(this).val()) || 0;
    });

    $('#crear-cajas-total-asignado').text(total);

    console.log('[CAJAS] Total asignado:', total, '/', crearCajasCantidadRequerida);

    // Validar
    if(total == crearCajasCantidadRequerida) {
        console.log('[CAJAS] Cantidad correcta - habilitando boton');
        $('#crear-cajas-warning').addClass('d-none');
        $('#crear-cajas-crear-btn').prop('disabled', false);
    } else if(total > crearCajasCantidadRequerida) {
        console.warn('[CAJAS] Exceso de ' + nombreTipoPlural + ':', total, '>', crearCajasCantidadRequerida);
        $('#crear-cajas-warning-text').text('Ha asignado mas ' + nombreTipoPlural + ' de las requeridas (' + total + ' > ' + crearCajasCantidadRequerida + ')');
        $('#crear-cajas-warning').removeClass('d-none');
        $('#crear-cajas-crear-btn').prop('disabled', true);
    } else {
        console.log('[CAJAS] Faltan ' + nombreTipoPlural + ':', crearCajasCantidadRequerida - total);
        $('#crear-cajas-warning-text').text('Faltan ' + (crearCajasCantidadRequerida - total) + ' ' + nombreTipoPlural + ' por asignar');
        $('#crear-cajas-warning').removeClass('d-none');
        $('#crear-cajas-crear-btn').prop('disabled', true);
    }
}

// Crear caja
$(document).on('click', '#crear-cajas-crear-btn', function(e) {
    e.preventDefault();
    console.log('[CAJAS] Boton Crear Caja clickeado');

    // Recopilar asignaciones
    var asignaciones = {};
    $('.crear-cajas-input').each(function() {
        var batchId = $(this).data('batch-id');
        var cantidad = parseInt($(this).val()) || 0;
        if(cantidad > 0) {
            asignaciones[batchId] = cantidad;
        }
    });

    console.log('[CAJAS] Asignaciones recopiladas:', asignaciones);

    var data = {
        csrf_token: csrfToken,
        id_productos: crearCajasProductoId,
        asignaciones: JSON.stringify(asignaciones)
    };

    console.log('[CAJAS] Enviando a ajax_crearCajaDeEnvases.php:', data);

    $.post('./ajax/ajax_crearCajaDeEnvases.php', data, function(response) {
        console.log('[CAJAS] Respuesta recibida:', response);
        if(response.status == 'OK') {
            console.log('[CAJAS] Exito - Caja creada:', response.codigo);
            window.location.href = './?s=inventario-de-productos&msg=1&msg_content=Caja ' + response.codigo + ' creada exitosamente';
        } else {
            console.error('[CAJAS] Error:', response.mensaje);
            alert('Error: ' + response.mensaje);
        }
    }, 'json').fail(function(xhr, status, error) {
        console.error('[CAJAS] AJAX Error:', { xhr: xhr, status: status, error: error });
        console.error('[CAJAS] Response Text:', xhr.responseText);
        alert('Error al crear la caja');
    });
});

// Reset modal al cerrar
$('#crear-cajas-modal').on('hidden.bs.modal', function() {
    $('#crear-cajas-step-1').show();
    $('#crear-cajas-step-2').hide();
    $('#crear-cajas-producto-select').val('');
    $('#crear-cajas-producto-info').addClass('d-none');
    $('#crear-cajas-siguiente-btn').prop('disabled', true);
    crearCajasProductoId = null;
    crearCajasFormatoId = null;
    crearCajasCantidadRequerida = 0;
    crearCajasEsMixto = false;
});

// ===========================
// REVERTIR ENVASADO
// ===========================

console.log('[REVERTIR] Sistema de reversion inicializado');

// Al hacer click en boton revertir
$(document).on('click', '.revertir-envasado-btn', function() {
    var $btn = $(this);
    var id = $btn.data('id');
    var tipo = $btn.data('tipo');
    var envases = $btn.data('envases');
    var total = $btn.data('total');
    var origen = $btn.data('origen');
    var volumen = $btn.data('volumen');
    var batchNombre = $btn.data('batch');

    var nombreTipoPlural = tipo == 'Lata' ? 'latas' : 'botellas';
    var verbo = tipo == 'Lata' ? 'Enlatado' : 'Embotellado';

    console.log('[REVERTIR] Click en revertir:', { id: id, tipo: tipo, envases: envases, total: total, origen: origen });

    // Validar que todos los envases esten disponibles
    if(envases != total) {
        alert('No se puede revertir: solo hay ' + envases + ' de ' + total + ' ' + nombreTipoPlural + ' disponibles. Las demas estan asignadas a cajas.');
        return false;
    }

    // Llenar datos en la modal
    $('#revertir-batch-id').val(id);
    $('#revertir-tipo').val(tipo);
    $('#revertir-modal-title').text('Revertir ' + verbo);
    $('#revertir-envases-count').text(envases);
    $('#revertir-envases-tipo-label').text(nombreTipoPlural);
    $('#revertir-volumen').text((volumen / 1000).toFixed(2));
    $('#revertir-origen').text(origen);
    $('#revertir-batch-nombre').text(batchNombre);

    // Abrir modal
    $('#revertir-envasado-modal').modal('show');
});

// Al confirmar reversion
$(document).on('click', '#revertir-envasado-confirmar-btn', function() {
    var id = $('#revertir-batch-id').val();
    var tipo = $('#revertir-tipo').val();
    var verbo = tipo == 'Lata' ? 'enlatado' : 'embotellado';

    console.log('[REVERTIR] Confirmando reversion del batch:', id, 'tipo:', tipo);

    var data = {
        id_batch_de_envases: id
    };

    $.post('./ajax/ajax_revertirEnvasado.php', data, function(response) {
        console.log('[REVERTIR] Respuesta:', response);
        if(response.status == 'OK') {
            console.log('[REVERTIR] Exito:', response.mensaje);
            window.location.href = './?s=inventario-de-productos&msg=1&msg_content=' + encodeURIComponent(response.mensaje);
        } else {
            console.error('[REVERTIR] Error:', response.mensaje);
            alert('Error: ' + response.mensaje);
        }
    }, 'json').fail(function(xhr, status, error) {
        console.error('[REVERTIR] AJAX Error:', { xhr: xhr, status: status, error: error });
        alert('Error al revertir el ' + verbo);
    });
});

// ===========================
// ELIMINAR CAJA
// ===========================

console.log('[ELIMINAR-CAJA] Sistema de eliminacion de cajas inicializado');

// Al hacer click en boton eliminar caja
$(document).on('click', '.eliminar-caja-btn', function() {
    var $btn = $(this);
    var id = $btn.data('id');
    var codigo = $btn.data('codigo');
    var producto = $btn.data('producto');
    var cantidad = $btn.data('cantidad');

    console.log('[ELIMINAR-CAJA] Click en eliminar:', { id: id, codigo: codigo, producto: producto, cantidad: cantidad });

    // Llenar datos en la modal
    $('#eliminar-caja-id').val(id);
    $('#eliminar-caja-codigo').text(codigo);
    $('#eliminar-caja-producto').text(producto);
    $('#eliminar-caja-cantidad').text(cantidad);

    // Abrir modal
    $('#eliminar-caja-modal').modal('show');
});

// Al confirmar eliminacion
$(document).on('click', '#eliminar-caja-confirmar-btn', function() {
    var id = $('#eliminar-caja-id').val();
    var codigo = $('#eliminar-caja-codigo').text();

    console.log('[ELIMINAR-CAJA] Confirmando eliminacion de caja:', id);

    var data = {
        id_caja: id
    };

    $.post('./ajax/ajax_eliminarCajaDeEnvases.php', data, function(response) {
        console.log('[ELIMINAR-CAJA] Respuesta:', response);
        if(response.status == 'OK') {
            console.log('[ELIMINAR-CAJA] Exito:', response.mensaje);
            window.location.href = './?s=inventario-de-productos&msg=1&msg_content=' + encodeURIComponent('Caja ' + codigo + ' eliminada. ' + response.envases_liberados + ' envases liberados.');
        } else {
            console.error('[ELIMINAR-CAJA] Error:', response.mensaje);
            alert('Error: ' + response.mensaje);
        }
    }, 'json').fail(function(xhr, status, error) {
        console.error('[ELIMINAR-CAJA] AJAX Error:', { xhr: xhr, status: status, error: error });
        alert('Error al eliminar la caja');
    });
});


</script>