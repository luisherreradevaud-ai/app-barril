<?php

  $batches_activos_fermentacion = BatchActivo::getAll("WHERE estado='Fermentación' AND litraje!=0");
  $ba_fermentacion = [];
  foreach($batches_activos_fermentacion as $baf) {
    $ba_fermentacion[$baf->id_batches][$baf->id] = $baf;
  }
  $batches_activos_maduracion = BatchActivo::getAll("WHERE estado='Maduración' ORDER BY id_batches asc");
  $ba_maduracion = [];
  foreach($batches_activos_maduracion as $baf) {
    $ba_maduracion[$baf->id_batches][$baf->id] = $baf;
  }
  $barriles_en_planta = Barril::getAll("WHERE id_batches!=0 AND estado='En planta' AND clasificacion='Cerveza' ORDER BY codigo asc");
  $barriles_en_terreno = Barril::getAll("WHERE id_batches!=0 AND estado!='En planta' AND clasificacion='Cerveza' ORDER BY codigo asc");
  $barriles_disponibles = Barril::getAll("WHERE litros_cargados!=litraje AND estado='En planta' AND clasificacion='Cerveza' ORDER BY codigo asc");

  $activos_traspaso = [];
  $activos_traspaso_disponibles = Activo::getAll("WHERE clase='Fermentador' AND id_batches='0' ORDER BY codigo asc");

  $batches = Batch::getAll();

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
<?php 
    Msg::show(1,$msg_content,'info');
    Msg::show(2,'Traspaso realizado con éxito.','info');
?>
<div class="d-sm-flex align-items-center justify-content-between mb-4">
  <div class="mb-2">
    <h1 class="h2 mb-0 text-gray-800"><b>Jefe de Planta</b></h1>
  </div>
</div>

<button class="btn btn-outline-primary me-2" id="traspasar-btn">
    Traspasar
</button>

<button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#llenar-barriles-modal" id="llenar-barriles-btn"> 
    Llenar Barril
</button>

<div class="row mt-3 d-none">
    <div class="col-md-6 mb-3">
        <div class="card shadow mb-3">
            <div class="card-header">
                <div class="card-title d-flex justify-content-between">
                    <h1 class="h3 mb-0 text-gray-800">Fermentación</h1>
                    <button class="btn btn-sm btn-outline-primary" id="traspasar-btn">
                        Traspasar
                    </button>
                </div>
            </div>
            <div class="card-body">
        <?php
        foreach($ba_fermentacion as $bas) {
            $batch = new Batch(end($bas)->id_batches);
            $receta = new Receta($batch->id_recetas);
            ?>
            
                    <div class="accordion mb-3" id="accordionExample<?= $batch->id; ?>">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingOne<?= $batch->id; ?>">
                                <button class="accordion-button collapsed fs-5" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne<?= $batch->id; ?>" aria-expanded="true" aria-controls="collapseOne<?= $batch->id; ?>" style="height: 30px">
                                    <div class="fw-bold me-2">
                                        Batch #<?= $batch->id; ?> <?= $receta->nombre; ?>
                                    </div>
                                    <div class="ms-3">
                                        <?= datetime2fechayhora($batch->creada); ?>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapseOne<?= $batch->id; ?>" class="accordion-collapse collapse" aria-labelledby="headingOne<?= $batch->id; ?>" data-bs-parent="#accordionExample<?= $batch->id; ?>">
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
                                                <td class="ps-3">
                                                    <?= $activo->codigo; ?>
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
                </div>
            </div>
            <div class="card-body">
        <?php
        foreach($ba_maduracion as $bas) {
            $batch = new Batch(end($bas)->id_batches);
            $receta = new Receta($batch->id_recetas);
            ?>
            
                    <div class="accordion mb-3" id="accordionExample<?= $batch->id; ?>">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingOne<?= $batch->id; ?>">
                                <button class="accordion-button collapsed fs-5" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne<?= $batch->id; ?>" aria-expanded="true" aria-controls="collapseOne<?= $batch->id; ?>" style="height: 30px">
                                    <div class="fw-bold">
                                        Batch #<?= $batch->id; ?> <?= $receta->nombre; ?>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapseOne<?= $batch->id; ?>" class="accordion-collapse collapse" aria-labelledby="headingOne<?= $batch->id; ?>" data-bs-parent="#accordionExample<?= $batch->id; ?>">
                                <div class="accordion-body p-0">
                                    <table class="table table-striped table-sm my-0">
                                        <thead>
                                            <th class="ps-3">
                                                Fermentador
                                            </th>
                                            <th>
                                                Fecha
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
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach($barriles_en_planta as $ba) {
                            $batch = new Batch($batch->id);
                            $receta = new Receta($batch->id_recetas);
                            ?>
                            <tr>
                                <td>
                                    <?= $ba->codigo; ?>
                                </td>
                                <td>
                                    <?= $receta->nombre; ?> #<?= $batch->id; ?> <?= date2fecha($batch->batch_date); ?>
                                </td>
                                <td>
                                    <?= $ba->litros_cargados; ?>L
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <h1 class="h3 mb-0 text-gray-800">Barriles en Terreno</h1>
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
                                    <?= $receta->nombre; ?> #<?= $batch->id; ?> <?= date2fecha($batch->batch_date); ?>
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
                            foreach($batches_activos_maduracion as $bam) {
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


<script>


const activos_traspaso = <?= json_encode($activos_traspaso,JSON_PRETTY_PRINT); ?>;
const activos_traspaso_disponibles = <?= json_encode($activos_traspaso_disponibles,JSON_PRETTY_PRINT); ?>;
const batches = <?= json_encode($batches, JSON_PRETTY_PRINT); ?>;
const recetas = <?= json_encode(Receta::getAll(), JSON_PRETTY_PRINT); ?>;
const barriles_disponibles = <?= json_encode($barriles_disponibles, JSON_PRETTY_PRINT); ?>;
const batches_activos_maduracion = <?= json_encode($batches_activos_maduracion, JSON_PRETTY_PRINT); ?>;

var id_fermentadores_inicio = 0;
var id_fermentadores_final = 0;
var id_batches = 0;
var date = '';
var hora = '';

$(document).on('click','#traspasar-btn',function(e){
    e.preventDefault();
    $('#traspasos-desde-select').val(activos_traspaso[0].id);
    $('#traspasos-hasta-select').val(activos_traspaso_disponibles[0].id);
    $('#traspaso-warning-label').hide();
    var activo_1 = activos_traspaso.find((a) => a.id == $('#traspasos-desde-select').val());
    const batch = batches.find((b) => b.id == activo_1.id_batches);
    id_batches = batch.id;
    const receta = recetas.find((r) => r.id == batch.id_recetas);
    $('#traspasos-batch').val(receta.nombre + ' #' + batch.id);

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
        window.location.href = './?s=jefe-de-planta&msg=2';

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
    const bam = batches_activos_maduracion.find((b) => b.id == $('#llenar-barriles_id_batches_activos-select').val());
    $('#llenar-barriles-fermentador-cantidad-disponible').val(bam.litraje);
    console.log(bam);
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
        window.location.href = './?s=jefe-de-planta&msg=1&msg_content=' + response.msg_content;
    });

});


</script>