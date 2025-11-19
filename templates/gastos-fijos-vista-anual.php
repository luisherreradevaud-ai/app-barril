<?php

function cantidadDiasMes($mes,$ano) {
  $mes = intval($mes);
  if($mes<1||$mes>12) {
    return 0;
  }
  $cantidad_dias_mes[1] = 31;
  $cantidad_dias_mes[2] = 28;
  $cantidad_dias_mes[3] = 31;
  $cantidad_dias_mes[4] = 30;
  $cantidad_dias_mes[5] = 31;
  $cantidad_dias_mes[6] = 30;
  $cantidad_dias_mes[7] = 31;
  $cantidad_dias_mes[8] = 31;
  $cantidad_dias_mes[9] = 30;
  $cantidad_dias_mes[10] = 31;
  $cantidad_dias_mes[11] = 30;
  $cantidad_dias_mes[12] = 31;
  if($ano%4==0) {
    $cantidad_dias_mes[2] = 29;
  }
  return $cantidad_dias_mes[$mes];
}

$msg = 0;
if(isset($_GET['msg'])) {
$msg = $_GET['msg'];
}

$mes = date('m');
if(validaIdExists($_GET,'mes')) {
  $mes = $_GET['mes'];
}

$ano = date('Y');
if(validaIdExists($_GET,'ano')) {
  $ano = $_GET['ano'];
}

$primer_dia_mes = $ano."-".$mes."-01";
$ultimo_dia_mes = date($ano."-".$mes."-".cantidadDiasMes($mes,$ano));

$order_by = "date_vencimiento";
if(isset($_GET['order_by'])) {
  if($_GET['order_by'] == "id") {
    $order_by = "id";
  }
  if($_GET['order_by'] == "item") {
    $order_by = "item";
  }
  if($_GET['order_by'] == "tipo_de_gasto") {
    $order_by = "tipo_de_gasto";
  }
  if($_GET['order_by'] == "fecha") {
    $order_by = "date_vencimiento";
  }
  if($_GET['order_by'] == "monto") {
    $order_by = "monto";
  }
}

$order = "desc";
if(isset($_GET['order'])) {
  if($_GET['order'] == "asc") {
    $order = "asc";
  }
}

$usuario = $GLOBALS['usuario'];

$tdgs = TipoDeGasto::getAll("ORDER BY nombre asc");

$tipos_de_gastos = array();

foreach($tdgs as $tipo_de_gasto_obj) {
  $query = "WHERE tipo_de_gasto='".$tipo_de_gasto_obj->id."'";
  $gastos_arr = GastoFijo::getAll($query);
  $tipos_de_gastos[] = array(
    'obj' => $tipo_de_gasto_obj,
    'gastos' => $gastos_arr
  );
}

?>
<style>
.gastos-fijos-mes-td {
  cursor: pointer;
  transition: 0.4s;
}
.gastos-fijos-mes-td:hover {
  transform: scale(1.1);
}
</style>
<style>

.drop-zone {
    border: 2px dashed #ccc;
    padding: 20px;
    text-align: center;
    cursor: pointer;
    margin-bottom: 20px;
    height: 100px;
}

.drop-zone.dragover {
    border-color: #000;
    background-color: #e1e1e1;
}

#preview-container {
    display: flex;
    flex-wrap: wrap;
}

.preview {
    position: relative;
    margin: 10px;
}

.preview img {
    max-width: 100px;
    max-height: 100px;
    display: block;
}

.preview button {
    position: absolute;
    top: 0;
    right: 0;
    background: rgba(255, 255, 255, 0.8);
    border: none;
    cursor: pointer;
}

.imagen-subida {
    width: 100px;
    max-width: 100%;
    max-height: 100%;
    display: block;
    margin: auto; 
    transition: 0.4s;
    cursor: pointer;
}
.imagen-subida:hover {
    transform: scale(1.1);
    z-index: 100;
}


.lightbox {
    display: none;
    position: fixed;
    z-index: 3000;
    padding-top: 60px;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.9);
}

.lightbox-content {
    margin: auto;
    display: block;
    width: 80%;
    max-width: 700px;
    transition: transform 0.2s;
}

.lightbox-content:hover {
    transform: scale(1.05);
}

