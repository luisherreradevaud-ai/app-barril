<?php

  $msg = 0;

  if(isset($_GET['msg'])) {
    $msg = $_GET['msg'];
  }

  function getSemanas($date) {

    $semanas = array();

      $time = strtotime($date);
      $year = date('Y', $time);
      $month = date('m', $time);

      for($day = 1; $day <= 31; $day++)
      {
          $time = mktime(0, 0, 0, $month, $day, $year);
          if (date('N', $time) == 1)
          {
            $semana = new stdClass;
            $semana->lunes = date('Y-m-d', $time);
            $semana->domingo = date('Y-m-d',strtotime($semana->lunes.' +6 days'));
            $semanas[] = $semana;

          }
      }

      return $semanas;
  }

  $ano = date('Y');
  $mes = date('m');

  if(validaIdExists($_GET,'ano')) {
    $ano = $_GET['ano'];
  }

  if(validaIdExists($_GET,'mes')) {
    $mes = $_GET['mes'];
  }

  $date = date($ano."-".$mes.'-d');

  if(isset($_GET['lunes'])) {
    $date = $_GET['lunes'];
  }

  $datetime = new DateTime($date);
  $ano = $datetime->format('Y');
  $mes = $datetime->format('m');

  $esta_semana = new stdClass;

  if($datetime->format('N') == 1){
    $esta_semana->lunes = $datetime->format('Y-m-d');
  } else {
    $esta_semana->lunes = $datetime->modify('last monday')->format('Y-m-d');
  }

  $esta_semana->domingo = date('Y-m-d',strtotime($esta_semana->lunes.' +6 days'));


  $semanas = getSemanas($date);
  $clientes = Cliente::getAll();
  $tipos_barril_cerveza = $GLOBALS['tipos_barril_cerveza'];

  $datos = array();

  $totales['Caja'] = 0;
  $totales['ventas'] = 0;
  $totales['unidades_semanales'] = 0;
  $totales['deuda'] = 0;
  $totales['pagos'] = 0;

  foreach($tipos_barril_cerveza as $tbc) {
    $totales['Barril'][$tbc] = 0;
  }

  foreach($clientes as $cliente) {

    $datos[$cliente->id]['nombre'] = $cliente->nombre;

    $unidades_vendidas['unidades_semanales'] = 0;
    $unidades_vendidas['Caja'] = 0;
    $unidades_vendidas['CO2'] = 0;
    $unidades_vendidas['Vasos'] = 0;

    foreach($tipos_barril_cerveza as $tbc) {
      $unidades_vendidas['Barril'][$tbc] = 0;
    }

    $datos[$cliente->id]['venta_semanal'] = 0;

    $query = "WHERE id_clientes='".$cliente->id."' AND creada BETWEEN '".$esta_semana->lunes." 00:00:00' AND '".$esta_semana->domingo." 23:59:59'";
    $entregas = Entrega::getAll($query);


    foreach($entregas as $entrega) {

      $entregas_productos = EntregaProducto::getAll("WHERE id_entregas='".$entrega->id."'");

      foreach($entregas_productos as $ep) {

        if($ep->tipo == "") {
          continue;
        }

        if($ep->tipo == "Barril") {
          $unidades_vendidas[$ep->tipo][$ep->tipos_cerveza] += 1;
          $totales[$ep->tipo][$ep->tipos_cerveza] += 1;
        } else
        if( $ep->tipo == "Caja") {
          $unidades_vendidas[$ep->tipo] += 1;
          $totales[$ep->tipo] += 1;
        }

        if($ep->tipo != "CO2" && $ep->tipo != "Vasos") {
          $unidades_vendidas['unidades_semanales'] += 1;
          $totales['unidades_semanales'] += 1;
        }

      }

      $datos[$cliente->id]['venta_semanal'] += $entrega->monto;
      $totales['ventas'] += $entrega->monto;


    }

    $datos[$cliente->id]['deuda'] = 0;
    $entregas_sinpagar = Entrega::getAll("WHERE id_clientes='".$cliente->id."' AND estado!='Pagada' AND creada < '".$esta_semana->domingo." 23:59:59'");
    foreach($entregas_sinpagar as $esp) {
      $datos[$cliente->id]['deuda'] += $esp->monto;
      $totales['deuda'] += $esp->monto;
    }

    $datos[$cliente->id]['pagos'] = 0;
    $pagos = Pago::getAll("WHERE id_clientes='".$cliente->id."' AND creada BETWEEN '".$esta_semana->lunes." 00:00:00' AND '".$esta_semana->domingo." 23:59:59'");
    foreach($pagos as $pago) {
      $datos[$cliente->id]['pagos'] += $pago->amount;
      $totales['pagos'] += $pago->amount;
    }

    $datos[$cliente->id]['unidades_vendidas'] = $unidades_vendidas;

  }


