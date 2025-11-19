<?php

//checkAutorizacion("Administrador");
$usuario = $GLOBALS['usuario'];

$id = "";

if(validaIdExists($_GET,'id')) {
  $id = $_GET['id'];
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

$obj = new Cliente($id);

$entregas_anteriores_sin_pagar = Entrega::getAll("WHERE id_clientes='".$obj->id."'  AND creada < '".$ano."-".$mes."-01 00:00:00' AND estado!='Pagada' ORDER BY id asc");
$entregas = Entrega::getAll("WHERE id_clientes='".$obj->id."' AND (creada BETWEEN '".$ano."-".$mes."-01 00:00:00' AND '".$ano."-".$mes."-31 23:59:59' OR (datetime_abonado BETWEEN '".$ano."-".$mes."-01 00:00:00' AND '".$ano."-".$mes."-31 23:59:59') AND abonado=monto) ORDER BY id asc");
$pagos = Pago::getAll("WHERE id_clientes='".$obj->id."'  AND creada BETWEEN '".$ano."-".$mes."-01 00:00:00' AND '".$ano."-".$mes."-31 23:59:59' ORDER BY id asc");
$reemplazos = BarrilReemplazo::getAll("WHERE id_clientes='".$obj->id."'  AND creada BETWEEN '".$ano."-".$mes."-01 00:00:00' AND '".$ano."-".$mes."-31 23:59:59' ORDER BY id asc");
$documentos = Documento::getAll("WHERE id_clientes='".$obj->id."'  AND creada BETWEEN '".$ano."-".$mes."-01 00:00:00' AND '".$ano."-".$mes."-31 23:59:59' ORDER BY id asc");

?>
<style>
.tr-entregas {
  cursor: pointer;
}
.tr-pagos {
  cursor: pointer;
}
.tr-barriles_reemplazos {
  cursor: pointer;
}
.tr-documentos {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800">

      <i class="fas fa-fw fa-handshake"></i> Resumen de Cliente:
      <b>
        <?= $obj->nombre; ?>
      </b>
      <?php if($obj->estado == 'Bloqueado') { ?>
        <span class="badge bg-danger">Bloqueado</span>
      <?php } ?>
    </h1>
  </div>
  <div class="d-flex gap-2">
    <?php if($obj->estado != 'Bloqueado') { ?>
      <button class="btn btn-danger btn-sm" id="bloquear-cliente-btn">
        <i class="fas fa-fw fa-ban"></i> Bloquear
      </button>
    <?php } else { ?>
      <button class="btn btn-success btn-sm" id="desbloquear-cliente-btn">
        <i class="fas fa-fw fa-check"></i> Desbloquear
      </button>
    <?php } ?>
    <div>
      <?php $usuario->printReturnBtn(); ?>
    </div>
  </div>
</div>
<div class="d-sm-flex mb-3">
<div>
    <select class="form-control" id="periodo-select">
      <option>Mensual</option>
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
    <select class="form-control" id="ano-select">
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
if($msg == 1) {
?>
<div class="alert alert-info" role="alert" >Cliente guardado con &eacute;xito.</div>
<?php
} if($msg == 3) {
?>
<div class="alert alert-danger" role="alert" >Pago eliminado.</div>
<?php
} if($msg == 4) {
?>
<div class="alert alert-info" role="alert" >Abono generado con &eacute;xito.</div>
<?php
} if($msg == 5) {
?>
<div class="alert alert-info" role="alert" >Entrega #<?= $_GET['id_entregas']; ?> marcada como "Pagada".</div>
<?php
}
?>
<?php
if($msg == 6) {
?>
<div class="alert alert-info" role="alert" >Entrega guardada con &eacute;xito.</div>
<?php
}
?>
<div class="d-flex justify-content-between">
<h3 class="h5 mb-0 text-gray-800">
  Entregas (<?= (count($entregas_anteriores_sin_pagar) + count($entregas)); ?>)
</h3>
<button class="btn btn-primary btn-sm btn-resumen-cliente-detalle" ><i class="fas fa-fw fa-glasses"></i> Ver Detalle</button>
</div>

<table class="table table-hover table-striped table-sm mt-3">
  <thead class="thead-dark">
    <tr>
      <th>
        ID
      </th>
      <th>
        Fecha
      </th>
      <th>
        Factura
      </th>
      <th>
        Receptor Entrega
      </th>
      <th>
        Monto
      </th>
      <th>
        <b>Adeudado</b>
      </th>
      <th>
        Estado
      </th>
    </tr>
  </thead>
  <tbody>
    <?php

    $total = 0;
    $adeudado = 0;

      foreach($entregas_anteriores_sin_pagar as $entrega) {

        if($entrega->monto == 0) {
          continue;
        }

        $adeudado += $entrega->monto - $entrega->abonado;
    ?>
    <tr class="table-danger tr-entregas" data-identregas="<?= $entrega->id; ?>">
      <td>
        <b>#<?= $entrega->id; ?></b>
      </td>
      <td>
        <b><?= datetime2fechayhora($entrega->creada); ?></b>
      </td>
      <td>
        <a href="./php/dte.php?folio=<?= $entrega->factura; ?>"><b><?= $entrega->factura; ?></b></a>
      </td>
      <td>
        <b><?= $entrega->receptor_nombre; ?></b>
      </td>
      <td>
        <b>$<?= number_format($entrega->monto); ?></b>
      </td>
      <td>
        <b>$<?= number_format($entrega->monto - $entrega->abonado); ?></b>
      </td>
      <td>
        <b><?= $entrega->estado; ?>
      </td>
    </tr>

    <?php
      }
    ?>
    <?php

      foreach($entregas as $entrega) {

        if($entrega->monto == 0) {
          continue;
        }

        $total += $entrega->monto;
        $adeudado += ($entrega->monto - $entrega->abonado);


    ?>
    <tr  class="tr-entregas" data-identregas="<?= $entrega->id; ?>">
      <td>
        #<?= $entrega->id; ?>
      </td>
      <td>
        <?= datetime2fechayhora($entrega->creada); ?>
      </td>
      <td>
        <a href="./php/dte.php?folio=<?= $entrega->factura; ?>"><b><?= $entrega->factura; ?></b></a>
      </td>
      <td>
        <b><?= $entrega->receptor_nombre; ?></b>
      </td>
      <td>
        $<?= number_format($entrega->monto); ?>
      </td>
      <td>
        $<?= number_format($entrega->monto - $entrega->abonado); ?>
      </td>
      <td>
        <?= $entrega->estado; ?>
      </td>
    </tr>
    <?php
      }
    ?>
    <tr style="border: 1px solid black; background-color: white; height: 30px">
      <td>
      </td>
      <td>
      </td>
      <td>
      <td>
      <td>
        <!--<b>
          TOTAL MENSUAL: <b>$<?= number_format($total); ?></b>
        </b>-->
      </td>
      <td>
        <b>
          DEUDA: <b>$<?= number_format($adeudado); ?></b>
        </b>
      </td>
      <td>
      </td>
    </tr>
  </tbody>
</table>
<hr />
<h3 class="h5 mb-0 text-gray-800">
  Pagos (<?= count($pagos); ?>)
</h3>
<table class="table table-hover table-striped table-sm mt-3">
  <thead class="thead-dark">
    <tr>
      <th>
        ID
      </th>
      <th>
        Fecha
      </th>
      <th>
        Monto
      </th>
      <th>
        Forma de Pago
      </th>
    </tr>
  </thead>
  <tbody>
    <?php
    $total = 0;
      foreach($pagos as $pago) {
        $total += $pago->amount;
    ?>
    <tr class="tr-pagos" data-idpagos="<?= $pago->id; ?>">
      <td>
        #<?= $pago->id; ?>
      </td>
      <td>
        <?= datetime2fechayhora($pago->creada); ?>
      </td>
      <td>
        $<?= number_format($pago->amount); ?>
      </td>
      <td>
        <?= $pago->forma_de_pago; ?>
      </td>
    </tr>
    <?php
      }
    ?>
    <tr style="border: 1px solid black; background-color: white; height: 30px">
      <td>
      </td>
      <td>
      </td>
      <td>
        <b>
          TOTAL: <b>$<?= number_format($total); ?></b>
        </b>
      </td>
      <td>
      </td>
    </tr>
  </tbody>
</table>

<button class="btn btn-primary btn-sm" id="generar-abono-btn"><i class="fas fa-fw fa-dollar-sign"></i> Ingresar Pago</button>

<hr />
<h3 class="h5 mb-0 text-gray-800">
  Reemplazos de Barriles (<?= count($reemplazos); ?>)
</h3>
<table class="table table-hover table-striped table-sm mt-3">
  <thead class="thead-dark">
    <tr>
      <th>
        ID
      </th>
      <th>
        Fecha
      </th>
      <th>
        Barril Devuelto
      </th>
      <th>
        Barril Reemplazo
      </th>
      <th>
        Motivo
      </th>
    </tr>
  </thead>
  <tbody>
    <?php
      foreach($reemplazos as $reemplazo) {
        $barril_devuelto = new Barril($reemplazo->id_barriles_devuelto);
        $barril_reemplazo = new Barril($reemplazo->id_barriles_reemplazo);

    ?>
    <tr class="tr-barriles_reemplazos" data-idbarrilesreemplazos="<?= $reemplazo->id; ?>">
      <td>
        #<?= $reemplazo->id; ?>
      </td>
      <td>
        <?= datetime2fechayhora($reemplazo->creada); ?>
      </td>
      <td>
        <?= $barril_devuelto->codigo; ?>
      </td>
      <td>
        <?= $barril_reemplazo->codigo; ?>
      </td>
      <td>
        <?= $reemplazo->motivo; ?>
      </td>
    </tr>
    <?php
      }
    ?>
  </tbody>
</table>

<hr />
<h3 class="h5 mb-0 text-gray-800">
  Documentos (<?= count($documentos); ?>)
</h3>

<table class="table table-hover table-striped table-sm mt-3" id="documentos-table">
  <thead class="thead-dark">
    <tr>
        <th>
            # 
        </th>
        <th>
            Fecha y hora
        </th>
        <th>
            Folio
        </th>
        <th>
            Cliente
        </th>
        <th>
            Monto
        </th>
    </tr>
  </thead>
  <tbody>
    <?php
      $total = 0;
      foreach($documentos as $documento) {
        $cliente = new Cliente($documento->id_clientes);
    ?>
    <tr class="tr-documentos" data-iddocumentos="<?= $documento->id; ?>">
        <td>
            <?= $documento->id; ?>
        </td>
        <td>
            <?= datetime2fechayhora($documento->creada); ?>
        </td>
        <td>
            <?= $documento->folio; ?>
        </td>
        <td>
            <?= $cliente->nombre; ?>
        </td>
        <td>
            $<?= number_format($documento->monto); ?>
        </td>
    </tr>
    <?php
      }
    ?>
  </tbody>
</table>


<div class="modal fade" id="generar-abono-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ingresar Pago</h5>
                <button class="close" type="button" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body" style="font-weight: bold">
              <div class="input-group">
                <span class="input-group-text" id="basic-addon1" style="border-radius: 5px 0px 0px 5px">$</span>
                <input type="number" class="form-control acero" value="0" id="generar-abono-input">
              </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary btn-sm shadow-sm" id="generar-abono-aceptar-btn"><i class="fas fa-fw fa-dollar-sign"></i> Ingresar</a>
            </div>
        </div>
    </div>
</div>

<script>

$(document).on('click','.tr-clientes',function(e){
  window.location.href = "./?s=resumen-cliente&id=" + $(e.currentTarget).data('idclientes');
});
$(document).on('change','#mes-select', function(e) {
  window.location.href = "./?s=resumen-cliente&id=<?= $obj->id; ?>&mes=" + $('#mes-select').val() + "&ano=" + $('#ano-select').val();
});
$(document).on('change','#ano-select', function(e) {
  window.location.href = "./?s=resumen-cliente&id=<?= $obj->id; ?>&mes=" + $('#mes-select').val() + "&ano=" + $('#ano-select').val();
});
$(document).ready(function(){
  $('#mes-select').val(<?= $mes; ?>);
  $('#ano-select').val(<?= $ano; ?>);
});

$(document).on('click','#generar-abono-aceptar-btn',function(e){

  e.preventDefault();

  var input = $('#generar-abono-input').val();

  if(input == 0) {
    alert("Debe ingresar un monto mayor a cero.");
    return false;
  }

  $('#generar-abono-input').attr('DISABLED',true);

  var url = "./ajax/ajax_generarAbono.php";
  var data = {
    'id': <?= $obj->id; ?>,
    'tipopago': 'abono',
    'monto': input,
    'id_usuarios': <?= $usuario->id; ?>
  };

  $.post(url,data,function(raw){
  console.log(raw);
    var response = JSON.parse(raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
    } else {
      window.location.href = "./?s=resumen-cliente&id=<?= $obj->id; ?>&msg=4&mes=<?= $mes; ?>";
    }
  }).fail(function(){
    alert("No funciono");
  });
});

$(document).on('click','#generar-abono-btn',function(){
  $('#generar-abono-modal').modal('toggle');
});

$(document).on('click','.pagar-btn',function(e){

  e.stopPropagation();

  $('#pagar-btn').attr('DISABLED',true);

  var url = "./ajax/ajax_marcarEntregaPagada.php";
  var data = {
    'id': $(e.currentTarget).data('id'),
    'tipopago': 'pago'
  };

  $.post(url,data,function(response){
    if(response.mensaje!="OK") {
      alert("Algo fallo");
    } else {
      window.location.href = "./?s=resumen-cliente&id=" + response.obj.id_clientes + "&id_entregas=" + response.obj.id + "&msg=5&mes=<?= $mes; ?>";
    }
  },'json').fail(function(){
    alert("No funciono");
  });
});

$(document).on('click','.tr-entregas',function(e){
  window.location.href = "./?s=detalle-entregas&id=" + $(e.currentTarget).data('identregas');
});

$(document).on('click','.tr-barriles_reemplazos',function(e){
  window.location.href = "./?s=detalle-barriles_reemplazos&id=" + $(e.currentTarget).data('idbarrilesreemplazos');
});

$(document).on('click','.tr-documentos',function(e){
  window.location.href = "./?s=detalle-documentos&id=" + $(e.currentTarget).data('iddocumentos');
});


$(document).on('click','.btn-resumen-cliente-detalle',function(e){
  window.location.href = "./?s=resumen-cliente-detalle&id=<?= $id; ?>&mes=<?= $mes; ?>&ano=<?= $ano; ?>";
});

$(document).on('keyup','.acero',function(){
  $(this).val($(this).val().replace(/\D/g,''));
  if($(this).val() == "") {
    $(this).val(0);
  }
  $(this).val(parseInt($(this).val()));
});

$(document).on('click','.tr-pagos',function(e) {
  window.location.href = "./?s=detalle-pagos&id=" + $(e.currentTarget).data('idpagos') ;
});

$(document).on('click','#bloquear-cliente-btn',function(e){
  e.preventDefault();

  if(!confirm('¿Está seguro que desea bloquear este cliente?')) {
    return false;
  }

  var url = "./ajax/ajax_bloquearCliente.php";
  var data = {
    'id': <?= $obj->id; ?>,
    'estado': 'Bloqueado'
  };

  $.post(url,data,function(response){
    console.log(response);
    if(response.status == "OK") {
      window.location.reload();
    } else {
      alert("Error al bloquear cliente");
    }
  },'json').fail(function(){
    alert("Error al bloquear cliente");
  });
});

$(document).on('click','#desbloquear-cliente-btn',function(e){
  e.preventDefault();

  if(!confirm('¿Está seguro que desea desbloquear este cliente?')) {
    return false;
  }

  var url = "./ajax/ajax_bloquearCliente.php";
  var data = {
    'id': <?= $obj->id; ?>,
    'estado': 'Activo'
  };

  $.post(url,data,function(response){
    console.log(response);
    if(response.status == "OK") {
      window.location.reload();
    } else {
      alert("Error al desbloquear cliente");
    }
  },'json').fail(function(){
    alert("Error al desbloquear cliente");
  });
});

</script>
