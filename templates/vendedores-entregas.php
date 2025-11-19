<?php


  //checkAutorizacion(["Vendedor"]);

  $msg = 0;
  if(isset($_GET['msg'])) {
    $msg = $_GET['msg'];
  }

  $id_clientes = 0;
  if(validaIdExists($_GET,'id_clientes')) {
    $id_clientes = $_GET['id_clientes'];
  }

  $usuario = $GLOBALS['usuario'];
  $clientes = Cliente::getAll("WHERE id_usuarios_vendedor='".$usuario->id."'");

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
        $query = "WHERE id_usuarios_vendedor='".$usuario->id."' AND creada BETWEEN '".$esta_semana->lunes." 00:00:00' AND '".$esta_semana->domingo." 23:59:59'";
    } else {
        $query = "WHERE id_usuarios_vendedor='".$usuario->id."' AND creada BETWEEN '".$ano."-".$mes."-01 00:00:00' AND '".$ano."-".$mes."-31 23:59:59'";
    }

    if(validaIdExists($_GET,'id_clientes')) {
      $query .= " AND id_clientes='".$_GET['id_clientes']."'";
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
    <h1 class="h5 mb-0 text-gray-800">Entregas del Vendedor</h1>
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
  <select class="form-control" style="max-width: 300px" id="clientes-select">
      <option value="0">Cliente: TODOS</option>
      <?php
        foreach($clientes as $cliente) {
          print "<option value='".$cliente->id."'";
          if($cliente->id == $id_clientes) {
            print " SELECTED";
          }
          print ">".$cliente->nombre."</option>";
        }
      ?>
  </select>
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
        Monto
      </th>
      <th>
        Estado
      </th>
      <th>
        Barriles
      </th>
      <th>
        Meta Barriles
      </th>
      <th>
        Cajas
      </th>
      <th>
        Meta Cajas
      </th>
      <!--<th>

      </th>-->
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
    <tr class="tr-entregas" data-identregas="<?= $entrega->id; ?>">
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
        $<?= number_format($entrega->monto); ?>
      </td>
      <td>
        <?= $entrega->estado; ?>
      </td>
      <td>
        <?= $barriles; ?>
      </td>
      <td>
      </td>
      <td>
        <?= $cajas; ?>
      </td>
      <td>
      </td>
    </tr>
    <?php
      }
    ?>
    <tfooter>
      <tr style="border: 1px solid black; background-color: white">
        <td colspan="4">
          Totales
        </td>
        <td colspan="2">
          <b>$<?= number_format($totales['monto']); ?></b>
        </td>
        <td>
          <?= $totales['Barriles']; ?>
        </td>
        <td>
          <?= $usuario->vendedor_meta_barriles; ?>
        </td>
        <td>
          <?= $totales['Cajas']; ?>
        </td>
        <td>
          <?= $usuario->vendedor_meta_cajas; ?>
        </td>

      </tr>
    </tfooter>
  </tbody>
</table>

<script>

var lunes = '<?= $semana->lunes; ?>';
var mes = '<?= $mes; ?>';
var id_clientes = '<?= $id_clientes; ?>';

$(document).on('click','.tr-entregas',function(e){
  window.location.href = "./?s=detalle-entregas&id=" + $(e.currentTarget).data('identregas');
});
$(document).on('change','#mes-select', function(e) {
  window.location.href = "./?s=entregas&mes=" + $(e.currentTarget).val() + "&id_clientes=" + id_clientes + "&modo=" + $("#vista-select").val();
});

$(document).on('change','#lunes-select', function(e) {
  window.location.href = "./?s=entregas&lunes=" + $(e.currentTarget).val() + "&id_clientes=" + id_clientes + "&modo=" + $("#vista-select").val();
});

$(document).on('change','#clientes-select', function(e) {
  window.location.href = "./?s=entregas&lunes=" + lunes + "&id_clientes=" + $(e.currentTarget).val() + "&modo=" + $("#vista-select").val();
});

$(document).on('change','#vista-select', function(e) {
  window.location.href = "./?s=entregas&lunes=" + lunes + "&id_clientes=" + id_clientes + "&modo=" + $("#vista-select").val();
});

$(document).ready(function(){
  $('#mes-select').val(<?= $mes; ?>);
  $('#ano-select').val(<?= $ano; ?>);
  $('#vista-select').val('<?= $modo; ?>');
});

$(document).on('click','.pagar-btn',function(e){

  e.stopPropagation();

  /*if($('input[name="nombre"]').val().length < 2) {
    alert("El nombre debe tener mas de 2 caracteres.");
    return false;
  }*/

  $('#pagar-btn').attr('DISABLED',true);

  var url = "./ajax/ajax_generarPago.php";
  var data = {
    'id': $(e.currentTarget).data('id'),
    'tipopago': 'pago'
  };

  $.post(url,data,function(response){
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-entregas&id=" + response.obj.id + "&msg=2&lunes=<?= $esta_semana->lunes; ?>";
    }
  },'json').fail(function(){
    alert("No funciono");
  });
});
</script>
