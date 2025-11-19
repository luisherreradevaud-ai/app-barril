<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-dollar-sign"></i> <b>Caja</b></h1>
  </div>
  <div>
    <div>
      <input class="form-control mt-3" type="date" id="datepicker" style="max-width: 200px; background-color: var(--bg_color)" value="<?= date('Y-m-d'); ?>">
    </div>
  </div>
</div>
<table class="table table-hover table-striped mt-4 shadow" style="">
  <thead>
    <tr>
      <th>#</th>
      <th>Hora</th>
      <th>Efectivo</th>
      <th>Tarjeta</th>
      <th>Transferencia</th>
      <th>Monto total</th>
      <th>Estado</th>
  </thead>
  <tbody id="caja-table-tbody">
  </tbody>
</table>

<script>
$(document).ready(function(){
  getCaja();
});

function getCaja(){
  var url = "./ajax/ajax_getCaja.php";
  var data = {
    'date': $('#datepicker').val()
  };
  $.ajax({
    type: "GET",
    url: url,
    data: data,
    dataType: "json",
    success: function(response){
      rellenarCaja(response);
    }
  });
}

function rellenarCaja(response){

  console.log(response);

  var html = '';
  var total_efectivo = 0;
  var total_tarjeta = 0;
  var total_transferencia = 0;
  var total_monto = 0;
  var total_propina = 0;

  $('#caja-table-tbody').html('');
  $('#caja-table-tbody').hide();

  for(var i = 0; i<response.length; i++){
    html += "<tr class='caja-venta-tr' data-idcerradas='" + response[i].id + "'>";
    html += "<td>" + response[i].id + "</td>";
    html += "<td>" + response[i].creada.split(' ')[1] + "</td>";
    html += "<td>" + response[i].cerrada.split(' ')[1] + "</td>";
    html += "<td>$" + parseInt(response[i].efectivo).toLocaleString('en-US') + "</td>";
    html += "<td>$" + parseInt(response[i].tarjeta).toLocaleString('en-US') + "</td>";
    html += "<td>$" + parseInt(response[i].transferencia).toLocaleString('en-US') + "</td>";
    html += "<td>$" + parseInt(response[i].monto).toLocaleString('en-US') + "</td>";
    html += "<td>$" + parseInt(response[i].propina).toLocaleString('en-US') + "</td>";
    html += "</tr>";

    total_efectivo += parseInt(response[i].efectivo);
    total_tarjeta += parseInt(response[i].tarjeta);
    total_transferencia += parseInt(response[i].transferencia);
    total_monto += parseInt(response[i].monto);
    total_propina += parseInt(response[i].propina);

  }

  html += "<tr style='border: 1px solid white'>";
  html += "<td colspan='3' style='font-size: 1.2em; height: 50px;vertical-align:middle'>TOTALES</td>";
  html += "<td style='font-size: 1.2em; height: 50px;vertical-align:middle'>$" + total_efectivo.toLocaleString('en-US') + "</td>";
  html += "<td style='font-size: 1.2em; height: 50px;vertical-align:middle'>$" + total_tarjeta.toLocaleString('en-US') + "</td>";
  html += "<td style='font-size: 1.2em; height: 50px;vertical-align:middle'>$" + total_transferencia.toLocaleString('en-US') + "</td>";
  html += "<td style='font-size: 1.2em; height: 50px;vertical-align:middle'>$" + total_monto.toLocaleString('en-US') + "</td>";
  html += "<td style='font-size: 1.2em; height: 50px;vertical-align:middle'>$" + total_propina.toLocaleString('en-US') + "</td>";
  html += "</tr>";

  $('#caja-table-tbody').html(html);
  $('#caja-table-tbody').show(400);

}

$(document).on('change','#datepicker',function(){
  getCaja();
});

$(document).on('click','.caja-venta-tr',function(e){
  var url = "./ajax/getVenta.php";
  $.ajax({
    url: url,
    data: {
      'id_cerradas': $(e.currentTarget).data('idcerradas')
    },
    method: 'GET',
    success: function(response){
      $('#venta-modal-body').html(response);
      $('#cerradaModal').modal('toggle');
      $('#venta-modal-id').html($(e.currentTarget).data('idcerradas'));
    }
  });
});
</script>