.close {
    position: absolute;
    top: 15px;
    right: 35px;
    color: #f1f1f1;
    font-size: 40px;
    font-weight: bold;
    transition: 0.3s;
    cursor: pointer;
}

.close:hover,
.close:focus {
    color: #bbb;
    text-decoration: none;
    cursor: pointer;
}

.caption {
    text-align: center;
    color: #ccc;
    padding: 10px 20px;
    height: 150px;
}

</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><b>Gastos Fijos</b></h1>
  </div>
  <div>
    <a href="./?s=nuevo-gastos" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm mb-2"><i class="fas fa-fw fa-plus"></i> Nuevo Gasto Variable</a>
    <a href="./?s=nuevo-gastos-fijos" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm mb-2"><i class="fas fa-fw fa-plus"></i> Nuevo Gasto Fijo</a>
  </div>
</div>
<div class="d-flex justify-content-between mb-3">
    <table style="max-width: 400px">
      <tr>
        <td>
          <select class="form-control date-select" id="ano-select">
            <?php
            for($i = 2023; $i<=date('Y')+1; $i++) {
              print "<option>".$i."</option>";
            }
            ?>
          </select>
        </td>
        <td>
          <button class="btn btn-sm btn-secondary" id="mostrar-proyectados-btn">Ocultar Proyectado</button>
        </td>
      </tr>
    </table>
    <?php
      //Widget::print('gastos-fijos-menu');
    ?>
</div>
<hr />
<?php
if($msg == 2) {
?>
<div class="alert alert-danger" role="alert" >Gasto eliminado.</div>
<?php
}
if($msg == 3) {
?>
<div class="alert alert-danger" role="alert" >Tipo de Gasto eliminado.</div>
<?php
}
if($msg == 6) {
?>
<div class="alert alert-info" role="alert" >Gastos modificados exitosamente.</div>
<?php
}
if($msg == 7) {
?>
<div class="alert alert-danger" role="alert" >Gastos eliminados exitosamente.</div>
<?php
}
?>
<?php
?>



