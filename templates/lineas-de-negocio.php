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

$lineas_de_negocio = LineaDeNegocio::getAll("ORDER BY nombre asc");



?>
<style>
.tr-gastos-fijos {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <table style="max-width: 400px">
      <tr>
        <td>
          <select class="form-control date-select me-1" id="mes-select" style="max-width: 200px">
            <?php
            for($i = 1; $i<=12; $i++) {
              print "<option value='".$i."'>".int2mes($i)."</option>";
            }
            ?>
          </select>
        </td>
        <td>
          <select class="form-control date-select" id="ano-select">
            <?php
            for($i = 2023; $i<=date('Y'); $i++) {
              print "<option>".$i."</option>";
            }
            ?>
          </select>
        </td>
      </tr>
    </table>
  </div>
  <div>
    <button class="d-sm-inline-block btn btn-sm btn-primary shadow-sm mb-2" id="nueva-lineas-de-negocio-btn"><i class="fas fa-fw fa-plus"></i> Nueva Linea de Negocio</button>
  </div>
</div>
<hr />

<table class="table table-hover table-striped table-sm">
  <thead class="thead-dark">
    <tr>
      <th>
      <a href="#" class="sort" data-sortorderby="item">&Iacute;tem</a>
      </th>
      <?php
        foreach($lineas_de_negocio as $linea_de_negocio) {
            ?>
            <th>
                <a href="./?s=detalle-lineas-de-negocio&id=<?= $linea_de_negocio->id; ?>">
                    <?= $linea_de_negocio->nombre; ?>
                </a>
            </th>
            <?php
        }
      ?>
    </tr>
  </thead>
  <tbody>
    <?php

    foreach($lineas_de_negocio as $linea_de_negocio) {
        $totales_lineas_de_negocio[$linea_de_negocio->id]['neto'] = 0;
        $totales_lineas_de_negocio[$linea_de_negocio->id]['impuesto'] = 0;
        $totales_lineas_de_negocio[$linea_de_negocio->id]['bruto'] = 0;
    }

    
    foreach($tipos_de_gastos as $tdg) {
      ?>
    <thead class="thead-dark">
    <tr>
      <th colspan="4">
        <b><a href="/?s=detalle-tipos_de_gastos&id=<?= $tdg['obj']->id; ?>"><?= $tdg['obj']->nombre; ?></a></b>
      </th>
    </tr>
  </thead>

      <?php
      
      foreach($tdg['gastos'] as $gasto) {
        $gasto->getTotalMes($mes,$ano);

        if($gasto->visible == 0) {
          continue;
        }

        $montos = $gasto->montos;

        foreach($lineas_de_negocio as $linea_de_negocio) {
            $montos_lineas_de_negocio[$linea_de_negocio->id] = 0;
        }

        $gasto_lineas_de_negocio = GastoLineaDeNegocio::getAll("WHERE id_gastos_fijos='".$gasto->id."'");
        foreach($gasto_lineas_de_negocio as $gldn) {
            $montos_lineas_de_negocio[$gldn->id_lineas_de_negocio] += floor((($montos->real_neto)*$gldn->porcentaje)/100);
            $totales_lineas_de_negocio[$gldn->id_lineas_de_negocio]['neto'] += floor((($montos->real_neto)*$gldn->porcentaje)/100);
            $totales_lineas_de_negocio[$gldn->id_lineas_de_negocio]['impuesto'] += floor((($montos->real_impuesto)*$gldn->porcentaje)/100);
            $totales_lineas_de_negocio[$gldn->id_lineas_de_negocio]['bruto'] += floor((($montos->real_bruto)*$gldn->porcentaje)/100);
        }

    ?>
    <tr class="tr-gastos-fijos" data-idgastosfijos="<?= $gasto->id; ?>">
      <td>
        <?= $gasto->item; ?>
      </td>
      <?php
        foreach($lineas_de_negocio as $linea_de_negocio) {
            ?>
            <td>    
                $<?= number_format($montos_lineas_de_negocio[$linea_de_negocio->id]); ?>
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
  <tfoot>
    <tr>
      <td colspan="1">
        <b>Total Neto:</b>
      </td>
      <?php
        foreach($lineas_de_negocio as $linea_de_negocio) {
            ?>
            <td>    
                $<?= number_format($totales_lineas_de_negocio[$linea_de_negocio->id]['neto']); ?>
            </td>
            <?php
        }
      ?>
    </tr>
    <tr>
      <td colspan="1">
        <b>Total Impuesto:</b>
      </td>
      <?php
        foreach($lineas_de_negocio as $linea_de_negocio) {
            ?>
            <td>    
                $<?= number_format($totales_lineas_de_negocio[$linea_de_negocio->id]['impuesto']); ?>
            </td>
            <?php
        }
      ?>
    </tr>
    <tr class="table-bordered">
      <td colspan="1">
        <b>Total Bruto:</b>
      </td>
      <?php
        foreach($lineas_de_negocio as $linea_de_negocio) {
            ?>
            <td>    
                $<?= number_format($totales_lineas_de_negocio[$linea_de_negocio->id]['bruto']); ?>
            </td>
            <?php
        }
      ?>
    </tr>
  </tfoot>
</table>

<div class="modal fade" tabindex="-1" role="dialog" id="nueva-lineas-de-negocio-modal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Nueva Linea de Negocio</h5>
        <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
            <i class="ki-duotone ki-cross fs-1">
              <span class="path1"></span>
              <span class="path2"></span>
            </i>
          </div>
      </div>
      <div class="modal-body">
        <form id="lineas-de-negocio-form">
          <input type="hidden" name="id" value="">
          <input type="hidden" name="entidad" value="lineas_de_negocio">
        <div class="row">
          <div class="col-6">
            Nombre:
          </div>
          <div class="col-6">
            <input type="text" class="form-control" name="nombre">
          </div>
        </div>
      </div>
      </form>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="nueva-lineas-de-negocio-aceptar-btn" data-bs-dismiss="modal"><i class="fas fa-fw fa-save"></i> Guardar</button>
      </div>
    </div>
  </div>
</div>




<script>

var mes = '<?= intval($mes); ?>';
var ano = '<?= intval($ano); ?>';
var order_by = '<?= $order_by; ?>';
var order = '<?= $order; ?>';

$(document).ready(function(){
  $('#mes-select').val(mes);
  $('#ano-select').val(ano);
});

$(document).on('change','.date-select', function(e) {
  window.location.href = "./?s=lineas-de-negocio&mes=" + $('#mes-select').val() + "&ano=" + $('#ano-select').val();
});


$(document).on('click','.tr-gastos-fijos',function(e) {
    window.location.href = "./?s=detalle-gastos-fijos&id=" + $(e.currentTarget).data('idgastosfijos') +"&mes=" + mes + "&ano=" + ano;
});

$(document).on('click','.sort',function(e){
  if(order == "asc") {
    order = "desc";
  } else {
    order = "asc";
  }
  window.location.href = "./?s=gastos&mes=" + mes + "&ano=" + ano + "&order_by=" + $(e.currentTarget).data('sortorderby') + "&order=" + order;
});



$(document).on('click','#nueva-lineas-de-negocio-btn',function(e){

  e.preventDefault();
  $('#lineas-de-negocio-form input[name="nombre"]').val('');
  $('#nueva-lineas-de-negocio-modal').modal('toggle');

});
  
$(document).on('click','#nueva-lineas-de-negocio-aceptar-btn',function(e){
  
  e.preventDefault();

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("lineas-de-negocio");
  console.log(data);

  $.post(url,data,function(raw){
    console.log(raw);
    var response = JSON.parse(raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=lineas-de-negocio&msg=1";
    }
  }).fail(function(){
    alert("No funciono");
  });
})

</script>