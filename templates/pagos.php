<?php

  //checkAutorizacion(["Administrador"]);

  $mes = date('m');
  if(validaIdExists($_GET,'mes')) {
    $mes = $_GET['mes'];
  }

  $ano = date('Y');
  if(validaIdExists($_GET,'ano')) {
    $ano = $_GET['ano'];
  }

  $id_clientes = 0;
  if(validaIdExists($_GET,'id_clientes')) {
    $id_clientes = $_GET['id_clientes'];
  }

  $primer_dia_mes = $ano."-".$mes."-1";
  $ultimo_dia_mes = date($ano."-".$mes."-t");


  $order_by = "id";
  $order = "desc";

  if(isset($_GET['order_by'])) {
    if($_GET['order_by'] == "cliente") {
      $order_by = "cliente";
    } else
    if($_GET['order_by'] == "forma_de_pago") {
      $order_by = "forma_de_pago";
    } else
    if($_GET['order_by'] == "creada") {
      $order_by = "creada";
    }
    if($_GET['order_by'] == "amount") {
      $order_by = "amount";
    }
  }

  if(isset($_GET['order'])) {
    if($_GET['order'] == "asc") {
      $order = "asc";
    }
  }

  if($order_by != "cliente") {
    if($id_clientes == 0) {
      $pagos = Pago::getAll("WHERE creada BETWEEN '".$primer_dia_mes."' AND '".$ultimo_dia_mes."' ORDER BY ".$order_by." ".$order);
    } else {
      $pagos = Pago::getAll("WHERE creada BETWEEN '".$primer_dia_mes."' AND '".$ultimo_dia_mes."' AND id_clientes='".$id_clientes."' ORDER BY ".$order_by." ".$order);
    }
  } else {
    $query = "INNER JOIN clientes ON pagos.id_clientes = clientes.id WHERE pagos.creada BETWEEN '".$primer_dia_mes."' AND '".$ultimo_dia_mes."' ORDER BY clientes.nombre ".$order;
    $pagos = Pago::getAll($query);
  }
  
  
  $clientes =  Cliente::getAll();
  
?>
<style>
.tr-pagos {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-handshake"></i> <b>Pagos</b></h1>
  </div>
  <div>
    <a href="./?s=nuevo-documentos" class="btn btn-sm btn-primary"><i class="fas fa-fw fa-plus"></i> Ingresar Documento</a>
  </div>
</div>
<div class="d-sm-flex mb-3">
  <div>
    <select class="form-control me-1 fecha-select" id="mes-select">
      <?php
      for($i = 1; $i<=12; $i++) {
        if($i<10) {
          $i = "0".$i;
        }
        print "<option value='".$i."'>".int2mes($i)."</option>";
      }
      ?>
    </select>
  </div>
  <div>
    <select class="form-control fecha-select" id="ano-select">
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
<table class="table table-hover table-striped table-sm" id="pagos-table">
  <thead class="thead-dark">
    <tr>
      <th>
        <a href="#" class="sort" data-sortorderby="id">ID</a>
      </th>
      <th>
        <a href="#" class="sort" data-sortorderby="cliente">Cliente</a>
      </th>
      <th>
        <a href="#" class="sort" data-sortorderby="creada">Fecha</a>
      </th>
      <th>
        <a href="#" class="sort" data-sortorderby="forma_de_pago">Forma de Pago</a>
      </th>
      <th>
        <a href="#" class="sort" data-sortorderby="amount">Monto</a>
      </th>
    </tr>
  </thead>
  <tbody>
    <?php
      $total = 0;
      foreach($pagos as $pago) {
        $cliente = new Cliente($pago->id_clientes);
        $total += $pago->amount;
    ?>
    <tr class="tr-pagos" data-idpagos="<?= $pago->id; ?>">
      <td>
        <?= $pago->id; ?>
      </td>
      <td>
        <?= $cliente->nombre; ?>
      </td>
      <td>
        <?= datetime2fechayhora($pago->creada); ?>
      </td>
      <td>
        <?= $pago->forma_de_pago; ?>
      </td>
      <td>
        $<?= number_format($pago->amount); ?>
      </td>
    </tr>
    <?php
      }
    ?>
  </tbody>
  <tfooter>
    <tr>
    <td colspan="3">
    </td>
    <td>
      Total:
    </td>
    <td style="text-align: right">
      <b>$<?= number_format($total); ?></b>
    </td>
    </tr>
  </tfooter>
</table>
<script>
var mes = '<?= $mes; ?>';
var ano = '<?= $ano; ?>';
var id_clientes = '<?= $id_clientes; ?>';
var order_by = '<?= $order_by; ?>';
var order = '<?= $order; ?>';

new DataTable('#pagos-table', {
    language: {
        url: '//cdn.datatables.net/plug-ins/2.1.3/i18n/es-CL.json'
    },
    pageLength: 50,
    stateSave: true,
    paging: false
});

$(document).on('change','.fecha-select', function(e) {
  window.location.href = "./?s=pagos&mes=" + $('#mes-select').val() + "&ano=" + $('#ano-select').val() + "&id_clientes=" + id_clientes;
});

$(document).on('change','#clientes-select', function(e) {
  window.location.href = "./?s=pagos&mes=" + mes + "&id_clientes=" + $(e.currentTarget).val() + "&ano=" + $('#ano-select').val();
});

$(document).on('click','.sort',function(e) {
  if(order == "asc") {
    order = "desc";
  } else {
    order = "asc";
  }
  window.location.href = "./?s=pagos&mes=" + mes + "&id_clientes=" + id_clientes + "&ano=" + ano + "&order_by=" + $(e.currentTarget).data('sortorderby') + "&order=" + order;
});

$(document).on('click','.tr-pagos',function(e) {
  window.location.href = "?s=detalle-pagos&id=" + $(e.currentTarget).data('idpagos');
});

$(document).ready(function(){
  $('#mes-select').val('<?= $mes; ?>');
  $('#ano-select').val('<?= $ano; ?>');
});
</script>