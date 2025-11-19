<?php

$usuario = $GLOBALS['usuario'];

/*if($usuario->id!='6') {
  die();
}*/

$id = "";

if(validaIdExists($_GET,'id')) {
  $id = $_GET['id'];
}

$obj = new Despacho($id);
//$media_arr = $obj->getMedia();


$repartidores = Usuario::getAll("WHERE nivel='repartidor'");
$clientes = Cliente::getAll();
$barriles = Barril::getAll();

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

$barriles_enplanta_co2  = Barril::getAll("WHERE clasificacion='CO2' AND estado='En planta'");
$barriles_enplanta_cerveza  = Barril::getBarrilesConProducto();



?>
<style>
.tr-entregas {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800">
      <b>
        <?php
        if($obj->id=="") {
          print '<i class="fas fa-fw fa-plus"></i> Nuevo';
        } else {
          print '<i class="fas fa-fw fa-truck"></i> Detalle';
        }
        ?> Despacho
      </b>
    </h1>
  </div>
  <div>
    <div>
      <?php $usuario->printReturnBtn(); ?>
    </div>
  </div>
</div>
<hr /><?php 
  Msg::show(1,'Despacho guardado con &eacute;xito','primary');
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
        Repartidor:
      </div>
      <div class="col-6 mb-1">
        <select id="id_usuarios_repartidor" class="form-control">
          <?php
          foreach($repartidores as $repartidor) {
            print "<option value='".$repartidor->id."'>".$repartidor->nombre."</option>";
          }
          ?>
        </select>
      </div>
      <table class="table table-striped mt-4" id="pedidos-table">
      </table>
      <div class="col-12 mt-3 mb-1 d-flex justify-content-between">
        <div>
          <button class="btn btn-sm btn-primary" id="agregar-barriles"><i class="fas fa-fw fa-plus"></i> Barril</button>
          <button class="btn btn-sm btn-primary" id="agregar-cajas"><i class="fas fa-fw fa-plus"></i> Caja</button>
          <button class="btn btn-sm btn-primary" id="agregar-co2-btn"><i class="fas fa-fw fa-plus"></i> CO<sub>2</sub></button>
          <button class="btn btn-sm btn-primary" id="agregar-vasos-aceptar"><i class="fas fa-fw fa-plus"></i> Vasos</button>
        </div>
        <div>
          <button class="btn btn-sm btn-primary guardar-btn" id="guardar-btn"><i class="fas fa-fw fa-save"></i> Guardar</button>
          <button class="btn btn-sm btn-primary guardar-btn" id="guardar-y-agregar-btn"><i class="fas fa-fw fa-save"></i> Guardar y agregar Nuevo</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" tabindex="-1" role="dialog" id="barrilesModal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Agregar Barril</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-6 mb-3">
              Cantidad
            </div>
            <div class="col-6 mb-3">
              <select class="form-control" id="tipos_barril_select">
              </select>
            </div>
            <div class="col-6 mb-3">
              Tipo de Cerveza
            </div>
            <div class="col-6 mb-3">
              <select class="form-control" id="tipos_barril_cerveza_select">
              </select>
            </div>
            <div class="col-6 mb-3">
              C&oacute;digo
            </div>
            <div class="col-6 mb-3">
              <select class="form-control" id="codigo_barril">
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

    <div class="modal" tabindex="-1" role="dialog" id="barrilesCO2Modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Agregar Barril CO<sub>2</sub></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row"><div class="col-6 mb-3">
              Cantidad
            </div>
            <div class="col-6 mb-3">
              <select class="form-control" id="barriles_co2_cantidad_select">
              </select>
            </div>
            <div class="col-6 mb-3">
              C&oacute;digo
            </div>
            <div class="col-6 mb-3">
              <select class="form-control" id="barriles_co2_codigo_select">
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="agregar-barriles-co2-aceptar" data-bs-dismiss="modal">Agregar</button>
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
              Cantidad
            </div>
            <div class="col-6 mb-3">
              <select class="form-control" id="tipos_caja_select">
              </select>
            </div>
            <div class="col-6 mb-3">
              Tipo de Cerveza
            </div>
            <div class="col-6 mb-3">
              <select class="form-control" id="tipos_caja_cerveza_select">
                <?php
                foreach($productos['Caja'] as $producto_caja) {
                  print "<option>".$producto_caja->nombre."</option>";
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

var productos_lista = [];
var vasos = false;
var tipos_barril = <?= json_encode($tipos_barril,JSON_PRETTY_PRINT); ?>;
var tipos_caja = <?= json_encode($tipos_caja,JSON_PRETTY_PRINT); ?>;
var barriles_enplanta_co2  = <?= json_encode($barriles_enplanta_co2,JSON_PRETTY_PRINT); ?>;
var barriles_enplanta_cerveza  = <?= json_encode($barriles_enplanta_cerveza,JSON_PRETTY_PRINT); ?>;
var productos = <?= json_encode($productos,JSON_PRETTY_PRINT); ?>;
var barriles_co2_cantidad = <?= json_encode($barriles_co2_cantidad,JSON_PRETTY_PRINT); ?>;


$(document).ready(function() {
  armarTiposBarriles();
  armarProductosBarriles();
  armarCodigosBarriles();
  armarTiposCajas();
  armarProductosCajas();
  armarBarrilesCO2CantidadSelect();
  armarBarrilesCO2CodigoSelect();
  checkDisableGuardarBtn();
});

function checkDisableGuardarBtn() {
  console.log(productos_lista);
  if(productos_lista.length == 0) {
    $('.guardar-btn').attr('DISABLED',true);
  } else {
    $('.guardar-btn').attr('DISABLED',false);
  }
}



function armarTiposBarriles() {

  $('#tipos_barril_select').empty();
  var html = '';

  tipos_barril.forEach(function(tb) {
    html += '<option>' + tb + '</option>';
  });

  $('#tipos_barril_select').html(html);

}

function armarProductosBarriles() {

  $('#tipos_barril_cerveza_select').empty();
  var html = '';

  productos_barril = productos['Barril'][$('#tipos_barril_select').val()];

  productos_barril.forEach(function(pb) {
    html += '<option value="' + pb.id + '">' + pb.nombre + '</option>';
  });

  $('#tipos_barril_cerveza_select').html(html);

}

function armarCodigosBarriles() {

  $('#codigo_barril').empty();

  var html = '';

  var barriles_cerveza = barriles_enplanta_cerveza /*.filter((b) => {
    console.log('b.id_productos',b.id_productos)
    console.log('$(#tipos_barril_cerveza_select).val()',$('#tipos_barril_cerveza_select').val())
    return b.tipo_barril == $('#tipos_barril_select').val() && b.id_productos == $('#tipos_barril_cerveza_select').val()
  });*/
  console.log(barriles_cerveza);
  barriles_cerveza.forEach(function(barril) {
    html += '<option value="' + barril.id + '">' + barril.codigo + ' | ' + barril.nombre + '</option>';
  });

  $('#codigo_barril').html(html);

  if(html == '') {
    $('#agregar-barriles-aceptar').attr('DISABLED',true);
  } else {
    $('#agregar-barriles-aceptar').attr('DISABLED',false);
  }

}


function armarTiposCajas() {

  $('#tipos_caja_select').empty();
  var html = '';

  tipos_caja.forEach(function(tb) {
    html += '<option>' + tb + '</option>';
  });

  $('#tipos_caja_select').html(html);

}

function armarProductosCajas() {

  $('#tipos_caja_cerveza_select').empty();
  var html = '';

  productos_cajas = productos['Caja'][$('#tipos_caja_select').val()];

  productos_cajas.forEach(function(p) {
    html += '<option value="' + p.id + '">' + p.nombre + '</option>';
  });

  $('#tipos_caja_cerveza_select').html(html);

}

function armarBarrilesCO2CantidadSelect() {

  $('#barriles_co2_cantidad_select').empty();
  var html = '';

  barriles_co2_cantidad.forEach(function(c) {
    html += '<option>' + c + '</option>';
  });

  $('#barriles_co2_cantidad_select').html(html);

}

function armarBarrilesCO2CodigoSelect() {

  $('#barriles_co2_codigo_select').empty();

  var html = '';

  var barriles_co2 = barriles_enplanta_co2.filter( (b) => b.tipo_barril == $('#barriles_co2_cantidad_select').val());
  barriles_co2.forEach(function(b) {
    html += '<option value="' + b.id + '">' + b.codigo + '</option>';
    //console.log(b);
  });


  $('#barriles_co2_codigo_select').html(html);

  if(html == '') {
    $('#agregar-barriles-co2-aceptar').attr('DISABLED',true);
  } else {
    $('#agregar-barriles-co2-aceptar').attr('DISABLED',false);
  }

}





$(document).on('click','#agregar-barriles-aceptar',function() {
  productos_lista.push({
    'tipo': 'Barril',
    'cantidad': $('#tipos_barril_select').val(),
    'tipos_cerveza': $('#tipos_barril_cerveza_select option:selected').text(),
    'codigo': $('#codigo_barril option:selected').text(),
    'id_barriles': $('#codigo_barril').val(),
    'id_productos': $('#tipos_barril_cerveza_select').val(),
    'clasificacion': 'Cerveza'
  });
  renderTable();
});





$(document).on('click','#agregar-cajas-aceptar',function() {
  productos_lista.push({
    'tipo': 'Caja',
    'cantidad': $('#tipos_caja_select').val(),
    'tipos_cerveza': $('#tipos_caja_cerveza_select option:selected').text(),
    'codigo': '',
    'id_productos': $('#tipos_caja_cerveza_select').val(),
    'id_barriles': '0',
    'clasificacion': 'Cerveza'
  });
  renderTable();
});




$(document).on('click','#agregar-barriles-co2-aceptar',function() {
  productos_lista.push({
    'tipo': 'Barril',
    'cantidad': 'CO2',
    'tipos_cerveza': '-',
    'codigo': $('#barriles_co2_codigo_select option:selected').text(),
    'id_barriles': $('#barriles_co2_codigo_select').val(),
    'id_productos': '0',
    'clasificacion': 'CO2'
  });
  renderTable();
});




$(document).on('click','#agregar-vasos-aceptar',function() {
  if(vasos) {
    return false;
  }
  productos_lista.push({
    'tipo': 'Vasos',
    'cantidad': '',
    'tipos_cerveza': '',
    'codigo': '',
    'clasificacion': 'Vasos'
  });
  vasos = true;
  renderTable();
});




function renderTable() {
  var html = '';
  productos_lista.forEach(function(producto,index){
    html += '<tr class="productos-tr" data-index="' + index +'">';
    html += '<td>' + producto.clasificacion;
    html += '<td>' + producto.tipo;
    html += '</td><td>' + producto.cantidad;
    html += '</td><td>' + producto.tipos_cerveza;
        html += '</td><td>' + producto.codigo;
    html += '</td><td><button class="btn btn-sm item-eliminar-btn" data-index="' + index + '">x</button>';
    html += '</td></tr>';
  });
  $('#pedidos-table').html(html);
  checkDisableGuardarBtn();
}


$('#guardar-btn').click(function(e){

  e.preventDefault();

  if(productos_lista == []) {
    return false;
  }

  var url = "./ajax/ajax_guardarDespacho.php";
  var data = {
    'despacho': productos_lista,
    'id_usuarios_repartidor': $('#id_usuarios_repartidor').val()
  };

  console.log('data',data);

  $.post(url,data,function(response){
    console.log(response);
    response = JSON.parse(response)
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=nuevo-despachos&msg=1";
    }
    //window.location.href = "./?s=nuevo-despachos&msg=1";
  }).fail(function(){
    //window.location.href = "./?s=nuevo-despachos&msg=1";
    alert("No funciono");
  });

});

$('#guardar-y-agregar-btn').click(function(e){

  e.preventDefault();

  if(productos_lista == []) {
    return false;
  }

  var url = "./ajax/ajax_guardarDespacho.php";
  var data = {
    'despacho': productos_lista,
    'id_usuarios_repartidor': $('#id_usuarios_repartidor').val()
  };

  console.log('data',data);

  $.post(url,data,function(response){
    console.log(response);
    response = JSON.parse(response)
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=nuevo-despachos&msg=1";
    }
    //window.location.href = "./?s=nuevo-despachos&msg=1";
  }).fail(function(){
    //window.location.href = "./?s=nuevo-despachos&msg=1";
    alert("No funciono");
  });

});

$(document).on('click','.item-eliminar-btn',function(e){
  var index = $(e.currentTarget).data('index');
  if(productos_lista[index].tipo == 'Vasos') {
    vasos = false;
  }
  productos_lista.splice(index,1);
  renderTable();
});





$(document).on('click','#agregar-barriles',function() {
  $('#barrilesModal').modal('toggle');
});

$(document).on('click','#agregar-cajas',function(e) {
  e.preventDefault();
  $('#cajasModal').modal('toggle');
});

$(document).on('click','#agregar-co2-btn',function(e) {
  e.preventDefault();
  $('#barrilesCO2Modal').modal('toggle');
});

$(document).on('change','#tipos_barril_select',function(e){
  e.preventDefault();
  armarProductosBarriles();
  armarCodigosBarriles();
});

$(document).on('change','#tipos_caja_select',function(e){
  e.preventDefault();
  armarProductosCajas();
});

$(document).on('change','#barriles_co2_cantidad_select',function(e){
  e.preventDefault();
  armarBarrilesCO2CodigoSelect();
});

$(document).on('change','#tipos_barril_cerveza_select',function(e){
  e.preventDefault();
  armarCodigosBarriles();
})

</script>