?>
<style>
.tr-clientes {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-handshake"></i> <b>Dashboard</b></h1>
  </div>
  <div class="d-flex">


  </div>
</div>
<div class="d-sm-flex mb-3">
  <div>
    <select class="form-control" id="lunes-select">
      <?php
      foreach($semanas as $semana) {
        print "<option value='".$semana->lunes."'";
        if($esta_semana->lunes == $semana->lunes) {
          print " SELECTED";
        }
        print ">".date2fecha($semana->lunes)." al ".date2fecha($semana->domingo)."</option>";
      }
      ?>
    </select>
  </div>
  <div>
    <select class="form-control" id="mes-select">
      <?php
      for($i = 1; $i<=12; $i++) {
        print "<option value='".$i."'>".int2mes($i)."</option>";
      }
      ?>
    </select>
  </div>
  <div>
    <select class="form-control">
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
<div class="alert alert-danger" role="alert" >Cliente eliminado.</div>
<?php
}
?>
<div style="overflow-x: scroll; width:100%">
<table class="table table-hover table-striped table-sm table-bordered">
  <thead class="thead-dark">
    <tr style="font-size: 0.7em">
      <th style='width: 200px'>
        Nombre
      </th>
      <?php
      foreach($tipos_barril_cerveza as $tbc) {
        print "<th style='width: 60px'>
        ".$tbc."
        </th>";
      }
      ?>
      <th style='width: 60px'>
        Cajas
      </th>
      <th>
        Venta Semanal
      </th>
      <th style="width: 80px">
        Unid. Semanales
      </th>
      <th>
        Deuda
      </th>
      <th>
        Pagos
      </th>
    </tr>
  </thead>
  <tbody>
    <?php
      foreach($datos as $key=>$dato) {
    ?>
    <tr data-idclientes="<?= $key; ?>">
      <td>
        <?= $dato['nombre']; ?>
      </td>
      <?php
      foreach($tipos_barril_cerveza as $tbc) {
        print "<td>
        ".$dato['unidades_vendidas']['Barril'][$tbc]."
        </td>";
      }
      ?>
      <td>
        <?= $dato['unidades_vendidas']['Caja']; ?>
      </td>
      <td>
        $<?= number_format($dato['venta_semanal']); ?>
      </td>
      <td>
        <?= $dato['unidades_vendidas']['unidades_semanales']; ?>
      </td>
      <td>
        $<?= number_format($dato['deuda']); ?>
      </td>
      <td>
        $<?= number_format($dato['pagos']); ?>
      </td>
    </tr>
    <?php
      }
    ?>
    <tr style="border: 2px black solid">
      <td>
        <b>Totales:</b>
      </td>
      <?php
      foreach($tipos_barril_cerveza as $tbc) {
        print "<td>
        ".$totales['Barril'][$tbc]."
        </td>";
      }
      ?>
      <td>
        <?= $totales['Caja']; ?>
      </td>
      <td>
        $<?= number_format($totales['ventas']); ?>
      </td>
      <td>
        <?= $totales['unidades_semanales']; ?>
      </td>
      <td>
        $<?= number_format($totales['deuda']); ?>
      </td>
      <td>
        $<?= number_format($totales['pagos']); ?>
      </td>
    </tr>
  </tbody>
</table>
</div>
<script>
$(document).on('click','.tr-clientes',function(e){
  window.location.href = "./?s=detalle-clientes&id=" + $(e.currentTarget).data('idclientes');
});

$(document).on('change','#mes-select', function(e) {
  window.location.href = "./?s=dashboard-administrador&mes=" + $(e.currentTarget).val();
});

$(document).on('change','#lunes-select', function(e) {
  window.location.href = "./?s=dashboard-administrador&lunes=" + $(e.currentTarget).val();
});

$(document).ready(function(){
  $('#mes-select').val(<?= $mes; ?>);
});
</script>