<div class="container-fluid" style="overflow-x: scroll">
<table class="table table-hover table-striped table-bordered table-sm" style="width: 3000px" id="gastos-fijos-table">
  <thead class="thead-dark">
    <tr>
        <th style="width: 300px !important">
        </th>
        <?php
            for($i = 1; $i<=12; $i++) {
                ?>
                <th colspan="2" class="text-center meses-th" style="width: 600px !important; padding: 0px">
                    <!--<div class="btn-group">
                      <button class="btn btn-default btn-sm" type="button">
                        
                      </button>
                      <button type="button" class="btn btn-sm btn-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="visually-hidden">Toggle Dropdown</span>
                      </button>
                      <ul class="dropdown-menu">
                        
                      </ul>
                    </div>-->
                    <div class="btn-group">
                      <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <?= int2mes($i); ?>
                      </button>
                      <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="./?s=gastos-fijos&mes=<?= $i; ?>&ano=<?= $ano; ?>">Ver Detalle</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" id="copiar-gastos-fijos-meses-btn" data-mes="<?= $i; ?>" data-ano="<?= $ano; ?>">Copiar Gastos</a></li>
                      </ul>
                    </div>
                </th>
                <?php
            }
        ?>
    </tr>
    <tr>
      <th style="width: 300px !important">
      </th>
        <?php
            for($i = 1; $i<=12; $i++) {
                ?>
                <th style="width: 300px !important" class="proyectados-td">
                    Proy
                </th>
                <th style="width: 300px !important">
                    Real
                </th>
                <?php
            }
        ?>
    </tr>
  </thead>
  <tbody>

    <?php

      $gastos_fijos = array();
      $gastos_fijos_meses = array();
      $total_proyectado_neto = 0;
      $total_real_neto = 0;
      $total_proyectado_impuesto = 0;
      $total_real_impuesto = 0;
      $total_proyectado_bruto = 0;
      $total_real_bruto = 0;
    
    foreach($tipos_de_gastos as $tdg) {
      ?>
    <thead>
    <tr>
      <th colspan="25">
        <a href="./?s=detalle-tipos_de_gastos&id=<?= $tdg['obj']->id; ?>"><b><?= $tdg['obj']->nombre; ?></b></a>
      </th>
    </tr>
  </thead>
      <?php
      foreach($tdg['gastos'] as $gasto) {
        $gastos_fijos[] = $gasto;
        ?>
        <tr data-idgastosfijos="<?= $gasto->id; ?>">
            <td style="width: 300px !important">
                <a href="./?s=detalle-gastos-fijos&id=<?= $gasto->id; ?>"><?= $gasto->item; ?></a>
            </td>
            <?php
            for($i = 1; $i<=12; $i++) {
                $gasto->getTotalMes($i,$ano);
                $montos = $gasto->montos;
                $mes_proyectado_bruto = 0;
                $mes_real_bruto = 0;
                $montos_id = 0;
                $class_color = '';

                if($gasto->visible == 1) {
                  $gastos_fijos_meses[] = $gasto->montos;
                  $mes_proyectado_bruto = $montos->proyectado_neto;
                  $mes_real_bruto = $montos->real_bruto;
                  $montos_id = $montos->id;
                  if($montos->real_neto != 0) {
                    $class_color = 'table-info';
                  }
                }
                
                ?>
                <td class="gastos-fijos-mes-td proyectados-td <?= $class_color; ?>" data-mes="<?= $i; ?>" data-ano="<?= $ano; ?>" data-idgastosfijosmes="<?= $montos_id; ?>" data-idgastosfijos="<?= $gasto->id; ?>" data-monto="proyectado" data-bs-toggle="tooltip" data-bs-placement="top" title="Proyectado Neto de <?= $gasto->item; ?> en <?= int2mes($i)." ".$ano; ?>">
                    $<?= number_format($mes_proyectado_bruto); ?>
                </td>
                <td class="gastos-fijos-mes-td <?= $class_color; ?>" data-mes="<?= $i; ?>" data-ano="<?= $ano; ?>" data-idgastosfijosmes="<?= $montos_id; ?>" data-idgastosfijos="<?= $gasto->id; ?>" data-monto="real" data-bs-toggle="tooltip" data-bs-placement="top" title="Real Neto de <?= $gasto->item; ?> en <?= int2mes($i)." ".$ano; ?>">
                    $<?= number_format($mes_real_bruto); ?>
                </td>
                <?php
            }
    ?>
    </tr>
    <?php
      }
    }
  ?>
  </tbody>
</table>
</div>



