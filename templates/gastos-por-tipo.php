<?php

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

  $modo = "Mensual";
  if(isset($_GET['modo'])) {
    if(in_array($_GET['modo'],['Mensual','Semanal'])) {
      $modo = $_GET['modo'];
    }
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

  if($modo == "Semanal") {
    $start_date = $esta_semana->lunes;
    $end_date = $esta_semana->domingo;
  } else 
  if($modo == "Mensual") {
    $start_date = $ano."-".$mes."-1";
    $end_date = date($ano."-".$mes."-t");
  }



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

  $total = 0;
  $total_vencido = 0;
  $total_por_vencer = 0;
  $total_pagado = 0;

  if($usuario->nivel == "Administrador") {
    $tipos_de_gastos = TipoDeGasto::getAll("ORDER by nombre ".$order);
  } else {
    $tipos_de_gastos = TipoDeGasto::getAll("WHERE nombre='Caja Chica' or nombre='Gas' or nombre='Combustible' or nombre='Envios' ORDER by nombre ".$order);
  }

  $hoy = date('Y-m-d')." 00:00:00";
  $ts_hoy = strtotime($hoy);

  foreach($tipos_de_gastos as $tdg) {
      $gastos_atrasados = Gasto::getAll("WHERE tipo_de_gasto='".$tdg->nombre."' AND date_vencimiento < NOW() AND date_vencimiento < '".$end_date."' AND estado='Por Pagar' AND aprobado='1'");
      $gastos = Gasto::getAll("WHERE tipo_de_gasto='".$tdg->nombre."' AND date_vencimiento BETWEEN '".$start_date."' AND '".$end_date."' AND (date_vencimiento >= NOW() OR estado='Pagado') AND aprobado='1'");
      $gastos = array_merge($gastos,$gastos_atrasados);

      foreach($gastos as $gasto) {
        $tdg->total += $gasto->monto;
        $total += $gasto->monto;
        if($gasto->estado == "Pagado") {
          $tdg->total_pagado += $gasto->monto;
          $total_pagado += $gasto->monto;
        }
        if($gasto->estado == "Por Pagar") {
          $vencimi = $gasto->date_vencimiento." 00:00:00";
          $ts_vencimi = strtotime($vencimi);
          if($ts_vencimi < $ts_hoy) {
            $tdg->total_vencido += $gasto->monto;
            $total_vencido += $gasto->monto;
          } else {
            $tdg->total_por_vencer += $gasto->monto;
            $total_por_vencer += $gasto->monto;
          }
          
        }
      }
  }

  if($order_by == "total") {
    //sort
    $swapping = true;
    while($swapping) {
        $swapping = false;
        foreach($tipos_de_gastos as $key => $tdg) {
            if($key + 1 == count($tipos_de_gastos)) {
                continue;
            }
            if($order == "asc") {
                if($tipos_de_gastos[$key+1]->total > $tipos_de_gastos[$key]->total) {
                    $mayor = $tipos_de_gastos[$key];
                    $menor = $tipos_de_gastos[$key+1];
                    $tipos_de_gastos[$key] = $menor;
                    $tipos_de_gastos[$key+1] = $mayor;
                    $swapping = true;
                }
            } else {
                if($tipos_de_gastos[$key+1]->total < $tipos_de_gastos[$key]->total) {
                    $mayor = $tipos_de_gastos[$key];
                    $menor = $tipos_de_gastos[$key+1];
                    $tipos_de_gastos[$key] = $menor;
                    $tipos_de_gastos[$key+1] = $mayor;
                    $swapping = true;
                }
            }
            
        }
    }
    
  }


?>
<style>
.tr-gastos {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-dollar-sign"></i> <b>Gastos por Tipo</b></h1>
  </div>
  <div>
    <a href="./?s=nuevo-gastos" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm mb-2"><i class="fas fa-fw fa-plus"></i> Ingresar Nuevo Gasto</a>
  </div>
</div>
<div class="d-sm-flex mb-3">
  <div>
    <select class="form-control vista-select me-1" data-select="modo">
      <option>Semanal</option>
      <option>Mensual</option>
    </select>
  </div>
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
    <select class="form-control vista-select me-1" data-select="mes">
      <?php
      for($i = 1; $i<=12; $i++) {
        print "<option value='".$i."'>".int2mes($i)."</option>";
      }
      ?>
    </select>
  </div>
  <div>
    <select class="form-control vista-select" data-select="ano">
      <?php
      for($i = 2023; $i<=date('Y'); $i++) {
        print "<option>".$i."</option>";
      }
      ?>
    </select>
  </div>
</div>
<div class="mb-3">
    <a href="./?s=gastos&mes=<?= $mes."&ano=".$ano; ?>" class="d-sm-inline-block btn btn-sm btn-secondary shadow-sm mb-2"><i class="fas fa-fw fa-list"></i> Ver por &Iacute;tem</a>
    <a href="./?s=gastos-por-tipo&mes=<?= $mes."&ano=".$ano; ?>" class="d-sm-inline-block btn btn-sm btn-secondary shadow-sm mb-2"><i class="fas fa-fw fa-dollar-sign"></i> Ver por Tipo</a>
</div>
<hr />
<table class="table table-hover table-striped table-sm">
  <thead class="thead-dark">
    <tr>
      <th>
        <a href="#" class="sort" data-sortorderby="nombre">Nombre</a>
      </th>
      <th>
        <a href="#" class="sort" data-sortorderby="vencidos">Vencidos</a>
      </th>
      <th>
        <a href="#" class="sort" data-sortorderby="por_vencer">Por Vencer</a>
      </th>
      <th>
        <a href="#" class="sort" data-sortorderby="pagado">Pagado</a>
      </th>
      <th>
        <a href="#" class="sort" data-sortorderby="total">Total</a>
      </th>
    </tr>
  </thead>
  <tbody>
    <?php  
      foreach($tipos_de_gastos as $tdg) {
    ?>
    <tr>
      <td>
        <?php
            if($usuario->nivel == "Administrador") {
                ?>
                <a href="?s=detalle-gastos-por-tipo&id=<?= $tdg->id; ?>&mes=<?= $mes; ?>&ano=<?= $ano; ?>"><?= $tdg->nombre; ?></a>
                <?php
            } else {
                print $tdg->nombre;
            }
            ?>
      </td>
      <td>
        $<?= number_format($tdg->total_vencido); ?>
      </td>
      <td>
        $<?= number_format($tdg->total_por_vencer); ?>
      </td>
      <td>
        $<?= number_format($tdg->total_pagado); ?>
      </td>
      <td>
        $<?= number_format($tdg->total); ?>
      </td>
    </tr>
    <?php
      }
    ?>
  </tbody>
  <tfooter>
    <tr style="background-color: white; border: 1px solid black">
      <td>
        <b>Total</b>
    </td>
    <td>
      <b>
        $<?= number_format($total_vencido); ?>
      </b>
    </td>
    <td>
      <b>
        $<?= number_format($total_por_vencer); ?>
      </b>
    </td>
    <td>
      <b>
        $<?= number_format($total_pagado); ?>
      </b>
    </td>
    <td>
      <b>
        $<?= number_format($total); ?>
      </b>
    </td>
    </tr>
  </tfooter>
</table>
<script>
var mes = '<?= intval($mes); ?>';
var ano = '<?= intval($ano); ?>';
var modo = '<?= $modo; ?>';
var lunes = '<?= $esta_semana->lunes; ?>';
var order_by = '<?= $order_by; ?>';
var order = '<?= $order; ?>';

$(document).ready(function(){

  $('.vista-select[data-select="mes"]').val(mes);
  $('.vista-select[data-select="ano"]').val(ano);
  $('.vista-select[data-select="modo"]').val(modo);
  $('.vista-select[data-select="lunes"]').val(lunes);
  console.log(lunes);

  $('.vista-select').hide();
  $('.vista-select[data-select="modo"]').show();

  if(modo == "Semanal") {
    $('.vista-select[data-select="lunes"]').show();
    $('.vista-select[data-select="mes"]').show();
    $('.vista-select[data-select="ano"]').show();
  }
  if(modo == "Mensual") {
    $('.vista-select[data-select="mes"]').show();
    $('.vista-select[data-select="ano"]').show();
  }

});

$(document).on('change','.date-select', function(e) {
  window.location.href = "./?s=gastos-por-tipo&mes=" + $('#mes-select').val() + "&ano=" + $('#ano-select').val();
});

$(document).on('click','.tr-gastos',function(e) {
    window.location.href = "./?s=detalle-gastos&id=" + $(e.currentTarget).data('idgastos') + "&mes=" + $(e.currentTarget).val() + "&ano=" + ano;;
});

$(document).on('click','.sort',function(e){
  if(order == 'asc') {
    order = 'desc';
  } else {
    order = 'asc';
  }
  window.location.href = "./?s=gastos-por-tipo&mes=" + mes + "&ano=" + ano + "&order_by=" + $(e.currentTarget).data('sortorderby') + "&order=" + order;
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
  }

  cambiarVista();

});

function cambiarVista() {

  if(modo == 'Semanal') {
    window.location.href = "./?s=gastos-por-tipo&lunes=" + lunes + "&mes=" + mes + "&ano=" + ano + "&modo=" + modo + "&order_by=" + order_by + "&order=" + order;
  }
  if(modo == 'Mensual') {
    window.location.href = "./?s=gastos-por-tipo&mes=" + mes + "&ano=" + ano + "&modo=" + modo + "&order_by=" + order_by + "&order=" + order;
  }
  if(modo == 'Trimestral') {
    window.location.href = "./?s=gastos-por-tipo&trimestre=" + trimestre + "&ano=" + ano + "&modo=" + modo + "&order_by=" + order_by + "&order=" + order;
  }
  if(modo == 'Semestral') {
    window.location.href = "./?s=gastos-por-tipo&semestre=" + semestre + "&ano=" + ano + "&modo=" + modo + "&order_by=" + order_by + "&order=" + order;
  }
  if(modo == 'Historico') {
    window.location.href = "./?s=gastos-por-tipo&modo=" + modo + "&order_by=" + order_by + "&order=" + order;
  }
  if(modo == 'Anual') {
    window.location.href = "./?s=gastos-por-tipo&ano=" + ano + "&modo=" + modo + "&order_by=" + order_by + "&order=" + order;
  }

}

</script>