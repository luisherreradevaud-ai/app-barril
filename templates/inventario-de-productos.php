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

  $batches = Batch::getAll('ORDER BY batch_nombre desc');

  $activos_disponibles = Activo::getAll("WHERE clase='Fermentador' AND id_batches='0' order by nombre asc");
  $activos = Activo::getAll("WHERE clase='Fermentador' order by nombre asc");

  $despachos = Despacho::getAll("ORDER BY id desc");


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
                                        </thead>
                                        <tbody>
                                        <?php
                                        foreach($bas as $ba) {
                                            $activo = new Activo($ba->id_activos);
                                            $activos_traspaso[] = $activo;
                                            ?>
                                            <tr>
                                                <td class="px-3 tr-fermentadores" style="cursor: pointer" data-idbatches="<?= $ba->id_batches; ?>" data-idactivos="<?= $ba->id_activos; ?>" data-idbatchesactivos="<?= $ba->id; ?>">
                                                    <div class="w-100 d-flex justify-content-between">
                                                        <div>
                                                            <?= $activo->codigo; ?>
                                                        </div>
                                                        <div>
                                                            <?= $ba->litraje; ?> L
                                                        </div>
                                                    </div>
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
                                                        ?>
                                                        <button
                                                        type="button"
                                                        class="btn btn-sm btn-default maduracion-eliminar-fermentadores-aceptar-btn" data-idbatches="<?= $ba->id_batches; ?>" data-idbatchesactivos="<?= $ba->id; ?>"
                                                        >
                                                            Eliminar
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
                            $batch = new Batch($batch->id);
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
                        <button type="button" class="btn btn-primary mt-3 w-100" id="llenar-barril-aceptar-btn" data-bs-dismiss="modal">
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
                            <option value="<?= $bd->id; ?>"><?= $bd->codigo; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                        <label class="mt-2">
                            Cargado:
                        </label>
                        <div class="input-group">
                            <input type="number" class="form-control fw-bold fs-3" id="llenar-barriles-barril-litros-cargados" READONLY>
                            <span class="input-group-text fs-3" id="basic-addon1" style="border-radius: 0px 10px 10px 0px">Litros</span>
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
                            foreach($activos as $activo) {
                            ?>
                            <option value="<?= $activo->id; ?>"><?= $activo->nombre; ?></option>
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




<script>


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
        'hora': $('#nuevo-traspasos-hora-input').val()
    };

    var url = './ajax/ajax_agregarTraspasosInventarioProductos.php';

    $.post(url,data,function(response){
        console.log(response);
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
}

$(document).on('change','#llenar-barriles_id_batches_activos-select',renderLlenarBarrilesFermentadores);
function renderLlenarBarrilesFermentadores() {
    const bam = batches_para_carga.find((b) => b.id == $('#llenar-barriles_id_batches_activos-select').val());
    $('#llenar-barriles-fermentador-cantidad-disponible').val(bam.litraje);
    //console.log(bam);
}

$(document).on('click','#llenar-barril-aceptar-btn',function(e){

    e.preventDefault();

    var data = {
        'id_batches_activos': $('#llenar-barriles_id_batches_activos-select').val(),
        'id_barriles': $('#llenar-barriles_id_barriles-select').val(),
        'cantidad_a_cargar': $('#llenar-barriles-cantidad-a-cargar').val()
    };

    console.log(data);

    var url = './ajax/ajax_llenarBarriles.php';
    $.post(url,data,function(response){
        console.log(response);
        response = JSON.parse(response);
        window.location.href = './?s=inventario-de-productos&msg=1&msg_content=' + response.msg_content;
    });

});








function armarFermentadoresSelect() {
    var fermentadores_select_html = '';
    fermentadores_disponibles.sort();
    fermentadores_disponibles.forEach(function(f){
        //console.log(f.codigo);
        //console.log(f);
        fermentadores_select_html += '<option value="' + f.id + '">' + f.codigo + '</option>';
    });
    console.log(fermentadores_select_html)
    console.log('fermentadores_disponibles[0].id',fermentadores_disponibles[0].id)
    $('#agregar-fermentadores_id_activos-select').html(fermentadores_select_html);
    $('#agregar-fermentadores_id_activos-select').val(fermentadores_disponibles[0].id)
    console.log($('#agregar-fermentadores_id_activos-select').val())
}


$(document).on('click','#agregar-fermentadores-btn',function(e){
    e.preventDefault();
    armarFermentadoresSelect();
    var id_activos = fermentadores_disponibles[0].id;
    var fermentador = fermentadores.find((f) => f.id == id_activos);
    console.log('id_activos',id_activos)
    console.log('fermentadores',fermentadores)
    console.log('fermentador',fermentador)
    $('#agregar-fermentadores-cantidad-input').val(fermentador.litraje);
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


var data_eliminar = null

$(document).on('click','.tr-fermentadores',function(e){

    e.preventDefault();

    data_eliminar = {
        'id_batches_activos': $(e.currentTarget).data('idbatchesactivos'),
        'id_batches': $(e.currentTarget).data('idbatches'),
        'id_activos': $(e.currentTarget).data('idactivos'),
        'estado': 'Fermentación'
    };

    $('#eliminar-fermentadores-modal').modal('toggle')
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




</script>