'  <div class="modal fade" tabindex="-1" role="dialog" id="modificar-gastos-fijos-mes">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Modificar Gasto Fijo</h5>
          <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
            <i class="ki-duotone ki-cross fs-1">
              <span class="path1"></span>
              <span class="path2"></span>
            </i>
          </div>
        </div>
        <div class="modal-body">
        <form action="./php/modificar-gastos-fijos-mes.php" method="post" enctype="multipart/form-data" id="modificar-gastos-fijos-mes-form">
          <input type="hidden" name="id" value="" id="modificar-gastos-fijos-mes-id-input">
          <input type="hidden" name="id_gastos_fijos" value="" id="modificar-gastos-fijos-mes-id_gastos_fijos-input">
          <div class="row">
            <div class="col-3 mb-1">
              Ítem:
            </div>
            <div class="col-3 mb-1">
              <input type="text" class="form-control" value="" id="modificar-gastos-fijos-mes-item-input" READONLY>
            </div>
            <div class="col-3 mb-1">
              Tipo de Gasto:
            </div>
            <div class="col-3 mb-1">
              <select class="form-control" id="modificar-gastos-fijos-mes-tipo_de_gasto-select" READONLY>
                <?php
                  foreach($tdgs as $tdg) {
                    print "<option value='".$tdg->id."'>".$tdg->nombre."</option>";
                  }
                ?>
              </select>
            </div>
            <div class="col-3 mb-1">
              Mes:
            </div>
            <div class="col-3 mb-1">
              <select class="form-control" name="mes" id="modificar-gastos-fijos-mes-mes-select" READONLY>
                <?php
                  for($i = 1; $i <= 12; $i++) {
                    print "<option value='".$i."'>".int2mes($i)."</option>";
                  }
                ?>
              </select>
            </div>
            <div class="col-3 mb-1">
              Año:
            </div>
            <div class="col-3 mb-1">
              <select class="form-control" name="ano" id="modificar-gastos-fijos-mes-ano-select" READONLY>
                <?php
                  for($i = 2023; $i <= (date('Y') + 1); $i++) {
                    print "<option>".$i."</option>";
                  }
                ?>
              </select>
            </div>
            <div class="col-12 mb-1">
              <ul class="nav nav-tabs mb-5">
                <li class="nav-item">
                  <a class="nav-link active menu-item-gastos" aria-current="page" href="#" data-menuitemgastos="montos">Montos</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link menu-item-gastos" href="#" data-menuitemgastos="comprobantes">Comprobantes (<span id="comprobantes-cantidad">0</span>)</a>
                </li>
              </ul>
            </div>
          </div>
          <div class="row" id="modificar-gastos-fijos-mes-montos-div">
            <div class="col-6 mb-5">
              <b>Proyectado</b>
            </div>
            <div class="col-6 mb-5">
              <b>Real</b>
            </div>
            <div class="col-3 mb-1">
              Neto:
            </div>
            <div class="col-3 mb-1">
              <div class="input-group">
                <span class="input-group-text" id="basic-addon1" style="border-radius: 10px 0px 0px 10px">$</span>
                <input type="number" class="form-control acero modifica-bruto-variable" value="0" name="proyectado_neto" id="modificar-gastos-fijos-mes-proyectado_neto-input">
              </div>
            </div>
            <div class="col-3 mb-1">
              Neto:
            </div>
            <div class="col-3 mb-1">
              <div class="input-group">
                <span class="input-group-text" id="basic-addon1" style="border-radius: 10px 0px 0px 10px">$</span>
                <input type="number" class="form-control acero modifica-bruto-variable" value="0" name="real_neto" id="modificar-gastos-fijos-mes-real_neto-input">
              </div>
            </div>
            <div class="col-3 mb-1">
              Impuesto:
            </div>
            <div class="col-3 mb-1">
              <div class="input-group">
                <span class="input-group-text" id="basic-addon1" style="border-radius: 10px 0px 0px 10px">$</span>
                <input type="number" class="form-control acero modifica-bruto-variable" value="0" name="proyectado_impuesto" id="modificar-gastos-fijos-mes-proyectado_impuesto-input">
              </div>
            </div>
            <div class="col-3 mb-1">
              Impuesto:
            </div>
            <div class="col-3 mb-1">
              <div class="input-group">
                <span class="input-group-text" id="basic-addon1" style="border-radius: 10px 0px 0px 10px">$</span>
                <input type="number" class="form-control acero modifica-bruto-variable" value="0" name="real_impuesto" id="modificar-gastos-fijos-mes-real_impuesto-input">
              </div>
            </div>
            <div class="col-3 mb-1">
              Bruto:
            </div>
            <div class="col-3 mb-1">
              <div class="input-group">
                <span class="input-group-text" id="basic-addon1" style="border-radius: 10px 0px 0px 10px">$</span>
                <input type="number" class="form-control acero modifica-bruto-variable" value="0" name="proyectado_bruto" id="modificar-gastos-fijos-mes-proyectado_bruto-input">
              </div>
            </div>
            <div class="col-3 mb-1">
              Bruto:
            </div>
            <div class="col-3 mb-1">
              <div class="input-group">
                <span class="input-group-text" id="basic-addon1" style="border-radius: 10px 0px 0px 10px">$</span>
                <input type="number" class="form-control acero modifica-bruto-variable" value="0" name="real_bruto" id="modificar-gastos-fijos-mes-real_bruto-input">
              </div>
            </div>
          </div>
          <div class="row" id="modificar-gastos-fijos-mes-comprobantes-div">
            <div class="col-12">
              <div id="comprobantes-aviso">No se han agregado comprobantes aún.</div>
              <div style="overflow-x: scroll; justify-content: space-evenly; width: 100%; display: flex" id="mostrar-media-div">
              </div>
              <div class="drop-zone mt-4" id="drop-zone">
                  Arrastra y suelta archivos aquí o haz clic para seleccionar
                  <input type="file" id="file-input" name="images[]" multiple accept="image/*" style="display: none" REQUIRED>
              </div>
              <div id="preview-container">
              </div>
              
            </div>
          </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="modificar-gastos-fijos-mes-aceptar-btn">Aceptar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" tabindex="-1" role="dialog" id="copiar-gastos-fijos-mes-modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Copiar Gastos Fijos</h5>
          <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
            <i class="ki-duotone ki-cross fs-1">
              <span class="path1"></span>
              <span class="path2"></span>
            </i>
          </div>
        </div>
        <div class="modal-body">
          <div class="row mb-3">
            <div class="col-6">
              <div class="row">
                <div class="col-12 mt-1 mb-3">
                  <b>Copiando:</b>
                </div>
                <div class="col-6 mb-1">
                  Mes:
                </div>
                <div class="col-6 mb-1">
                  <select class="form-control" name="mes" id="copiar-gastos-fijos-copiando-mes-select">
                    <?php
                      for($i = 1; $i <= 12; $i++) {
                        print "<option value='".$i."'>".int2mes($i)."</option>";
                      }
                    ?>
                  </select>
                </div>
                <div class="col-6 mb-1">
                  Año:
                </div>
                <div class="col-6 mb-1">
                  <select class="form-control" name="ano" id="copiar-gastos-fijos-copiando-ano-select">
                    <?php
                      for($i = 2023; $i <= (date('Y') + 1); $i++) {
                        print "<option>".$i."</option>";
                      }
                    ?>
                  </select>
                </div>
              </div>
            </div>
            <div class="col-6">
              <div class="row">
                <div class="col-12 mt-1 mb-3">
                  <b>Copiar a:</b>
                </div>
                <div class="col-6 mb-1">
                  Mes:
                </div>
                <div class="col-6 mb-1">
                  <select class="form-control" name="mes" id="copiar-gastos-fijos-copiar-a-mes-select">
                    <?php
                      for($i = 1; $i <= 12; $i++) {
                        print "<option value='".$i."'>".int2mes($i)."</option>";
                      }
                    ?>
                  </select>
                </div>
                <div class="col-6 mb-1">
                  Año:
                </div>
                <div class="col-6 mb-1">
                  <select class="form-control" name="ano" id="copiar-gastos-fijos-copiar-a-ano-select">
                    <?php
                      for($i = 2023; $i <= (date('Y') + 1); $i++) {
                        print "<option>".$i."</option>";
                      }
                    ?>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="copiar-gastos-fijos-mes-aceptar-btn">Copiar</button>
        </div>
      </div>
    </div>
  </div>

  <div id="lightbox" class="lightbox">
      <span class="close">&times;</span>
      <img class="lightbox-content" id="lightbox-img">
      <div class="caption" id="caption"></div>
  </div>

