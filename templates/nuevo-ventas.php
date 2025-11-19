<?php

  $categorias = Categoria::getAll();
  $subcategorias = SubCategoria::getAll();
  $estados = $GLOBALS['estados'];

 ?>
<style>
.tr-productos {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-plus"></i> <b>Nueva Venta</b></h1>
  </div>
  <div>

  </div>
</div>
<input type="search" class="form-control drgrass-search-bar-input mt-3 shadow" placeholder="Buscar en tienda...">
<table class="table table-striped table-sm mt-1 mb-3 shadow" style="border: 1px solid black !important">
  <thead class="thead-light">
    <tr>
      <th></th>
      <th>Producto</th>
      <th>Cantidad</th>
      <th>Monto</th>
      <th></th>
  </thead>
  <tbody id="nuevo-ventas-table-tbody">
  </tbody>
  <tfoot class="table-light" style="border: 1px solid black">
    <tr>
      <td>
      <td>
        TOTAL:
      </td>
      <td>
      </td>
      <td id="table-total" style="font-weight: bold">
      </td>
      <td>
      </td>
    </tr>
  </tfoot>
</table>



<form id="ventas-form" class="mt-5">
  <input type="hidden" name="id" value="">
  <input type="hidden" name="modo" value="productos">
  <div class="row">
    <div class="col-md-6 row">
    </div>
    <div class="col-md-12 row">
      <div class="col-5 mb-1">
        Fecha:
      </div>
      <div class="col-7 mb-1">
        <input type="date" name="fecha" class="form-control" value="<?= date('Y-m-d'); ?>">
      </div>
      <div class="col-5 mb-1">
        Estado:
      </div>
      <div class="col-7 mb-1">
        <select name="estado" class="form-control">
          <?php
            foreach($estados as $key=>$estado) {
              print "<option>";
              print $estado;
              print "</option>";
            }
          ?>
        </select>
      </div>
      <div class="col-5 mb-1">
        Efectivo:
      </div>
      <div class="col-7 mb-1">
        <div class="input-group mr-sm-2">
          <div class="input-group-prepend">
            <div class="input-group-text">$</div>
          </div>
          <input type="text" class="form-control" value="0" name="efectivo">
        </div>
      </div>
      <div class="col-5 mb-1">
        Debito:
      </div>
      <div class="col-7 mb-1">
        <div class="input-group mr-sm-2">
          <div class="input-group-prepend">
            <div class="input-group-text">$</div>
          </div>
          <input type="text" class="form-control" value="0" name="debito">
        </div>
      </div>
      <div class="col-5 mb-1">
        Credito:
      </div>
      <div class="col-7 mb-1">
        <div class="input-group mr-sm-2">
          <div class="input-group-prepend">
            <div class="input-group-text">$</div>
          </div>
          <input type="text" class="form-control" value="0" name="credito">
        </div>
      </div>
      <div class="col-5 mb-1">
        Transferencia:
      </div>
      <div class="col-7 mb-1">
        <div class="input-group mr-sm-2">
          <div class="input-group-prepend">
            <div class="input-group-text">$</div>
          </div>
          <input type="text" class="form-control" value="0" name="transferencia">
        </div>
      </div>
      <div class="col-12 mb-1">
        Observaciones:
      </div>
      <div class="col-12 mb-3">
        <textarea name="observaciones" class="form-control"></textarea>
      </div>
      <div class="col-12 mb-3">
        <div class="custom-control custom-checkbox my-1 mr-sm-2">
          <input type="checkbox" class="custom-control-input" name="boleta">
          <label class="custom-control-label" for="customControlInline">Generar Boleta</label>
        </div>
      </div>
      <div class="col-12 mb-1 text-right">
        <button class="btn btn-primary" id="guardar-btn">Guardar</button>
      </div>
    </div>
  </div>
</form>
<script>

var items = [];

function agregarProducto(producto){
  items.push({
    "producto": producto,
    "cantidad": 1
  });
  console.log(items);
}

function armarTabla() {
  var html = "";
  var total = 0;
  $('#nuevo-ventas-table-tbody').html('');
  items.forEach(function(item,index){
    var producto = item.producto;
    html += "<tr class='venta-productos-tr' data-iditem='" + producto.id + "'>";
    html += "<td></td>";
    html += "<td>" + producto.nombre + "</td>";
    html += "<td>1</td>";
    html += "<td>$" + producto.monto + "</td>";
    html += "<td><span aria-hidden=true class='item-eliminar-btn' data-itemindex='" + index + "'>×</span></td>";
    html += "</tr>";
    total += parseInt(producto.monto);
  });

  $('#nuevo-ventas-table-tbody').append(html);
  $('#table-total').html("$" + total);
}

$(document).ready(function(){

$( ".drgrass-search-bar-input" ).autocomplete({
  minLength: 0,
  source: './ajax/ajax_getProductos.php',
  focus: function( event, ui ) {
    $( ".drgrass-search-bar-input" ).val( ui.item.nombre );
    return false;
  },
  select: function( event, ui ) {
    agregarProducto(ui.item);
    armarTabla();
  }
})
.autocomplete( "instance" )._renderItem = function( ul, item ) {
  return $( "<li>" )
    .append( "<div class='search-results-li' data-idproductos='" + (item.length + 1) + "'>" + item.nombre + "<br>" + item.descripcion + "</div>" )
    .appendTo( ul );
};
});

$(document).on('click','.item-eliminar-btn',function(e){
  var index = $(e.currentTarget).data('itemindex');
  items.splice(index,1);
  armarTabla();
});

$(document).on('click','#guardar-btn',function(e){

  e.preventDefault();

  var url = "./ajax/ajax_guardarVenta.php";
  var data = getDataForm("ventas");

  $.post(url,data,function(response){
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-productos&id=" + response.obj.id;
    }
  },"json").fail(function(){
    alert("No funciono");
  });
});
</script>
