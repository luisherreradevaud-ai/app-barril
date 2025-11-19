<?php

  $usuario = $GLOBALS['usuario'];

  $clientes = Cliente::getAll("WHERE estado='Activo' ORDER BY nombre asc");

  $tipos_barril = $GLOBALS['tipos_barril'];
  $tipos_barril_cerveza = $GLOBALS['tipos_barril_cerveza'];
  $tipos_caja = $GLOBALS['tipos_caja'];
  $tipos_caja_cerveza = $GLOBALS['tipos_caja_cerveza'];
  $usuario = $GLOBALS['usuario'];

  $barriles_co2_cantidad = ['7L','9L','25L'];
  $tipos_barril = ['20L','30L','50L'];
  $tipos_caja = $GLOBALS['tipos_caja'];
  $tipos_caja_cerveza = $GLOBALS['tipos_caja_cerveza'];

  foreach($tipos_barril as $tb) {
    $productos['Barril'][$tb] = Producto::getAll("WHERE tipo='Barril' AND cantidad='".$tb."'");
  }

  foreach($tipos_caja as $tc) {
    $productos['Caja'][$tc] = Producto::getAll("WHERE tipo='Caja' AND cantidad='".$tc."'");
  }

?>
<style>
.tr-pedidos {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h4 mb-0 text-gray-800">Nuevo pedido</h1>
  </div>
  <div>
    <div>
      <?= $usuario->printReturnBtn(); ?>
    </div>
  </div>
</div>
<?php 
  Msg::show(1,'Pedido guardado con &eacute;xito.','primary');
?>
<div class="row">
  <div class="col-lg-8 row">
    <div class="col-6 mb-1">
      Fecha:
    </div>
    <div class="col-6 mb-1">
      <input type="date" class="form-control" name="fecha" value="<?= date('Y-m-d'); ?>">
    </div>
    <div class="col-6 mb-1">
      Cliente:
    </div>
    <div class="col-6 mb-1">
      <select class="form-control" name="id_clientes" value="<?= date('Y-m-d'); ?>" id="id_clientes-select">
        <?php
        foreach($clientes as $cliente) {
        ?>
        <option value="<?= $cliente->id; ?>"><?= $cliente->nombre; ?></option>
        <?php
        }
        ?>
      </select>
    </div>
    <table class="table table-striped mt-4" id="pedidos-table">
    </table>
    <div class="col-12 mt-3 mb-1 d-flex justify-content-between">
      <div>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal" id="agregarBarrilBtn"><i class="fas fa-fw fa-plus"></i> Barril</button>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#cajasModal"><i class="fas fa-fw fa-plus"></i> Caja</button>
        <button class="btn btn-sm btn-primary" id="agregar-co2-aceptar"><i class="fas fa-fw fa-plus"></i> CO<sub>2</sub></button>
        <button class="btn btn-sm btn-primary" id="agregar-vasos-aceptar"><i class="fas fa-fw fa-plus"></i> Vasos</button>
      </div>
      <div>
        <button class="btn btn-sm btn-secondary me-1" id="guardar-btn"><i class="fas fa-fw fa-lock"></i> Guardar</button>
        <button class="btn btn-sm btn-secondary" id="guardar-y-agregar-nuevo-btn"><i class="fas fa-fw fa-refresh"></i> Guardar y Agregar Nuevo</button>
      </div>
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
            Litraje
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
            Producto
          </div>
          <div class="col-6 mb-3">
            <select class="form-control" id="tipos_barril_cerveza_select">
              <?php
              /*foreach($tipos_barril_cerveza as $tbc) {
                print "<option>".$tbc."</option>";
              }*/
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

var productos_disponibles = <?= json_encode($productos,JSON_PRETTY_PRINT); ?>;

$(document).on('click','#agregar-barriles-aceptar',function() {
  productos.push({
    'tipo': 'Barril',
    'cantidad': $('#tipos_barril_select').val(),
    'tipos_cerveza': $('#tipos_barril_cerveza_select option:selected').text(),
    'cantidad_items': $('#cantidad_items_select_1').val(),
    'id_productos': $('#tipos_barril_cerveza_select').val()
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
    'id_clientes': $('#id_clientes-select').val(),
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
      window.location.href = "./?s=central-de-despacho&msg=1#pedidos";
    }
  }).fail(function(){
    alert("No funciono");
  });

});

$('#guardar-y-agregar-nuevo-btn').click(function(e){

  e.preventDefault();

  if(productos == []) {
    return false;
  }

  var url = "./ajax/ajax_guardarPedido.php";
  var data = {
    'id_clientes': $('#id_clientes-select').val(),
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
      window.location.href = "./?s=nuevo-pedidos&msg=1";
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


$(document).on('click','#agregarBarrilBtn',armarBarriles);
$(document).on('change','#tipos_barril_select',armarBarriles);
function armarBarriles() {
  const litraje = $('#tipos_barril_select').val();
  const filtered = productos_disponibles['Barril'][litraje];
  var html = '';
  filtered.forEach((prod) => {
    html += '<option value="' + prod.id + '">' + prod.nombre + '</option>';
  });
  $('#tipos_barril_cerveza_select').html(html);
}


</script>