<script>

var ano = '<?= intval($ano); ?>';
var order_by = '<?= $order_by; ?>';
var order = '<?= $order; ?>';
var gastos_fijos = <?= json_encode($gastos_fijos,JSON_PRETTY_PRINT); ?>;
var gastos_fijos_meses = <?= json_encode($gastos_fijos_meses,JSON_PRETTY_PRINT); ?>;
var media = [];
var id_gastos_fijos_mes = '<?= (validaIdExists($_GET,'id_gastos_fijos_mes')) ? $_GET['id_gastos_fijos_mes'] : 0; ?>';
var mostrar_proyectados = true;

$(document).ready(function(){
  $('#ano-select').val(ano);
  $('#modificar-gastos-fijos-mes-comprobantes-div').hide();

  $('.proyectados-td').hide(200);
  $('.meses-th').attr('colspan','1');

});


$(document).on('click','#mostrar-proyectados-btn',function(e){

  e.preventDefault();
  if(mostrar_proyectados) {
    $(e.currentTarget).html('Ocultar Proyectados');
    $('.proyectados-td').show(200);
    $('.meses-th').attr('colspan','2');
    $('#gastos-fijos-table').css('width','3000px');
  } else {
    $(e.currentTarget).html('Mostrar Proyectados');
    $('.proyectados-td').hide(200);
    $('.meses-th').attr('colspan','1');
    $('#gastos-fijos-table').css('width','3000px');
  }
  mostrar_proyectados = !mostrar_proyectados;

});

$(document).on('change','.date-select', function(e) {
  window.location.href = "./?s=gastos-fijos-vista-anual&ano=" + $('#ano-select').val();
});

