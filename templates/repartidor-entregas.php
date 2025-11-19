<?php


  //checkAutorizacion(["Repartidor"]);

  $msg = 0;
  if(isset($_GET['msg'])) {
    $msg = $_GET['msg'];
  }

  $usuario = $GLOBALS['usuario'];
  $clientes = Cliente::getAll();

  $tipos_barriles = $GLOBALS['tipos_barril_cerveza'];

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
  $modo = "Semanal";

  if(validaIdExists($_GET,'ano')) {
    $ano = $_GET['ano'];
  }

  if(validaIdExists($_GET,'mes')) {
    $mes = $_GET['mes'];
  }

  if($mes == date('m')) {
    $date = date($ano."-".$mes.'-d');
  } else {
    $date = date($ano."-".$mes.'-1');
  }

  if(isset($_GET['lunes'])) {
    $date = $_GET['lunes'];
  }

  if(isset($_GET['modo'])) {
    $modo = $_GET['modo'];
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


  $entregas = array();


    

    if($modo == "Semanal") {
        $query = "WHERE id_usuarios_repartidor='".$usuario->id."' AND creada BETWEEN '".$esta_semana->lunes." 00:00:00' AND '".$esta_semana->domingo." 23:59:59'";
    } else {
        $query = "WHERE id_usuarios_repartidor='".$usuario->id."' AND creada BETWEEN '".$ano."-".$mes."-01 00:00:00' AND '".$ano."-".$mes."-31 23:59:59'";
    }
    
    $entregas = Entrega::getAll($query);


?>
<style>
.tr-entregas {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
  <div class="mb-2">
    <h1 class="h4 mb-0 text-gray-800"><b><?= $usuario->nombre; ?></b></h1>
    <h1 class="h5 mb-0 text-gray-800">Entregas del Repartidor</h1>
  </div>
  </div>
  <div>
    <div>

    </div>
  </div>
</div>
<div class="d-sm-flex mb-3">

  <div>
    <select class="form-control me-1" id="lunes-select">
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
    <select class="form-control me-1" id="mes-select">
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
<div class="mb-3">
  <select class="form-control" style="max-width: 300px" id="vista-select">
    <option>Semanal</option>
    <option>Mensual</option>
  </select>
</div>
<hr />
<table class="table table-hover table-striped table-sm">
  <thead class="thead-dark">
    <tr>
      <th>
        #
      </th>
      <th>
        Cliente
      </th>
      <th>
        Fecha
      </th>
      <th>
        Factura
      </th>
      <th>
        Receptor
      </th>
    </tr>
  </thead>
  <tbody>
    <?php

      $totales = array();
      $totales['monto'] = 0;
      foreach($tipos_barriles as $tipo_barril) {
        $totales[$tipo_barril] = 0;
      }
      $totales['Barriles'] = 0;
      $totales['Cajas'] = 0;
      

      foreach($entregas as $entrega) {
        $cliente = new Cliente($entrega->id_clientes);
        $barriles = 0;
        $cajas = 0;
        $entrega_productos = EntregaProducto::getAll("WHERE id_entregas='".$entrega->id."'");

        foreach($entrega_productos as $entrega_producto) {
          if($entrega_producto->tipo == "Barril") {
            $totales['Barriles'] += 1;
            $barriles += 1;
          } else
          if($entrega_producto->tipo == "Caja") {
            $totales['Cajas'] += 1;
            $cajas += 1;
          } 
        }

        $totales['monto'] += $entrega->monto;

    ?>
    <tr class="tr-entregas" data-entregasfolio="<?= $entrega->factura; ?>">
      <td>
        <?= $entrega->id; ?>
      </td>
      <td>
        <?= $cliente->nombre; ?>
      </td>
      <td>
        <?= datetime2fecha($entrega->creada); ?>
      </td>
      <td>
        <?= $entrega->factura; ?>
      </td>
      <td>
        <?= $entrega->receptor_nombre; ?>
      </td>
    </tr>
    <?php
      }
    ?>
  </tbody>
</table>

<script>

var lunes = '<?= $semana->lunes; ?>';
var mes = '<?= $mes; ?>';

$(document).on('click','.tr-entregas',function(e){
  if($(e.currentTarget).data('entregasfolio') == '') {
    return false;
  }
  window.location.href = "./php/dte.php?folio=" + $(e.currentTarget).data('entregasfolio');
});
$(document).on('change','#mes-select', function(e) {
  window.location.href = "./?s=repartidor-entregas&mes=" + $(e.currentTarget).val() + "&modo=" + $("#vista-select").val();
});

$(document).on('change','#lunes-select', function(e) {
  window.location.href = "./?s=repartidor-entregas&lunes=" + $(e.currentTarget).val() + "&modo=" + $("#vista-select").val();
});

$(document).on('change','#clientes-select', function(e) {
  window.location.href = "./?s=repartidor-entregas&lunes=" + lunes + "&modo=" + $("#vista-select").val();
});

$(document).on('change','#vista-select', function(e) {
  window.location.href = "./?s=repartidor-entregas&lunes=" + lunes + "&modo=" + $("#vista-select").val();
});

$(document).ready(function(){
  $('#mes-select').val(<?= $mes; ?>);
  $('#ano-select').val(<?= $ano; ?>);
  $('#vista-select').val('<?= $modo; ?>');
});


</script>
