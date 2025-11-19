<?php

  //checkAutorizacion("Administrador");

  $usuario = $GLOBALS['usuario'];
  if($usuario->nivel != "Administrador" && $usuario->nivel != "Jefe de Planta" && $usuario->nivel != "Visita") {
    die();
  }

  $msg = 0;

  if(isset($_GET['msg'])) {
    $msg = $_GET['msg'];
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

  $datetime = new DateTime($date);
  $ano = $datetime->format('Y');
  $mes = $datetime->format('m');


  $clientes = Cliente::getAll();

  $datos = array();

  $totales['ventas'] = 0;
  $totales['pagos'] = 0;
  $totales['barriles_vendidos'] = 0;
  $totales['cajas_vendidas'] = 0;
  $totales['meta_barriles_mensuales'] = 0;
  $totales['meta_cajas_mensuales'] = 0;


  foreach($clientes as $cliente) {

    $datos[$cliente->id]['nombre'] = $cliente->nombre;
    $datos[$cliente->id]['barriles_vendidos'] = 0;
    $datos[$cliente->id]['cajas_vendidas'] = 0;
    $datos[$cliente->id]['venta_mensual'] = 0;
    $datos[$cliente->id]['pago_mensual'] = 0;
    $datos[$cliente->id]['meta_barriles_mensuales'] = $cliente->meta_barriles_mensuales;
    $datos[$cliente->id]['meta_cajas_mensuales'] = $cliente->meta_cajas_mensuales;

    $query = "WHERE id_clientes='".$cliente->id."' AND creada BETWEEN '".$ano."-".$mes."-01 00:00:00' AND '".$ano."-".$mes."-31 23:59:59'";
    $entregas = Entrega::getAll($query);
    $pagos = Pago::getAll($query);

    foreach($entregas as $entrega) {

      $entregas_productos = EntregaProducto::getAll("WHERE id_entregas='".$entrega->id."'");

      foreach($entregas_productos as $ep) {
        if($ep->tipo == "Barril") {
          $datos[$cliente->id]['barriles_vendidos'] += 1;
          $totales['barriles_vendidos'] += 1;
        } else
        if($ep->tipo == "Caja") {
          $datos[$cliente->id]['cajas_vendidas'] += 1;
          $totales['cajas_vendidas'] += 1;
        }

      }

      $datos[$cliente->id]['venta_mensual'] += $entrega->monto;
      $totales['ventas'] += $entrega->monto;

    }

    foreach($pagos as $pago) {
      $datos[$cliente->id]['pago_mensual'] += $pago->amount;
      $totales['pagos'] += $pago->amount;
    }

    $totales['meta_barriles_mensuales'] += $datos[$cliente->id]['meta_barriles_mensuales'];
    $totales['meta_cajas_mensuales'] += $datos[$cliente->id]['meta_cajas_mensuales'];

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
<div style="overflow-x: scroll; width:100%">
<table class="table table-hover table-striped table-sm table-bordered">
  <thead class="thead-dark">
    <tr style="font-size: 0.8em">
      <th>
        Nombre
      </th>
      <?php
      if($usuario->nivel == "Administrador") {
        print " <th>
            Venta mensual
          </th>";
          print " <th>
              Pago mensual
            </th>";
      }
      ?>
      <th>
        Barriles
      </th>
      <th>
        Meta
      </th>
      <th>
        Cajas
      </th>
      <th>
        Meta
      </th>
    </tr>
  </thead>
  <tbody>
    <?php
      foreach($datos as $key=>$dato) {
    ?>
    <tr class="tr-clientes" data-idclientes="<?= $key; ?>">
      <td>
        <?= $dato['nombre']; ?>
      </td>
      <?php
      if($usuario->nivel == "Administrador") {
        print "<td>
          $".number_format($dato['venta_mensual'])."
        </td>";
        print "<td>
          $".number_format($dato['pago_mensual'])."
        </td>";
      }
      ?>
      <td>
        <?= $dato['barriles_vendidos']; ?>
      </td>
      <td>
        <?= $dato['meta_barriles_mensuales']; ?>
      </td>
      <td>
        <?= $dato['cajas_vendidas']; ?>
      </td>
      <td>
        <?= $dato['meta_cajas_mensuales']; ?>
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
      if($usuario->nivel == "Administrador") {
        print "<td>
          $".number_format($totales['ventas'])."
        </td>";
        print "<td>
          $".number_format($totales['pagos'])."
        </td>";
      }
      ?>
      <td>
        <?= $totales['barriles_vendidos']; ?>
      </td>
      <td>
        <?= $totales['meta_barriles_mensuales']; ?>
      </td>
      <td>
        <?= $totales['cajas_vendidas']; ?>
      </td>
      <td>
        <?= $totales['meta_cajas_mensuales']; ?>
      </td>
    </tr>
  </tbody>
</table>
</div>
<script>
$(document).on('click','.tr-clientes',function(e){
  window.location.href = "./?s=resumen-cliente&id=" + $(e.currentTarget).data('idclientes');
});

$(document).on('change','#mes-select', function(e) {
  window.location.href = "./?s=resumen-mensual&mes=" + $(e.currentTarget).val();
});


$(document).ready(function(){
  $('#mes-select').val(<?= $mes; ?>);
  $('#ano-select').val(<?= $ano; ?>);
});
</script>