function openModalGastosFijosMes(id_gastos_fijos_mes) {

  var gasto_fijo_mes = gastos_fijos_meses.find((g2) => g2!=null && (g2.id == id_gastos_fijos_mes));
  console.log("gasto_fijo_mes: ");
  console.log(gasto_fijo_mes);

  var gasto_fijo = gastos_fijos.find((gf) => gf.id == gasto_fijo_mes.id_gastos_fijos);
  console.log("gasto_fijo: ");
  console.log(gasto_fijo);


  $('#modificar-gastos-fijos-mes-item-input').val(gasto_fijo.item);
  $('#modificar-gastos-fijos-mes-tipo_de_gasto-select').val(gasto_fijo.tipo_de_gasto);
  $('#modificar-gastos-fijos-mes-id_gastos_fijos-input').val(gasto_fijo.id);

  $('#modificar-gastos-fijos-mes-id-input').val(gasto_fijo_mes.id);
  $('#modificar-gastos-fijos-mes-mes-select').val(gasto_fijo_mes.mes);
  $('#modificar-gastos-fijos-mes-ano-select').val(gasto_fijo_mes.ano);
  $('#modificar-gastos-fijos-mes-proyectado_neto-input').val(gasto_fijo_mes.proyectado_neto);
  $('#modificar-gastos-fijos-mes-real_neto-input').val(gasto_fijo_mes.real_neto);
  $('#modificar-gastos-fijos-mes-proyectado_impuesto-input').val(gasto_fijo_mes.proyectado_impuesto);
  $('#modificar-gastos-fijos-mes-real_impuesto-input').val(gasto_fijo_mes.real_impuesto);
  $('#modificar-gastos-fijos-mes-proyectado_bruto-input').val(gasto_fijo_mes.proyectado_bruto);
  $('#modificar-gastos-fijos-mes-real_bruto-input').val(gasto_fijo_mes.real_bruto);

  console.log(gasto_fijo_mes);


  $('.menu-item-gastos').each(function(mi){
    $(this).removeClass('active');
  });
  $('.menu-item-gastos[data-menuitemgastos="montos"]').addClass('active');

  $('#modificar-gastos-fijos-mes-montos-div').show();
  $('#modificar-gastos-fijos-mes-comprobantes-div').hide();
  

  $('#comprobantes-aviso').show();
  $('#comprobantes-cantidad').html('0');


  var url = './ajax/ajax_getMedia.php';
  var data = {
    'entidad': 'gastos_fijos_mes',
    'id': id_gastos_fijos_mes
  }
  $.post(url,data,function(raw){
    console.log(raw);
    var response = JSON.parse(raw);
    if(response.mensaje=="OK") {
      media = response.media;
      mostrarMedia();
      $('#modificar-gastos-fijos-mes').modal('toggle');
    } else {
      alert("Algo fallo");
      return false;
    }
  }).fail(function(){
    alert("No funciono");
  });

}




