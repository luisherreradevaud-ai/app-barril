<?php

  //checkAutorizacion(["Cliente"]);

  $usuario = $GLOBALS['usuario'];
  $cliente = new Cliente($usuario->id_clientes);

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
  if(validaIdExists($_GET,'ano')) {
    $ano = $_GET['ano'];
  }

  $mes = intval(date('m'));
  if(validaIdExists($_GET,'mes')) {
    $mes = $_GET['mes'];
  }

  if(validaIdExists($_GET,'trimestre')) {
    $trimestre = $_GET['trimestre'];
  } else {
    $trimestre = ceil($mes/3);
  }

  if(validaIdExists($_GET,'semestre')) {
    $semestre = $_GET['semestre'];
  } else {
    $semestre = ceil($mes/6);
  }

  $order_by = "id";
  if(isset($_GET['order_by'])) {
    if($_GET['order_by'] == "clientes") {
      $order_by = "clientes";
    }
    if($_GET['order_by'] == "id") {
      $order_by = "id";
    }
    if($_GET['order_by'] == "monto") {
      $order_by = "monto";
    }
    if($_GET['order_by'] == "factura") {
      $order_by = "factura";
    }
    if($_GET['order_by'] == "estado") {
      $order_by = "estado";
    }
    if($_GET['order_by'] == "creada") {
      $order_by = "creada";
    }
  }

  $order = "asc";
  if(isset($_GET['order'])) {
    if($_GET['order'] == "asc") {
      $order = "asc";
    } else 
    if($_GET['order'] == "desc") {
      $order = "desc";
    }
  }

  if($mes == intval(date('m')) && $ano == date('Y')) {
    $date = date($ano."-".$mes.'-d');
  } else {
    $date = date($ano."-".$mes.'-1');
  }

  if(isset($_GET['lunes'])) {
    $date = $_GET['lunes'];
  }

  $modo = "Semanal";
  if(isset($_GET['modo'])) {
    $modo = $_GET['modo'];
  }

  $datetime = new DateTime($date);

  if(intval($datetime->format('m')) != $mes || intval($datetime->format('Y')) != $ano ) {
    $date = $ano."-".$mes."-1";
    $datetime = new DateTime($date);
  }

  //print $datetime->format('Y-m-d');
  
  $esta_semana = new stdClass;

  if($datetime->format('N') == 1){
    $esta_semana->lunes = $datetime->format('Y-m-d');
  } else {
    $esta_semana->lunes = $datetime->modify('last monday')->format('Y-m-d');
  }

  //$esta_semana->lunes = $datetime->format('Y-m-d');
  $lunes = $esta_semana->lunes;

  //print $lunes;

  $esta_semana->domingo = date('Y-m-d',strtotime($esta_semana->lunes.' +6 days'));

  $semanas = getSemanas($date);

  if($modo == "Semanal") {
    $date_inicio = $esta_semana->lunes;
    $date_final = $esta_semana->domingo;
  } else 
  if($modo == "Mensual") {
    $date_inicio = $ano."-".$mes."-01";
    $date_final = $ano."-".$mes."-31";
  } else 
  if($modo == "Trimestral") {
    if($trimestre == 1) {
      $date_inicio = $ano."-01-01";
      $date_final = $ano."-03-31";
    } else
    if($trimestre == 2) {
      $date_inicio = $ano."-04-01";
      $date_final = $ano."-06-31";
    } else
    if($trimestre == 3) {
      $date_inicio = $ano."-07-01";
      $date_final = $ano."-09-31";
    } else
    if($trimestre == 4) {
      $date_inicio = $ano."-10-01";
      $date_final = $ano."-12-31";
    }
  } else 
  if($modo == "Semestral") {
    if($semestre == 1) {
      $date_inicio = $ano."-01-01";
      $date_final = $ano."-06-31";
    } else
    if($semestre == 2) {
      $date_inicio = $ano."-07-01";
      $date_final = $ano."-12-31";
    }
  } else 
  if($modo == "Anual") {
    $date_inicio = $ano."-01-01";
    $date_final = $ano."-12-31";
  } else 
  if($modo == "Historico") {
    $date_inicio = "22-01-01";
    $date_final = date('Y-m-d');
  }

  //print $date_inicio." ".$date_final;

  $query = "";

  if($order_by == "clientes") {
    $query = "INNER JOIN clientes ON entregas.id_clientes=clientes.id";
  }

  $query .= " WHERE entregas.id_clientes='".$cliente->id."' AND entregas.creada BETWEEN '".$date_inicio." 00:00:00' AND '".$date_final." 23:59:59'";

  $id_clientes = 0;
  if(validaIdExists($_GET,'id_clientes')) {
    $query .= " AND id_clientes='".$_GET['id_clientes']."'";
  }

  if($order_by != "clientes") {
    $query .= " ORDER BY ".$order_by." ".$order;
  } else {
    $query .= " ORDER BY clientes.nombre ".$order;
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
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-handshake"></i> <b>Entregas</b></h1>
  </div>
  <div>
    <div>

    </div>
  </div>
</div>
<div class="d-sm-flex mb-3">

  <div>
    <select class="form-control me-1 vista-select"  data-select="lunes">
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
    <select class="form-control me-1 vista-select"  data-select="mes">
      <?php
      for($i = 1; $i<=12; $i++) {
        print "<option value='".$i."'>".int2mes($i)."</option>";
      }
      ?>
    </select>
  </div>

  <div>
    <select class="form-control me-1 vista-select" data-select="trimestre">
      <option value="1">Enero - Marzo</option>
      <option value="2">Abril - Junio</option>
      <option value="3">Julio - Septiembre</option>
      <option value="4">Octubre - Diciembre</option>
    </select>
  </div>

  <div>
    <select class="form-control me-1 vista-select"  data-select="semestre">
      <option value="1">Enero - Junio</option>
      <option value="2">Julio - Diciembre</option>
    </select>
  </div>

  <div>
    <select class="form-control vista-select"  data-select="ano">
      <?php
      for($i = 2023; $i<=date('Y'); $i++) {
        print "<option>".$i."</option>";
      }
      ?>
    </select>
  </div>
</div>
<div class="mb-3">
  <select class="form-control vista-select" style="max-width: 300px" data-select="modo">
    <option>Semanal</option>
    <option>Mensual</option>
    <option>Trimestral</option>
    <option>Semestral</option>
    <option>Anual</option>
    <option value="Historico">Hist&oacute;rico</option>
  </select>
</div>
<hr />
<?php
if($msg == 6) {
?>
<div class="alert alert-info" role="alert" >Entrega guardada con &eacute;xito.</div>
<?php
}
?>
<table class="table table-hover table-striped table-sm" id="table-entregas">
  <thead class="thead-dark">
    <tr>
      <th>
        <a href="#table-entregas" class="sort" data-orderby="id">#</a>
      </th>
      <th>
        <a href="#table-entregas" class="sort" data-orderby="clientes">Recibida por</a>
      </th>
      <th>
        <a href="#table-entregas" class="sort" data-orderby="creada">Fecha</a>
      </th>
      <th>
        <a href="#table-entregas" class="sort" data-orderby="factura">Factura</a>
      </th>
      <th>
        <a href="#table-entregas" class="sort" data-orderby="monto">Monto</a>
      </th>
      <th>
        <a href="#table-entregas" class="sort" data-orderby="estado">Estado</a>
      </th>
      <?php
        foreach($tipos_barriles as $tipo_barril) {
          print "<th style='font-size: 0.9em'>".$tipo_barril."</th>";
        }
      ?>
      <th>
        Cajas
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
      $totales['Caja'] = 0;

      foreach($entregas as $entrega) {
        $cliente = new Cliente($entrega->id_clientes);

        $barriles_arr = array();
        foreach($tipos_barriles as $tipo_barril) {
          $barriles_arr[$tipo_barril] = 0;
        }
        $cajas = 0;

        $entrega_productos = EntregaProducto::getAll("WHERE id_entregas='".$entrega->id."'");

        foreach($entrega_productos as $entrega_producto) {
          if($entrega_producto->tipo == "Barril") {
            $barriles_arr[$entrega_producto->tipos_cerveza] += 1;
            $totales[$entrega_producto->tipos_cerveza] += 1;
          } else
          if($entrega_producto->tipo == "Caja") {
            $cajas += 1;
            $totales['Caja'] += 1;
          }
        }

        $totales['monto'] += $entrega->monto;

    ?>
    <tr>
      <td>
        <?= $entrega->id; ?>
      </td>
      <td>
        <?= $entrega->receptor_nombre; ?>
      </td>
      <td>
        <?= datetime2fecha($entrega->creada); ?>
      </td>
      <td>
        <a href="./clientes/dte.php?folio=<?= $entrega->factura; ?>"><?= $entrega->factura; ?></a>
      </td>

      <td>
        $<?= number_format($entrega->monto); ?>
      </td>
      <td>
        <?= $entrega->estado; ?>
      </td>
      <?php
        foreach($tipos_barriles as $tipo_barril) {
          print "<td style='font-size: 0.9em'>".$barriles_arr[$tipo_barril]."</td>";
        }
      ?>
      <td>
        <?= $cajas; ?>
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
        <?php
          foreach($tipos_barriles as $tipo_barril) {
            print "<td><b>".$totales[$tipo_barril]."</b></td>";
          }
        ?>
        <td>
          <b><?= $totales['Caja']; ?></b>
        </td>
      </tr>
    </tfooter>
  </tbody>
</table>

<script>

var lunes = '<?= $esta_semana->lunes; ?>';
var mes = '<?= $mes; ?>';
var ano = '<?= $ano; ?>';
var id_clientes = '<?= $id_clientes; ?>';
var order_by = '<?= $order_by; ?>';
var order = '<?= $order; ?>';
var modo = '<?= $modo; ?>';
var trimestre = '<?= $trimestre; ?>';
var semestre = '<?= $semestre; ?>';


$(document).ready(function(){

  $('.vista-select[data-select="mes"').val(mes);
  $('.vista-select[data-select="ano"').val(ano);
  $('.vista-select[data-select="modo"').val(modo);
  $('.vista-select[data-select="lunes"').val(lunes);
  $('.vista-select[data-select="trimestre"').val(trimestre);
  $('.vista-select[data-select="semestre"').val(semestre);

  $('.vista-select').hide();
  $('.vista-select[data-select="modo"').show();
  $('.vista-select[data-select="id_clientes"').show();

  if(modo == "Semanal") {
    $('.vista-select[data-select="lunes"').show();
    $('.vista-select[data-select="mes"').show();
    $('.vista-select[data-select="ano"').show();
  }
  if(modo == "Mensual") {
    $('.vista-select[data-select="mes"').show();
    $('.vista-select[data-select="ano"').show();
  }
  if(modo == "Trimestral") {
    $('.vista-select[data-select="trimestre"').show();
    $('.vista-select[data-select="ano"').show();
  }
  if(modo == "Semestral") {
    $('.vista-select[data-select="semestre"').show();
    $('.vista-select[data-select="ano"').show();
  }
  if(modo == "Anual") {
    $('.vista-select[data-select="ano"').show();
  }

});

$(document).on('change','.vista-select', function(e) {

  var select = $(e.currentTarget).data('select');
  var value = $(e.currentTarget).val();

  if(select == 'lunes') {
    lunes = value;
  } else
  if(select == 'mes') {
    mes = value;
  } else
  if(select == 'ano') {
    ano = value;
  } else
  if(select == 'trimestre') {
    trimestre = value;
  } else
  if(select == 'semestre') {
    semestre = value;
  } else
  if(select == 'id_clientes') {
    id_clientes = value;
  } else
  if(select == 'modo') {
    modo = value;
  }

  cambiarVista();

});

$(document).on('click','.sort', function(e) {

  if($(e.currentTarget).data('orderby') == order_by) {
    if(order == "asc") {
      order = "desc";
    } else {
      order = "asc";
    }
  } else {
    order = "asc";
  }

  order_by = $(e.currentTarget).data('orderby');
  cambiarVista();

});



function cambiarVista() {

  if(modo == 'Semanal') {
    window.location.href = "./?s=central-clientes-entregas&lunes=" + lunes + "&id_clientes=" + id_clientes + "&mes=" + mes + "&ano=" + ano + "&modo=" + modo + "&order_by=" + order_by + "&order=" + order;
  }
  if(modo == 'Mensual') {
    window.location.href = "./?s=central-clientes-entregas&mes=" + mes + "&ano=" + ano + "&id_clientes=" + id_clientes + "&modo=" + modo + "&order_by=" + order_by + "&order=" + order;
  }
  if(modo == 'Trimestral') {
    window.location.href = "./?s=central-clientes-entregas&trimestre=" + trimestre + "&ano=" + ano + "&id_clientes=" + id_clientes + "&modo=" + modo + "&order_by=" + order_by + "&order=" + order;
  }
  if(modo == 'Semestral') {
    window.location.href = "./?s=central-clientes-entregas&semestre=" + semestre + "&ano=" + ano + "&id_clientes=" + id_clientes + "&modo=" + modo + "&order_by=" + order_by + "&order=" + order;
  }
  if(modo == 'Historico') {
    window.location.href = "./?s=central-clientes-entregas&id_clientes=" + id_clientes + "&modo=" + modo + "&order_by=" + order_by + "&order=" + order;
  }
  if(modo == 'Anual') {
    window.location.href = "./?s=central-clientes-entregas&ano=" + ano + "&id_clientes=" + id_clientes + "&modo=" + modo + "&order_by=" + order_by + "&order=" + order;
  }

}


</script>
