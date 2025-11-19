<?php


 require_once("./php/libredte.php");

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
  $mes = intval(date('m'));
  $modo = "Mensual";
  $mostrar = "activos";

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

  if(isset($_GET['modo'])) {
    $modo = $_GET['modo'];
  }

  if(isset($_GET['mostrar'])) {
    if($_GET['mostrar'] == "todos") {
      $mostrar = "todos";
    }
    if($_GET['mostrar'] == "bloqueados") {
      $mostrar = "bloqueados";
    }
  }

  $order_by = "nombre";
  $order = "asc";

  if(isset($_GET['order_by'])) {
  }

  if(isset($_GET['order'])) {
    if($_GET['order'] == "desc") {
      $order = "desc";
    }
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

  $mes = intval($mes);
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

  $query_clientes = "";

  if($mostrar == "activos") {
    $query_clientes .= "WHERE estado='Activo' ";
  } else
  if($mostrar == "bloqueados") {
    $query_clientes .= "WHERE estado='Bloqueado' ";
  } 

  $query_clientes .= "ORDER BY ".$order_by." ".$order;

  $clientes = Cliente::getAll($query_clientes);
  $tipos_barril_cerveza = $GLOBALS['tipos_barril_cerveza'];

  $datos = array();
  $productos_vendidos = array();

  $totales['Caja'] = 0;
  $totales['Barriles'] = 0;
  $totales['meta_barriles'] = 0;
  $totales['meta_cajas'] = 0;
  $totales['ventas'] = 0;
  $totales['unidades_semanales'] = 0;
  $totales['deuda'] = 0;
  $totales['pagos'] = 0;
  $totales['reemplazos'] = 0;
  $total_bruto = 0;
  $total_iva = 0;
  $total_ila = 0;
  $total_precio = 0;
  $total_productos_montos = 0;
  $entregas_con_factura = array();

  foreach($tipos_barril_cerveza as $tbc) {
    $totales['Barril'][$tbc] = 0;
  }

  foreach($clientes as $cliente) {

    $datos[$cliente->id]['nombre'] = $cliente->nombre;
    $datos[$cliente->id]['meta_barriles'] = $cliente->meta_barriles_mensuales;
    $datos[$cliente->id]['meta_cajas'] = $cliente->meta_cajas_mensuales;
    $totales['meta_barriles'] += $cliente->meta_barriles_mensuales;
    $totales['meta_cajas'] += $cliente->meta_cajas_mensuales;
    

    $unidades_vendidas['unidades_semanales'] = 0;
    $unidades_vendidas['Caja'] = 0;
    $unidades_vendidas['Barriles'] = 0;
    $unidades_vendidas['CO2'] = 0;
    $unidades_vendidas['Vasos'] = 0;

    foreach($tipos_barril_cerveza as $tbc) {
      $unidades_vendidas['Barril'][$tbc] = 0;
    }

    $datos[$cliente->id]['venta_semanal'] = 0;

    $query = "WHERE id_clientes='".$cliente->id."' AND creada BETWEEN '".$date_inicio." 00:00:00' AND '".$date_final." 23:59:59'";
    $entregas = Entrega::getAll($query);
    

    $datos[$cliente->id]['reemplazos'] = count(BarrilReemplazo::getAll($query));
    $totales['reemplazos'] += $datos[$cliente->id]['reemplazos'];

    foreach($entregas as $entrega) {

      if($entrega->factura != '') {

        $dte_str = LIBREDTE_getDataDTE($entrega->factura);
        $dte_obj = json_decode($dte_str);

        if(!isset($dte_obj->neto)) {
          continue;
        }

        $entregas_con_factura[] = array(
          'cliente' => $cliente->nombre,
          'factura' => $entrega->factura,
          'fecha' => $entrega->creada,
          'neto' => $dte_obj->neto,
          'iva' => $dte_obj->iva,
          'ila' => intval($dte_obj->total - $dte_obj->neto - $dte_obj->iva)
        );

        $total_bruto += $dte_obj->neto;
        $total_iva += $dte_obj->iva;
        $total_ila += intval($dte_obj->total - $dte_obj->neto - $dte_obj->iva);

      }

      $entregas_productos = EntregaProducto::getAll("WHERE id_entregas='".$entrega->id."'");

      foreach($entregas_productos as $ep) {

        if($ep->tipo == "") {
          continue;
        }

        if($ep->tipo == "Barril") {

          if(isset($unidades_vendidas[$ep->tipo][$ep->tipos_cerveza])) {
            $unidades_vendidas[$ep->tipo][$ep->tipos_cerveza] += 1;
          } else {
            $unidades_vendidas[$ep->tipo][$ep->tipos_cerveza] = 1;
          }
          $unidades_vendidas['Barriles'] += 1;

          if(isset($totales[$ep->tipo][$ep->tipos_cerveza])) {
            $totales[$ep->tipo][$ep->tipos_cerveza] += 1;
          } else {
            $totales[$ep->tipo][$ep->tipos_cerveza] = 1;
          }
          $totales['Barriles'] += 1;

        } else if($ep->tipo == "Caja") {

          if(isset($unidades_vendidas[$ep->tipo])) {
            $unidades_vendidas[$ep->tipo] += 1;
          } else {
            $unidades_vendidas[$ep->tipo] = 1;
          }

          if(isset($totales[$ep->tipo])) {
            $totales[$ep->tipo] += 1;
          } else {
            $totales[$ep->tipo] = 1;
          }
          
        }

        if($ep->tipo != "CO2" && $ep->tipo != "Vasos") {
          $unidades_vendidas['unidades_semanales'] += 1;
          $totales['unidades_semanales'] += 1;
          if($ep->id_productos!=0) {

            $producto = new Producto($ep->id_productos);
            
            $precio = ClienteProductoPrecio::getAll("WHERE id_productos='".$producto->id."' AND id_clientes='".$cliente->id."'");
            if(!isset($precio[0]->precio) || $precio[0]->precio == $producto->monto) {
              $total_productos_montos += $producto->monto;
              
              foreach($producto->productos_items as $pi) {
                //$total_bruto += $pi->monto_bruto;
                if($entrega->factura != "") {
                 // $total_iva += $pi->monto_bruto * 0.19;
                  if($pi->impuesto == "IVA + ILA") {
                    //$total_ila += $pi->monto_bruto * 0.205;
                  }
                  
                }
              }

            } else {
              $total_bruto += $precio[0]->precio;
              $total_precio += $precio[0]->precio;
              $total_productos_montos += $precio[0]->precio;
            }
              
            
            $productos_vendidos[] = $producto;
          }
        }

      }

      $datos[$cliente->id]['venta_semanal'] += $entrega->monto;
      $totales['ventas'] += $entrega->monto;

    }

    $datos[$cliente->id]['deuda'] = 0;
    $entregas_sinpagar = Entrega::getAll("WHERE id_clientes='".$cliente->id."' AND estado!='Pagada' AND creada < '".$esta_semana->domingo." 23:59:59'");
    foreach($entregas_sinpagar as $esp) {
      $datos[$cliente->id]['deuda'] += $esp->monto - $esp->abonado;
      $totales['deuda'] += $esp->monto - $esp->abonado;
    }

    $datos[$cliente->id]['pagos'] = 0;
    $pagos = Pago::getAll($query);
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
.table-detalle {
    width: 1px;
}
.table-metas {
    width: 1px;
}

</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-globe"></i> <b>Ventas</b></h1>
  </div>
  <div class="d-flex">


  </div>
</div>
<div class="mb-1">
    <select class="form-control vista-select" data-select="mostrar" style="max-width: 200px">
      <option value="todos">Todos</option>
      <option value="activos">Activos</option>
      <option value="bloqueados">Bloqueados</option>
    </select>
</div>


<div class="d-sm-flex mb-1">

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



<div class="d-sm-flex mb-3">
    <button class="btn btn-sm btn-primary" id="ver-detalle">Ver Detalle</button>
    &nbsp;&nbsp;
    <button class="btn btn-sm btn-primary" id="ver-metas">Ver Metas</button>
</div>
<hr />
<div style="overflow-x: scroll; width:100%">
<table class="table table-hover table-striped table-sm table-bordered">
  <thead class="thead-dark">
    <tr style="font-size: 0.7em">
      <th style='width: 200px'>
        <a href="#" class="sort" data-orderby="nombre">Nombre</a>
      </th>
      <th style='width: 60px'>
        Barriles
      </th>
      <th style='width: 60px'>
        Cajas
      </th>
      <th style='width: 60px'>
        Reemplazos
      </th>
      <th>
        Venta
      </th>
      <th>
        Pagos
      </th>
      <th style="border-right: 1px solid #5a5c68 !important;">
        Deuda hist&oacute;rica
      </th>
      <th class="table-detalle" style="background-color: #5a5c68;border: 1px solid #5a5c68 !important;"></th>
      <?php
      foreach($tipos_barril_cerveza as $tbc) {
        print "<th style='width: 60px' class='table-detalle'>
        ".$tbc."
        </th>";
      }
      ?>
      <th class="table-detalle" style="border-right: 1px solid #5a5c68 !important;">
        Cajas
      </th>
      <th class="table-metas" style="background-color: #5a5c68;border: 1px solid #5a5c68 !important;">
      </th>
      <th class="table-metas">
        Barriles
      </th>
      <th class="table-metas">
        Meta
      </th>
      <th class="table-metas">
        Cajas
      </th>
      <th class="table-metas">
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
      <td>
        <?= $dato['unidades_vendidas']['Barriles']; ?>
      </td>
      <td>
        <?= $dato['unidades_vendidas']['Caja']; ?>
      </td>
      <td>
        <?= $dato['reemplazos']; ?>
      </td>
      <td>
        $<?= number_format($dato['venta_semanal']); ?>
      </td>
      <td>
        $<?= number_format($dato['pagos']); ?>
      </td>
      <td>
        $<?= number_format($dato['deuda']); ?>
      </td>
      <td class="table-detalle" style="background-color: #5a5c68;border: 1px solid #5a5c68 !important;">
      </td>
      <?php
      foreach($tipos_barril_cerveza as $tbc) {
        print "<td class='table-detalle'>
        ".$dato['unidades_vendidas']['Barril'][$tbc]."
        </td>";
      }
      ?>
      <td class="table-detalle">
        <?= $dato['unidades_vendidas']['Caja']; ?>
      </td>
      <td class="table-metas" style="background-color: #5a5c68;border: 1px solid #5a5c68 !important;">
      </td>
      <td class="table-metas">
        <?= $dato['unidades_vendidas']['Barriles']; ?>
      </td>
      <td class="table-metas">
        <?= $dato['meta_barriles']; ?>
      </td>
      <td class="table-metas">
        <?= $dato['unidades_vendidas']['Caja']; ?>
      </td>
      <td class="table-metas">
        <?= $dato['meta_cajas']; ?>
      </td>
    </tr>
    <?php
      }
    ?>
    <tr style="border: 2px #5a5c68 solid">
      <td>
        <b>Totales:</b>
      </td>
      <td>
        <b><?= $totales['Barriles']; ?></b>
      </td>
      <td>
        <b><?= $totales['Caja']; ?></b>
      </td>
      <td>
        <b><?= $totales['reemplazos']; ?></b>
      </td>
      <td>
        <b>$<?= number_format($totales['ventas']); ?></b>
      </td>
      <td>
        <b>$<?= number_format($totales['pagos']); ?></b>
      </td>
      <td>
        <b>$<?= number_format($totales['deuda']); ?></b>
      </td>
      <td class="table-detalle" style="background-color: #5a5c68;border: 1px solid #5a5c68 !important;">
      </td>
      <?php
      foreach($tipos_barril_cerveza as $tbc) {
        print "<td class='table-detalle'><b>
        ".$totales['Barril'][$tbc]."
        </b></td>";
      }
      ?>
      <td class="table-detalle">
        <b><?= $totales['Caja']; ?></b>
      </td>
      <td class="table-metas" style="background-color: #5a5c68;border: 1px solid #5a5c68 !important;">
      </td>
      <td class="table-metas">
        <b><?= $totales['Barriles']; ?></b>
      </td>
      <td class="table-metas">
        <b><?= $totales['meta_barriles']; ?></b>
      </td>
      <td class="table-metas">
        <b><?= $totales['Caja']; ?></b>
      </td>
      <td class="table-metas">
        <b><?= $totales['meta_cajas']; ?></b>
      </td>
    </tr>
    
    <tr>
      <td colspan="1">
        <b>Total Bruto:</b>
      </td>
      <td colspan="2">
        <b>$<?= number_format(intval($total_productos_montos - intval($total_iva) - intval($total_ila))); ?></b>
      </td>

    </tr>
    <tr>
      <td colspan="1">
        <b>Total IVA:</b>
      </td>
      <td colspan="2">
        <b>$<?= number_format(intval($total_iva)); ?></b>
      </td>

    </tr>
    <tr>
      <td colspan="1">
        <b>Total ILA:</b>
      </td>
      <td colspan="2">
        <b>$<?= number_format(intval($total_ila)); ?></b>
      </td>

    </tr>
    
  </tbody>
</table>
</div>
<script>

var ver_detalle = false;
var ver_metas = false;
var modo = "<?= $modo; ?>";
var order_by = '<?= $order_by; ?>';
var order = '<?= $order; ?>';
var mostrar = '<?= $mostrar; ?>';
var mes = '<?= $mes; ?>';
var ano = '<?= $ano; ?>';
var trimestre = '<?= $trimestre; ?>';
var semestre = '<?= $semestre; ?>';
var lunes = '<?= $esta_semana->lunes; ?>';
var productos_vendidos = <?= json_encode($productos_vendidos,JSON_PRETTY_PRINT); ?>;

$(document).ready(function(){
  console.log(<?= json_encode($entregas_con_factura); ?>);
  console.log(<?= $total_productos_montos; ?>);
  console.log(<?= $total_precio; ?>);
  console.log(<?= $total_iva; ?>);
  console.log(<?= $total_bruto; ?>);
  console.log(<?= $total_ila; ?>);
  console.log(<?= 	round($total_iva + $total_ila + $total_bruto); ?>);
    $('.table-detalle').hide();
    $('.table-metas').hide();


  $('.vista-select[data-select="mostrar"]').val(mostrar);

  $('.vista-select[data-select="mes"').val(mes);
  $('.vista-select[data-select="ano"').val(ano);
  $('.vista-select[data-select="modo"').val(modo);
  $('.vista-select[data-select="lunes"').val(lunes);
  $('.vista-select[data-select="trimestre"').val(trimestre);
  $('.vista-select[data-select="semestre"').val(semestre);

  $('.vista-select').hide();
  $('.vista-select[data-select="modo"').show();
  $('.vista-select[data-select="mostrar"').show();

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

$(document).on('click','.tr-clientes',function(e){
  window.location.href = "./?s=resumen-cliente&id=" + $(e.currentTarget).data('idclientes') + "&mes=" + mes + "&ano=" + ano + "&mostrar=" + mostrar;
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
  if(select == 'modo') {
    modo = value;
  } else
  if(select == 'mostrar') {
    mostrar = value;
  }

  cambiarVista();

});

function cambiarVista() {

  if(modo == 'Semanal') {
    window.location.href = "./?s=ventas&lunes=" + lunes + "&mes=" + mes + "&ano=" + ano + "&modo=" + modo + "&order_by=" + order_by + "&order=" + order + "&mostrar=" + mostrar;
  }
  if(modo == 'Mensual') {
    window.location.href = "./?s=ventas&mes=" + mes + "&ano=" + ano + "&modo=" + modo + "&order_by=" + order_by + "&order=" + order + "&mostrar=" + mostrar;
  }
  if(modo == 'Trimestral') {
    window.location.href = "./?s=ventas&trimestre=" + trimestre + "&ano=" + ano + "&modo=" + modo + "&order_by=" + order_by + "&order=" + order + "&mostrar=" + mostrar;
  }
  if(modo == 'Semestral') {
    window.location.href = "./?s=ventas&semestre=" + semestre + "&ano=" + ano + "&modo=" + modo + "&order_by=" + order_by + "&order=" + order + "&mostrar=" + mostrar;
  }
  if(modo == 'Historico') {
    window.location.href = "./?s=ventas&modo=" + modo + "&order_by=" + order_by + "&order=" + order + "&mostrar=" + mostrar;
  }
  if(modo == 'Anual') {
    window.location.href = "./?s=ventas&ano=" + ano + "&modo=" + modo + "&order_by=" + order_by + "&order=" + order + "&mostrar=" + mostrar;
  }

}

$(document).on('click','#ver-detalle',function(e){
    if(ver_detalle) {
        $('.table-detalle').hide(200);
        $(e.currentTarget).html("Ver detalle");
    } else {
        $('.table-detalle').show(200);
        $(e.currentTarget).html("Ocultar detalle");
    }
    ver_detalle = !ver_detalle;
    
});

$(document).on('click','#ver-metas',function(e){
    if(ver_metas) {
        $('.table-metas').hide(200);
        $(e.currentTarget).html("Ver metas");
    } else {
        $('.table-metas').show(200);
        $(e.currentTarget).html("Ocultar metas");
    }
    ver_metas = !ver_metas;
});

</script>
