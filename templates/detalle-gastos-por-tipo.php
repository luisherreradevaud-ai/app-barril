<?php

//checkAutorizacion(["Administrador","Jefe de Planta"]);


if(!validaIdExists($_GET,'id')) {
    die();
}

$tipo_de_gasto = new TipoDeGasto($_GET['id']);

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

$primer_dia_mes = $ano."-".$mes."-1";
$ultimo_dia_mes = date($ano."-".$mes."-t");

$order_by = "id";
if(isset($_GET['order_by'])) {
  if($_GET['order_by'] == "item") {
    $order_by = "item";
  }
  if($_GET['order_by'] == "tipo_de_gasto") {
    $order_by = "tipo_de_gasto";
  }
  if($_GET['order_by'] == "fecha") {
    $order_by = "creada";
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
if($usuario->nivel == "Administrador") {
  $gastos_atrasados = Gasto::getAll("WHERE tipo_de_gasto='".$tipo_de_gasto->nombre."' AND date_vencimiento < NOW() AND date_vencimiento < '".$ultimo_dia_mes."' AND estado='Por Pagar'");
  $gastos = Gasto::getAll("WHERE tipo_de_gasto='".$tipo_de_gasto->nombre."' AND date_vencimiento BETWEEN '".$primer_dia_mes."' AND '".$ultimo_dia_mes."' AND (date_vencimiento >= NOW() OR estado='Pagado') ORDER BY ".$order_by." ".$order);
} else {
  $gastos_atrasados = Gasto::getAll("WHERE tipo_de_gasto='".$tipo_de_gasto->nombre."' AND date_vencimiento < NOW() AND date_vencimiento < '".$ultimo_dia_mes."' AND estado='Por Pagar'");
  $gastos = Gasto::getAll("WHERE tipo_de_gasto='".$tipo_de_gasto->nombre."' AND (tipo_de_gasto='Gas' OR tipo_de_gasto='Caja Chica' OR 'Combustible') AND date_vencimiento BETWEEN '".$primer_dia_mes."' AND '".$ultimo_dia_mes."' AND (date_vencimiento >= NOW() OR estado='Pagado') ORDER BY ".$order_by." ".$order);
}

$gastos = array_merge($gastos,$gastos_atrasados);

?>
<style>
.tr-gastos {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-dollar-sign"></i> <b>Gasto:</b> <?= $tipo_de_gasto->nombre; ?></h1>
  </div>
  <div>
      <?php $usuario->printReturnBtn(); ?>
  </div>
</div>
<div class="d-sm-flex mb-3">
  <div>
    <select class="form-control date-select me-1" id="mes-select">
      <?php
      for($i = 1; $i<=12; $i++) {
        print "<option value='".$i."'>".int2mes($i)."</option>";
      }
      ?>
    </select>
  </div>
  <div>
    <select class="form-control date-select" id="ano-select">
      <?php
      for($i = 2023; $i<=date('Y'); $i++) {
        print "<option>".$i."</option>";
      }
      ?>
    </select>
  </div>
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
?>
<table class="table table-hover table-striped table-sm">
  <thead class="thead-dark">
    <tr>
      <th>
      <a href="#" class="sort" data-sortorderby="item">&Iacute;tem</a>
      </th>
      <th>
      <a href="#" class="sort" data-sortorderby="tipo_de_gasto">Tipo de Gasto</a>
      </th>
      <th>
      <a href="#" class="sort" data-sortorderby="fecha">Fecha</a>
      </th>
      <th>
      <a href="#" class="sort" data-sortorderby="id_usuarios">Ingresado por</a>
      </th>
      <th>
      <a href="#" class="sort" data-sortorderby="vencido">Vencido</a>
      </th>
      <th>
      <a href="#" class="sort" data-sortorderby="por_vencer">Por Vencer</a>
      </th>
      <th>
      <a href="#" class="sort" data-sortorderby="pagado">Pagado</a>
      </th>
    </tr>
  </thead>
  <tbody>
    <?php
      $total = 0;
      $total_vencido = 0;
      $total_por_vencer = 0;
      $total_pagado = 0;
      $ts_hoy = strtotime(date('Y-m-d')." 00:00:00");
      foreach($gastos as $gasto) {
        $total += $gasto->monto;
        $vencido = 0;
        $por_vencer = 0;
        $pagado = 0;
        if($gasto->estado == "Por Pagar") {
          $vencimi = $gasto->date_vencimiento." 00:00:00";
          $ts_vencimi = strtotime($vencimi);
          if($ts_vencimi < $ts_hoy) {
            $vencido = $gasto->monto;
            $total_vencido += $gasto->monto;
          } else {
            $por_vencer = $gasto->monto;
            $total_por_vencer += $gasto->monto;
          }
        }
        if($gasto->estado == "Pagado") {
          $pagado = $gasto->monto;
          $total_pagado += $gasto->monto;
        }
        $usuario_creador = new Usuario($gasto->id_usuarios);
    ?>
    <tr class="tr-gastos" data-idgastos="<?= $gasto->id; ?>">
      <td>
        <?= $gasto->item; ?>
      </td>
      <td>
        <?= $gasto->tipo_de_gasto; ?>
      </td>
      <td>
        <?= date2fecha($gasto->creada); ?>
      </td>
      <td>
        <?= $usuario_creador->nombre; ?>
      </td>
      <td>
        $<?= number_format($vencido); ?>
      </td>
      <td>
        $<?= number_format($por_vencer); ?>
      </td>
      <td>
        $<?= number_format($pagado); ?>
      </td>
    </tr>
    <?php
      }
    ?>
  </tbody>
  <tfooter>
    <tr style="background-color: white; border: 1px solid black">
      <td colspan="3">
      </td>
      <td>
        Total:
    </td>
    <td><b>$<?= number_format($total_vencido); ?></td>
    <td><b>$<?= number_format($total_por_vencer); ?></td>
    <td><b>$<?= number_format($total_pagado); ?></td>
    </tr>
  </tfooter>
</table>

<script>
var obj = <?= json_encode($tipo_de_gasto,JSON_PRETTY_PRINT); ?>;
var mes = '<?= intval($mes); ?>';
var ano = '<?= intval($ano); ?>';
var order_by = '<?= $order_by; ?>';
var order = '<?= $order; ?>';

$(document).ready(function(){
  $('#mes-select').val(mes);
  $('#ano-select').val(ano);
});

$(document).on('change','.date-select', function(e) {
  window.location.href = "./?s=detalle-gastos-por-tipo&id=" + obj.id + "&mes=" + $('#mes-select').val() + "&ano=" + $('#ano-select').val();
});


$(document).on('click','.tr-gastos',function(e) {
    window.location.href = "./?s=detalle-gastos&id=" + $(e.currentTarget).data('idgastos');
});

$(document).on('click','.sort',function(e){
  if(order == "asc") {
    order = "desc";
  } else {
    order = "asc";
  }
  window.location.href = "./?s=detalle-gastos-por-tipo&id=" + obj.id + "&mes=" + mes + "&ano=" + ano + "&order_by=" + $(e.currentTarget).data('sortorderby') + "&order=" + order;
});

</script>