$(document).on('click','.gastos-fijos-mes-td',function(e){

  console.log("id_gastos_fijos: " + $(e.currentTarget).data('idgastosfijos'));
  console.log("id_gastos_fijos_mes: " + $(e.currentTarget).data('idgastosfijosmes'));

  var gasto_fijo = gastos_fijos.find((g) => g.id == $(e.currentTarget).data('idgastosfijos'));
  console.log("gasto_fijo: ");
  console.log(gasto_fijo);

  var gasto_fijo_mes = gastos_fijos_meses.find((g2) => g2!=null && (g2.id == $(e.currentTarget).data('idgastosfijosmes')));
  console.log("gasto_fijo_mes: ");
  console.log(gasto_fijo_mes);


  $('#modificar-gastos-fijos-mes-item-input').val(gasto_fijo.item);
  $('#modificar-gastos-fijos-mes-tipo_de_gasto-select').val(gasto_fijo.tipo_de_gasto);
  $('#modificar-gastos-fijos-mes-id_gastos_fijos-input').val(gasto_fijo.id);

  if($(e.currentTarget).data('idgastosfijosmes') != 0) {
    $('#modificar-gastos-fijos-mes-id-input').val(gasto_fijo_mes.id);
    $('#modificar-gastos-fijos-mes-mes-select').val(gasto_fijo_mes.mes);
    $('#modificar-gastos-fijos-mes-ano-select').val(gasto_fijo_mes.ano);
    $('#modificar-gastos-fijos-mes-proyectado_neto-input').val(gasto_fijo_mes.proyectado_neto);
    $('#modificar-gastos-fijos-mes-real_neto-input').val(gasto_fijo_mes.real_neto);
    $('#modificar-gastos-fijos-mes-proyectado_impuesto-input').val(gasto_fijo_mes.proyectado_impuesto);
    $('#modificar-gastos-fijos-mes-real_impuesto-input').val(gasto_fijo_mes.real_impuesto);
    $('#modificar-gastos-fijos-mes-proyectado_bruto-input').val(gasto_fijo_mes.proyectado_bruto);
    $('#modificar-gastos-fijos-mes-real_bruto-input').val(gasto_fijo_mes.real_bruto);
  }  else {
    $('#modificar-gastos-fijos-mes-mes-select').val($(e.currentTarget).data('mes'));
    $('#modificar-gastos-fijos-mes-ano-select').val($(e.currentTarget).data('ano'));
    $('#modificar-gastos-fijos-mes-proyectado_neto-input').val(0);
    $('#modificar-gastos-fijos-mes-real_neto-input').val(0);
    $('#modificar-gastos-fijos-mes-proyectado_impuesto-input').val(0);
    $('#modificar-gastos-fijos-mes-real_impuesto-input').val(0);
    $('#modificar-gastos-fijos-mes-proyectado_bruto-input').val(0);
    $('#modificar-gastos-fijos-mes-real_bruto-input').val(0);
  }

  $('.menu-item-gastos').each(function(mi){
    $(this).removeClass('active');
  });
  $('.menu-item-gastos[data-menuitemgastos="montos"]').addClass('active');

  $('#modificar-gastos-fijos-mes-montos-div').show();
  $('#modificar-gastos-fijos-mes-comprobantes-div').hide();
  
  $('#modificar-gastos-fijos-mes').modal('toggle');

  if($(e.currentTarget).data('monto') == "proyectado") {
    $('#modificar-gastos-fijos-mes-proyectado_neto-input').focus();
  } else {
    $('#modificar-gastos-fijos-mes-real_neto-input').focus();
  }

  $('#comprobantes-aviso').show();
  $('#comprobantes-cantidad').html('0');

  if($(e.currentTarget).data('idgastosfijosmes') != 0) {

    var url = './ajax/ajax_getMedia.php';
    var data = {
      'entidad': 'gastos_fijos_mes',
      'id': $(e.currentTarget).data('idgastosfijosmes')
    }
    $.post(url,data,function(raw){
      console.log(raw);
      var response = JSON.parse(raw);
      if(response.mensaje=="OK") {
        media = response.media;
        mostrarMedia();
      } else {
        alert("Algo fallo");
        return false;
      }
    }).fail(function(){
      alert("No funciono");
    });

  }
  

});

function mostrarMedia() {
  var html = '';
  var cantidad = 0;
  media.forEach(function(m) {
    if(m.id != 0) {
      html += '<img src="./media/images/' + m.url + '" class="imagen-subida" data-idmedia="' + m.id + '">';
      cantidad++;
    }
  });
  if(cantidad > 0) {
    $('#comprobantes-aviso').hide();
    $('#comprobantes-cantidad').html(cantidad);
  }
  $('#mostrar-media-div').html(html);
}


$(document).on('click','#modificar-gastos-fijos-mes-aceptar-btn',function(e){

  $('#modificar-gastos-fijos-mes-form').submit();

});


$(document).on('click','#copiar-gastos-fijos-meses-btn',function(e){

  $('#copiar-gastos-fijos-copiando-mes-select').val($(e.currentTarget).data('mes'));
  $('#copiar-gastos-fijos-copiando-ano-select').val($(e.currentTarget).data('ano'));
  var siguiente_mes = 0;
  var siguiente_ano = 0;
  if($(e.currentTarget).data('mes') < 12) {
    siguiente_mes = parseInt($(e.currentTarget).data('mes')) + 1;
    siguiente_ano = parseInt($(e.currentTarget).data('ano'));
  } else {
    siguiente_mes = 1;
    siguiente_ano = parseInt($(e.currentTarget).data('ano')) + 1;
  }
  $('#copiar-gastos-fijos-copiar-a-mes-select').val(siguiente_mes);
  $('#copiar-gastos-fijos-copiar-a-ano-select').val(siguiente_ano);
  $('#copiar-gastos-fijos-mes-modal').modal('toggle');
});

