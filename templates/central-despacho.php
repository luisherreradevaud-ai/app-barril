<?php


  //checkAutorizacion(["Jefe de Planta","Administrador"]);
  
  $msg = 0;
  if(isset($_GET['msg'])) {
    $msg = $_GET['msg'];
  }

  $despachos = Despacho::getAll("ORDER BY id desc");
  $usuario = $GLOBALS['usuario'];


  $barriles_disponibles = Barril::getAll("WHERE litros_cargados!=litraje AND estado='En planta' AND clasificacion='Cerveza' ORDER BY codigo asc");

  $batches_activos_maduracion = BatchActivo::getAll("JOIN activos 
        ON activos.id = batches_activos.id_activos
      WHERE activos.clase = 'Fermentador'
        AND batches_activos.litraje > 0
        AND (
          (activos.codigo LIKE 'BD%' 
            AND batches_activos.estado = 'Maduración')
          OR activos.codigo NOT LIKE 'BD%'
        )
      ORDER BY batches_activos.id_batches ASC");
  $ba_maduracion = [];
  foreach($batches_activos_maduracion as $baf) {
    $ba_maduracion[$baf->id_batches][$baf->id] = $baf;
  }


?>
<style>
.tr-despachos {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h4 mb-0 text-gray-800"><b>Central de Despacho</b></h1>
  </div>
  <div>
    <div>
      <a href="./?s=nuevo-despachos" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm mb-2"><i class="fas fa-fw fa-plus"></i> Nuevo Despacho</a>
    </div>
  </div>
</div>
<hr />
<?php
  if($msg == 1) {
?>
<div class="alert alert-info" role="alert" >Despacho creado con &eacute;xito.</div>
<?php
  }
  if($msg == 2) {
?>
<div class="alert alert-danger" role="alert" >Despacho eliminado con &eacute;xito.</div>
<?php
  }
  foreach($despachos as $despacho) {
    $repartidor = new Usuario($despacho->id_usuarios_repartidor);
    $productos = DespachoProducto::getAll("WHERE id_despachos='".$despacho->id."'");
?>
<div class="card w-100 shadow mb-5">
  <div class="card-body">
  <div class="row mb-3">
    <div class="col-md-4 mb-1">
      <h5 class="card-title mb-3"><i class="fas fa-fw fa-truck"></i> DESPACHO #<?= $despacho->id; ?></h5>
    </div>
    <div class="col-md-4 mb-1">
      Repartidor: <?= $repartidor->nombre; ?>
    </div>
    <div class="col-md-4 mb-1">
      Creado: <?= datetime2fechayhora($despacho->creada); ?>
    </div>
  </div>
    <table class="table table-striped table-sm mt-2">
      <?php
      foreach($productos as $dp) {
        ?>
        <tr>
          <td>
            <?= $dp->clasificacion; ?>
          </td>
          <td>
            <?= $dp->tipo; ?>
          </td>
          <td>
            <?= $dp->cantidad; ?>
          </td>
          <td>
            <?= $dp->tipos_cerveza; ?>
          </td>
          <td>
            <?= $dp->codigo; ?>
          </td>
        <?php
      }
       ?>
    </table>
    <?php
      if($usuario->nivel == "Administrador" ) {
    ?>
    <div class="mt-3 w-100 text-right">
      <button data-iddespachos="<?= $despacho->id; ?>" class="eliminar-despacho-btn btn btn-sm btn-danger">Eliminar Despacho</button>
    </div>
    <?php
      }
    ?>
  </div>
</div>
<?php
  }
?>


<div class="px-2">
  
  <br/>
  <div class="d-flex justify-content-between">
    <div>
      <h3>Barriles Cargados</h3>
    </div>
    <button class="btn btn-sm btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#llenar-barriles-modal" id="llenar-barriles-btn"> 
      Llenar Barril
    </button>
  </div>
  <table class="table table-sm table-striped table-bordered shadow">
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
      <tbody id="barriles-en-planta-id">
          <?php
          /*
          foreach($barriles_en_planta as $ba) {
              $batch = new Batch($ba->id_batches);
              $receta = new Receta($batch->id_recetas);
              //print_r($batch);
              ?>
              <tr data-idbarriles="<?= $ba->id; ?>">
                  <td>
                      <?= $ba->codigo; ?>
                  </td>
                  <td>
                      <?= $receta->nombre; ?> - #<?= $batch->id; ?>
                  </td>
                  <td>
                      <?= $ba->litros_cargados; ?>L
              </tr>
              <?php
          }*/
          ?>
      </tbody>
    </table>
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
                            <?php /*
                            foreach($batches_activos_maduracion as $bam) {
                                $activo = new Activo($bam->id_activos);
                                $batch = new Batch($bam->id_batches);
                                $receta = new Receta($batch->id_recetas);
                            ?>
                            <option value="<?= $bam->id; ?>"><?= $activo->codigo; ?> (<?= $activo->litraje; ?>L - <?= $receta->nombre; ?>)</option>
                            <?php
                            }*/
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
                            /*foreach($barriles_disponibles as $bd) {
                            ?>
                            <option value="<?= $bd->id; ?>"><?= $bd->codigo; ?></option>
                            <?php
                            }*/
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

<script>



$(document).on('click','.tr-despachos',function(e){
  window.location.href = "./?s=detalle-despachos&id=" + $(e.currentTarget).data('iddespachos');
});

$(document).on('click','.eliminar-despacho-btn',function(e){
  var data = {
    'id': $(e.currentTarget).data('iddespachos'),
    'modo': 'despachos'
  }
  var url = './ajax/ajax_eliminarEntidad.php';
  $.post(url,data,function(response){
    console.log(response);
    response = JSON.parse(response);
    if(response.status!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=central-despacho&msg=2";
    }
  }).fail(function(){
    alert("No funciono");
  });
});


// ----------------------------------------------------------
// ----------------------------------------------------------



let barriles_disponibles = <?= json_encode($barriles_disponibles, JSON_PRETTY_PRINT); ?>;
let batches_activos_maduracion = <?= json_encode($batches_activos_maduracion, JSON_PRETTY_PRINT); ?>;


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
        if(response.mensaje!="OK") {
            alert("Algo fallo");
            return false;
        } else {
            barriles_en_planta_2 = response.obj.barriles_en_planta;
            armarBarrilesEnPlanta();
            batches_activos_maduracion = response.obj.batches_activos_maduracion;
            armarBatchesActivosMaduracion();
            barriles_disponibles = response.obj.barriles_disponibles_para_carga;
            armarBarrilesDisponiblesParaCarga();
            
        }
    });

});

</script>
