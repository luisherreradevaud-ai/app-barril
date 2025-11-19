<?php

  $usuario = $GLOBALS['usuario'];

  $cliente = new Cliente($usuario->id_clientes);

  $tipos_barril = $GLOBALS['tipos_barril'];
  $tipos_barril_cerveza = $GLOBALS['tipos_barril_cerveza'];
  $tipos_caja = $GLOBALS['tipos_caja'];
  $tipos_caja_cerveza = $GLOBALS['tipos_caja_cerveza'];

?>
<style>
.tr-pedidos {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h4 mb-0 text-gray-800"><b><?= $cliente->nombre; ?></b></h1>
    <h1 class="h5 mb-0 text-gray-800">Nuevo pedido</h1>
  </div>
  <div>
    <div>
      <a href="./?s=central-clientes-completa" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm mb-2"><i class="fas fa-fw fa-backward"></i> Volver a Panel</a>
    </div>
  </div>
</div>
<hr />
<div class="row">
  <div class="col-lg-8 row">
    <div class="col-6 mb-1">
      Fecha:
    </div>
    <div class="col-6 mb-1">
      <input type="date" class="form-control" name="fecha" value="<?= date('Y-m-d'); ?>">
    </div>
    <table class="table table-striped mt-4" id="pedidos-table">
    </table>
    <div class="col-12 mt-3 mb-1 d-flex justify-content-between">
      <div>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal"><i class="fas fa-fw fa-plus"></i> Barril</button>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#cajasModal"><i class="fas fa-fw fa-plus"></i> Caja</button>
        <button class="btn btn-sm btn-primary" id="agregar-co2-aceptar"><i class="fas fa-fw fa-plus"></i> CO<sub>2</sub></button>
        <button class="btn btn-sm btn-primary" id="agregar-vasos-aceptar"><i class="fas fa-fw fa-plus"></i> Vasos</button>
      </div>
      <button class="btn btn-sm btn-secondary" id="guardar-btn"><i class="fas fa-fw fa-lock"></i> Enviar Pedido</button>
    </div>
  </div>
</div>

<div class="modal" tabindex="-1" role="dialog" id="exampleModal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Agregar Barril</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-6 mb-3">
            Tipo de Barril
          </div>
          <div class="col-6 mb-3">
            <select class="form-control" id="tipos_barril_select">
              <?php
              foreach($tipos_barril as $tb) {
                print "<option>".$tb."</option>";
              }
              ?>
            </select>
          </div>
          <div class="col-6 mb-3">
            Tipo de Cerveza
          </div>
          <div class="col-6 mb-3">
            <select class="form-control" id="tipos_barril_cerveza_select">
              <?php
              foreach($tipos_barril_cerveza as $tbc) {
                print "<option>".$tbc."</option>";
              }
              ?>
            </select>
          </div>
          <div class="col-6 mb-3">
            Cantidad
          </div>
          <div class="col-6 mb-3">
            <select class="form-control" id="cantidad_items_select_1">
              <?php
              for($i = 1; $i<10; $i++) {
                print "<option>".$i."</option>";
              }
              ?>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="agregar-barriles-aceptar" data-bs-dismiss="modal">Agregar</button>
      </div>
    </div>
  </div>
</div>


<div class="modal" tabindex="-1" role="dialog" id="cajasModal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Agregar Caja</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-6 mb-3">
            Producto
          </div>
          <div class="col-6 mb-3">
            <select class="form-control" id="tipos_caja_select">
              <?php
              foreach($tipos_caja as $tb) {
                print "<option>".$tb."</option>";
              }
              ?>
            </select>
          </div>
          <div class="col-6 mb-3">
            Tipo de Cerveza
          </div>
          <div class="col-6 mb-3">
            <select class="form-control" id="tipos_caja_cerveza_select">
              <?php
              foreach($tipos_caja_cerveza as $tbc) {
                print "<option>".$tbc."</option>";
              }
              ?>
            </select>
          </div>
          <div class="col-6 mb-3">
            Cantidad
          </div>
          <div class="col-6 mb-3">
            <select class="form-control" id="cantidad_items_select_2">
              <?php
              for($i = 1; $i<10; $i++) {
                print "<option>".$i."</option>";
              }
              ?>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="agregar-cajas-aceptar" data-bs-dismiss="modal">Agregar</button>
      </div>
    </div>
  </div>
</div>


<script>

var productos = [];
var co2 = false;
var vasos = false;

$(document).on('click','#agregar-barriles-aceptar',function() {
  productos.push({
    'tipo': 'Barril',
    'cantidad': $('#tipos_barril_select').val(),
    'tipos_cerveza': $('#tipos_barril_cerveza_select').val(),
    'cantidad_items': $('#cantidad_items_select_1').val()
  });
  renderTable();
});

$(document).on('click','#agregar-cajas-aceptar',function() {
  productos.push({
    'tipo': 'Caja',
    'cantidad': $('#tipos_caja_select').val(),
    'tipos_cerveza': $('#tipos_caja_cerveza_select').val(),
    'cantidad_items': $('#cantidad_items_select_2').val()
  });
  renderTable();
});

$(document).on('click','#agregar-co2-aceptar',function() {
  if(co2) {
    return false;
  }
  productos.push({
    'tipo': 'CO2',
    'cantidad': '',
    'tipos_cerveza': '',
    'cantidad_items': 1
  });
  co2 = true;
  renderTable();
});

$(document).on('click','#agregar-vasos-aceptar',function() {
  if(vasos) {
    return false;
  }
  productos.push({
    'tipo': 'Vasos',
    'cantidad': '',
    'tipos_cerveza': '',
    'cantidad_items': 1
  });
  vasos = true;
  renderTable();
});

function renderTable() {
  var html = '';
  productos.forEach(function(producto,index){
    html += '<tr class="productos-tr" data-index="' + index +'"><td>' + producto.tipo;
    html += '</td><td>' + producto.cantidad;
    html += '</td><td>' + producto.tipos_cerveza;
    html += '</td><td>' + producto.cantidad_items;
    html += '</td><td><button class="btn btn-sm item-eliminar-btn" data-index="' + index + '">x</button>';
    html += '</td></tr>';
  });
  $('#pedidos-table').html(html);
}


$('#guardar-btn').click(function(e){

  e.preventDefault();

  if(productos == []) {
    return false;
  }

  var url = "./ajax/ajax_guardarPedido.php";
  var data = {
    'id_clientes': <?= $cliente->id; ?>,
    'pedido': productos
  };

  console.log(data);
  

  $.post(url,data,function(response){
    console.log(response);
    response = JSON.parse(response);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=central-clientes-completa&msg=1#pedidos";
    }
  }).fail(function(){
    alert("No funciono");
  });

});

$(document).on('click','.item-eliminar-btn',function(e){

  var index = $(e.currentTarget).data('index');

  if(productos[index].tipo == 'CO2') {
    co2 = false;
  }
  if(productos[index].tipo == 'Vasos') {
    vasos = false;
  }

  productos.splice(index,1);

  renderTable();

});
</script>