$(document).on('click','#copiar-gastos-fijos-mes-aceptar-btn',function(e){


    var url = "./ajax/ajax_copiarGastosFijos.php";

    var data = {
      'copiando_mes': $('#copiar-gastos-fijos-copiando-mes-select').val(),
      'copiando_ano': $('#copiar-gastos-fijos-copiando-ano-select').val(),
      'copiar_a_mes': $('#copiar-gastos-fijos-copiar-a-mes-select').val(),
      'copiar_a_ano': $('#copiar-gastos-fijos-copiar-a-ano-select').val(),
      'confirmacion': 0
    };

    console.log(data);

    $.post(url,data,function(raw){
      console.log(raw);
      var response = JSON.parse(raw);
      if(response.mensaje == "CONFIRMAR") {
          $('#copiar-gastos-fijos-mes-modal').modal('toggle');
          $('#copiar-gastos-fijos-mes-confirmar-modal').modal('toggle');
      } else
      if(response.mensaje=="OK") {
        window.location.reload();
      } else {
        alert("Algo fallo");
        return false;
      }
    }).fail(function(){
      alert("No funciono");
    });

});


$(document).on('click','.menu-item-gastos',function(e){
  $('.menu-item-gastos').each(function(mi){
    $(this).removeClass('active');
  });
  $(e.currentTarget).addClass('active');

  if($(e.currentTarget).data('menuitemgastos') == 'montos') {
    $('#modificar-gastos-fijos-mes-montos-div').show();
    $('#modificar-gastos-fijos-mes-comprobantes-div').hide();
  } else
  if($(e.currentTarget).data('menuitemgastos') == 'comprobantes') {
    $('#modificar-gastos-fijos-mes-montos-div').hide();
    $('#modificar-gastos-fijos-mes-comprobantes-div').show();
  }
});
  
document.addEventListener('DOMContentLoaded', () => {
  const dropZone = document.getElementById('drop-zone');
  const fileInput = document.getElementById('file-input');
  const previewContainer = document.getElementById('preview-container');

  dropZone.addEventListener('click', () => {
      fileInput.click();
  });

  fileInput.addEventListener('change', handleFiles);

  dropZone.addEventListener('dragover', (e) => {
      e.preventDefault();
      dropZone.classList.add('dragover');
  });

  dropZone.addEventListener('dragleave', () => {
      dropZone.classList.remove('dragover');
  });

  dropZone.addEventListener('drop', (e) => {
      e.preventDefault();
      dropZone.classList.remove('dragover');
      const files = e.dataTransfer.files;
      handleFiles(files);
      updateFileInput(files);
  });

  function handleFiles(files) {
      files = files.length ? files : fileInput.files;
      [...files].forEach(file => {
          if (!file.type.startsWith('image/')) return;

          const reader = new FileReader();
          reader.onload = (e) => {
              const preview = document.createElement('div');
              preview.classList.add('preview');

              const img = document.createElement('img');
              img.src = e.target.result;

              const button = document.createElement('button');
              button.textContent = 'X';
              button.addEventListener('click', () => {
                  previewContainer.removeChild(preview);
              });

              preview.appendChild(img);
              preview.appendChild(button);
              previewContainer.appendChild(preview);
          };
          reader.readAsDataURL(file);
      });
  }

  function updateFileInput(files) {
      const dataTransfer = new DataTransfer();
      [...files].forEach(file => {
          dataTransfer.items.add(file);
      });
      fileInput.files = dataTransfer.files;
  }
});


$(document).on('click','.imagen-subida',function(e){
    console.log(media);
    console.log($(e.currentTarget).data('idmedia'));
    var imagen = media.find((m) => m.id == $(e.currentTarget).data('idmedia'));
    $('#lightbox-img').attr('src','./media/images/' + imagen.url);
    $('#lightbox').show(200);
});

$(document).on('click','.close',function(e){
    $('#lightbox').hide();
});

</script>