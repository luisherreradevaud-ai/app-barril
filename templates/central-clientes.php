<?php

  //checkAutorizacion(["Cliente"]);
  $usuario = $GLOBALS['usuario'];
  $cliente = new Cliente($usuario->id_clientes);
  $entregas = Entrega::getAll("WHERE id_clientes='".$cliente->id."'");
?>
<style>
.tr-entregas {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h4 mb-0 text-gray-800"><b><?= $cliente->nombre; ?></b></h1>
    <h1 class="h5 mb-0 text-gray-800">Facturaci&oacute;n</h1>
  </div>
  <div>
    <div>
    </div>
  </div>
</div>
<hr />
<table class="table table-striped table-sm">
  <thead class="thead-dark">
    <tr>
      <th>
      </th>
      <th>
        ID
      </th>
      <th>
        Fecha de entrega
      </th>
      <th>
        Factura
      </th>
      <th>
        Vencimiento
      </th>
      <th>
        Estado
      </th>
      <th>
        Monto
      </th>
    </tr>
  </thead>
  <tbody>
    <?php
      foreach($entregas as $entrega) {
        $barriles = Barril::getAll("WHERE id_entregas='".$entrega->id."'");
        $cajas = Caja::getAll("WHERE id_entregas='".$entrega->id."'");
        $entregas_productos = EntregaProducto::getAll("WHERE id_entregas='".$entrega->id."'");
    ?>
    <tr data-identregas="<?= $entrega->id; ?>" class="tr-entregas">
      <td>
        <?php
        if($entrega->estado != "Pagada") {
         ?>
        <input type="checkbox" data-monto="<?= $entrega->monto; ?>" data-id="<?= $entrega->id; ?>" class="checkbox-monto">
        <?php
        }
        ?>
      </td>
      <td>
        #<?= $entrega->id; ?>
      </td>
      <td>
        <?= datetime2fecha($entrega->creada); ?>
      </td>
      <td>
        <?= $entrega->factura; ?>
      </td>
      <td>
        <?= date2fecha($entrega->fecha_vencimiento); ?>
      </td>
      <td>
        <?= $entrega->estado; ?>
      </td>
      <td>
        <b>$<?= number_format($entrega->monto); ?></b>
      </td>
    </tr>
    <tr id="tr-desplegable-<?= $entrega->id; ?>" style="display: none; border: 1px solid #BBB">
      <td colspan="6" class="tr-desplegable" style="padding: 10px">
        <b>Detalle Entrega #<?= $entrega->id; ?>:</b>
        <br />
        <br />
        <table class="table">
          <?php
          foreach($entregas_productos as $ep) {
            ?>
            <tr>
              <td>
                <?= $ep->tipo; ?>
              </td>
              <td>
                <?= $ep->cantidad; ?>
              </td>
              <td>
                <?= $ep->tipos_cerveza; ?>
              </td>
              <td>
                <b>$<?= number_format($ep->monto); ?></b>
              </td>
            </tr>
            <?php
          }
          ?>

        </table>
      </td>
    </tr>
    <?php
      }
    ?>
  </tbody>
</table>
<table class="table w-100" style="border: 3px solid black">
  <tr>
    <td class="d-flex justify-content-between">
      <span class="h5 text-gray-800 mt-2">
        TOTAL: <b>$<span class="total">0</span></b>
      </span>
      <button class="d-sm-inline-block btn btn-primary shadow-sm" id="pagar-btn"><i class="fas fa-fw fa-dollar-sign"></i> Pagar</button>
    </td>
  </tr>
</table>
<form id="pay" method="POST" action="">
  <input id="token_ws" type="hidden" name="token_ws" value="">
</form>
<script>

var entregas = <?= json_encode($entregas,JSON_PRETTY_PRINT); ?>;
var total = 0;
var ids_entregas = [];

$(document).ready(function(){
  calcularTotal();
})

$(document).on('click','.checkbox-monto',function(e){
  e.stopPropagation();
  calcularTotal();
});

function calcularTotal() {
  total = 0;
  ids_entregas = []
  $('.checkbox-monto').each(function(){
    if($(this).is(':checked')){
      total += $(this).data('monto');
      ids_entregas.push($(this).data('id'));
    }
  })
  $('.total').html(parseInt(total).toLocaleString('en-US'));
  ids_entregas = ids_entregas.join(',');
  if(total == 0) {
    $('#pagar-btn').attr('disabled',true);
  } else {
    $('#pagar-btn').attr('disabled',false);
  }
}

$(document).on('click','.tr-despachos',function(e){
  window.location.href = "./?s=detalle-despachos&id=" + $(e.currentTarget).data('iddespachos');
});

$(document).on('click','#pagar-btn',function() {
  var url = "./ajax/ajax_getTransbankResponse.php";
  var data = {
    'total': total,
    'ids_entregas': ids_entregas,
    'id_clientes': <?= $cliente->id; ?>
  };
  console.log(data);

  $.ajax({
    url: url,
    data: data,
    method: 'POST',
    dataType: 'JSON',
    success: function(response){
      console.log(response);
      if(response.mensaje=="OK") {
        submitToWebpay(response.tokens);
      }
    }
  });

});

function submitToWebpay(tokens) {
  $('#pay').attr('action',tokens.url);
  $('#token_ws').val(tokens.token_ws);
  $('#pay').submit();
}

$(document).on('click','.tr-entregas',function(e){

  var id_entregas = $(e.currentTarget).data('identregas');
  var id = '#tr-desplegable-' + id_entregas;

  $(id).toggle(200);
});

</script>
