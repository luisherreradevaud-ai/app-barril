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
.tr-gastos-fijos {
  cursor: pointer;
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
<table class="table table-hover table-striped table-sm">
  <thead class="thead-dark">
    <tr>
      <th>
      <a href="#" class="sort" data-sortorderby="item">&Iacute;tem</a>
      </th>
      <th>
      <a href="#" class="sort" data-sortorderby="por_pagar">Proyectado Neto</a>
      </th>
      <th>
      <a href="#" class="sort" data-sortorderby="por_pagar">Real Neto</a>
      </th>
    </tr>
  </thead>
  <tbody>
    <?php
      $total_proyectado_neto = 0;
      $total_real_neto = 0;
      $total_proyectado_impuesto = 0;
      $total_real_impuesto = 0;
      $total_proyectado_bruto = 0;
      $total_real_bruto = 0;
    
    foreach($tipos_de_gastos as $tdg) {
      ?>
    <thead class="thead-dark">
    <tr>
      <th colspan="4">
        <b><?= $tdg['obj']->nombre; ?></b>
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

        $total_proyectado_neto += $montos->proyectado_neto;
        $total_real_neto += $montos->real_neto;

        $total_proyectado_impuesto += $montos->proyectado_impuesto;
        $total_real_impuesto += $montos->real_impuesto;

        $total_proyectado_bruto += $montos->proyectado_bruto;
        $total_real_bruto += $montos->real_bruto;

    ?>
    <tr class="tr-gastos-fijos" data-idgastosfijos="<?= $gasto->id; ?>">
      <td>
        <?= $gasto->item; ?>
      </td>
      <td>
        $<?= number_format($montos->proyectado_neto); ?>
      </td>
      <td>
        $<?= number_format($montos->real_neto); ?>
      </td>
    </tr>
    <?php
      }
    }
  ?>
  </tbody>
  <tfoot>
    <tr>
      <td colspan="3">
        <hr/>
      </td>
    </tr>
    <tr>
      <td colspan="1">
        <b>Total Neto:</b>
      </td>
      <td><b>$<?= number_format($total_proyectado_neto); ?></td>
      <td><b>$<?= number_format($total_real_neto); ?></td>
    </tr>
    <tr>
      <td colspan="1">
        <b>Total Impuesto:</b>
      </td>
      <td><b>$<?= number_format($total_proyectado_impuesto); ?></td>
      <td><b>$<?= number_format($total_real_impuesto); ?></td>
    </tr>
    <tr class="table-bordered">
      <td colspan="1">
        <b>Total Bruto:</b>
      </td>
      <td><b>$<?= number_format($total_proyectado_bruto); ?></td>
      <td><b>$<?= number_format($total_real_bruto); ?></td>
    </tr>
  </tfoot>
</table>


<script>
var mes = '<?= intval($mes); ?>';
var ano = '<?= intval($ano); ?>';
var order_by = '<?= $order_by; ?>';
var order = '<?= $order; ?>';
var change_checkbox = [];

$(document).ready(function(){
  $('#mes-select').val(mes);
  $('#ano-select').val(ano);
});

$(document).on('change','.date-select', function(e) {
  window.location.href = "./?s=gastos-fijos&mes=" + $('#mes-select').val() + "&ano=" + $('#ano-select').val();
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

$(document).on('click','.gastos-checkbox',function(e){

  e.stopPropagation();

  change_checkbox = [];
  total = 0;
  total_monto = 0;

  $('.gastos-checkbox').each(function(){
    if($(this).is(':checked')){
      total += 1;
      change_checkbox.push($(this).data('idgastos'));
      var gasto_p = gastos.find((g) => g.id == $(this).data('idgastos'));
      total_monto += parseInt(gasto_p.monto);
      console.log(gasto_p);
    }
  })
  $('#gastos_checkbox_total').html(total);
  $('#gastos_checkbox_total_monto').html(total_monto.toLocaleString('en-US'));
  $('#accion-masiva-eliminar-modal-total').html(total);
  

});

$(document).on('click','.accion-masiva',function(e){

  if(change_checkbox.length == 0) {
    return 0;
  }

  if($(e.currentTarget).data('accion') == "marcar-como") {

    var url = "./ajax/ajax_cambiarEstadoGastos.php";
    var data = {
      'table_name': 'gastos',
      'ids_gastos': change_checkbox,
      'estado': $(e.currentTarget).data('estado')
    };

    console.log(data);

    $.post(url,data,function(response_raw){
      console.log(response_raw);
      var response = JSON.parse(response_raw);
      if(response.mensaje!="OK") {
        alert("Algo fallo");
        return false;
      } else {
        window.location.reload();
      }
    }).fail(function(){
      alert("No funciono");
    });
  } else
  if($(e.currentTarget).data('accion') == "eliminar") {
    $('#accion-masiva-eliminar-modal').modal('toggle');
  }

});



$(document).on('click','#accion-masiva-eliminar-btn',function(e){

  if(change_checkbox.length == 0) {
    return 0;
  }

  var url = "./ajax/ajax_accionMasiva.php";
  var data = {
    'table_name': 'gastos',
    'ids': change_checkbox,
    'accion': $(e.currentTarget).data('accion')
  };

  console.log(data);

  $.post(url,data,function(response_raw){
    console.log(response_raw);
    var response = JSON.parse(response_raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.reload();
    }
  }).fail(function(){
    alert("No funciono");
  });


});



  


</script>