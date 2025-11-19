<?php

    $msg = 0;
    if(isset($_GET['msg'])) {
        $msg = $_GET['msg'];
    }

    $pedidos = Pedido::getAll("WHERE estado!='Entregado' ORDER BY id desc");
    $clientes = Cliente::getAll();
    $barriles_disponibles = Barril::getAll("");

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
    $barriles_enplanta_cerveza  = Barril::getAll("WHERE clasificacion='Cerveza' AND estado='En planta'");

    $pedidos_productos = array();

?>
<style>
.tr-pedidos {
  cursor: pointer;
}
</style>

<div class="row">
    <div class="col-md-6">

<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800">
        <b>Pedidos</b>
    </h1>
  </div>
  <div>
  </div>
</div>
<div class="row mb-4 d-none">
  <div class="col-lg-2">
    <select class="form-control">
      <option>Solicitados</option>
      <option>Entregados</option>
    </select>
  </div>
  <div class="col-lg-3">
    <select class="form-control">
      <option>Todos los clientes</option>
      <option>Entregados</option>
    </select>
  </div>
  <div class="col-md-3">
  </div>
  <div class="col-md-3">
  </div>


</div>
<hr />
<?php
  if($msg == 2) {
?>
<div class="alert alert-danger" role="alert" >Pedido eliminado con &eacute;xito.</div>
<?php
  }
  foreach($pedidos as $pedido) {
    $cliente = new Cliente($pedido->id_clientes);
    $productos = PedidoProducto::getAll("WHERE id_pedidos='".$pedido->id."'");
?>
<div class="card w-100 shadow mb-3">
  <div class="card-body tr-pedidos" data-idpedidos="<?= $pedido->id; ?>">
  <div class="row">
    <div class="col-12 d-flex justify-content-between">
      <h5 class="card-title mb-3 h5"><i class="fas fa-fw fa-truck"></i> PEDIDO #<?= $pedido->id; ?></h5>
      <!--<button type="button" class="close eliminar-pedido-btn" data-bs-dismiss="modal" aria-label="Close" data-idpedidos="<?= $pedido->id; ?>">
        <span aria-hidden="true">&times;</span>
      </button>-->
      <button type="button" class="btn-close eliminar-pedido-btn" data-bs-dismiss="modal" aria-label="Close" data-idpedidos="<?= $pedido->id; ?>"></button>
    </div>
    <div class="col-6">
      <b><?= $cliente->nombre; ?></b>
    </div>
    <div class="col-6">
      Estado: <b><?= $pedido->estado; ?></b>
    </div>
    <div class="col-6">
      <b><?= datetime2fechayhora($pedido->creada); ?></b>
    </div>
  </div>
    <table class="table table-striped mt-4" id="tr-desplegable-<?= $pedido->id; ?>" style="display: none;">
      <?php
      foreach($productos as $pp) {
        $pedidos_productos[] = $pp;
        ?>
        <tr>
          <td>
            <?= $pp->tipo; ?>
          </td>
          <td>
            <?= $pp->cantidad; ?>
          </td>
          <td>
            <?= $pp->tipos_cerveza; ?>
          </td>
          <td>
            <?= $pp->estado; ?>
          </td>
          <td>
            <?php
            if($pp->estado == "Solicitado") {
            ?>
            <button class="btn btn-sm btn-secondary pedido-producto-btn" data-idpedidosproductos="<?= $pp->id; ?>">Agregar a Despacho</button>
            <?php
            }
          ?>
          </td>
        <?php
      }
       ?>
    </table>
  </div>
</div>
<?php
  }
?>

    </div>
    <div class="col-md-6">

    <div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800">
      <b>
        Despacho
      </b>
    </h1>
  </div>
</div>
<hr /><?php 
  Msg::show(1,'Despacho guardado con &eacute;xito','primary');
?>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="id_barriles">Fecha:</label>
                <input type="date" class="form-control" name="fecha" value="<?= date('Y-m-d'); ?>">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="id_barriles">Repartidor:</label>
                <select id="id_usuarios_repartidor" class="form-control">
                <?php
                foreach($repartidores as $repartidor) {
                    print "<option value='".$repartidor->id."'>".$repartidor->nombre."</option>";
                }
                ?>
                </select>
            </div>
        </div>
    </div>
    <table class="table table-striped mt-4" id="pedidos-table">
    </table>
    <div class="col-12 mt-3 mb-1 d-flex justify-content-between">
        &nbsp;
        <button class="btn btn-sm btn-primary guardar-btn" id="guardar-btn"><i class="fas fa-fw fa-save"></i> Guardar</button>
    </div>

    </div>
</div>



<hr/>


<div class="modal fade" tabindex="-1" role="dialog" id="eliminar-modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Eliminar Pedido</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <center><h5>Desea eliminar este Pedido?<br/>Este paso no es reversible.</h5></center>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" id="eliminar-aceptar" data-bs-dismiss="modal">Eliminar</button>
        </div>
      </div>
    </div>
  </div>

<div class="modal fade" tabindex="-1" role="dialog" id="asignar-barril-pedido-despachos-modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Asignar Barril a Pedido</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="id_barriles">Seleccionar Barril</label>
                    <select class="form-control" id="id_barriles" name="id_barriles" required>
                        <option value="">Seleccione un barril</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="id_usuarios_repartidor">Seleccionar Repartidor</label>
                    <select class="form-control" id="id_usuarios_repartidor" name="id_usuarios_repartidor" required>
                        <option value="">Seleccione un repartidor</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="asignar-barril-btn">Asignar</button>
            </div>
        </div>
    </div>
</div>


  <div class="modal fade" tabindex="-1" role="dialog" id="barrilesModal">
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

    <div class="modal fade" tabindex="-1" role="dialog" id="barrilesCO2Modal">
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


  <div class="modal fade" tabindex="-1" role="dialog" id="cajasModal">
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

  var id_eliminar;
  var pedido_producto;
  var pedidos_productos = <?= json_encode($pedidos_productos,JSON_PRETTY_PRINT); ?>;

$(document).ready(function(){
    toggle_asignar_barril_btn();
});

$(document).on('click','.tr-pedidos',function(e){
    var id_pedidos = $(e.currentTarget).data('idpedidos');
    var id = '#tr-desplegable-' + id_pedidos;
    $(id).slideToggle(200);
});


$(document).on('click','.pedido-producto-btn',function(e){
    e.stopPropagation();
    pedido_producto = pedidos_productos.find((pp) => pp.id == $(e.currentTarget).data('idpedidosproductos'));
    console.log(pedido_producto);

    if(pedido_producto.tipo == "Barril") {
        $('#barrilesModal').modal('toggle');
    }
    if(pedido_producto.tipo == "Caja") {
        $('#cajasModal').modal('toggle');
    }
    if(pedido_producto.tipo == "CO2") {
        $('#CO2Modal').modal('toggle');
    }

    $('#id_barriles').val("");
    $('#id_usuarios_repartidor').val("");
    toggle_asignar_barril_btn();
});


$(document).on('click','#pedido-producto-aceptar-btn',function(e){

    e.stopPropagation();

    var id_barriles = $('#id_barriles').val();
    var id_usuarios_repartidor = $('#id_usuarios_repartidor').val();

    if (id_barriles === '' || id_usuarios_repartidor === '') {
        alert('Por favor, seleccione un barril y un repartidor.');
        return;
    }

    var data = {
        'id_barriles': id_barriles,
        'id_usuarios_repartidor': id_usuarios_repartidor,
        'id_pedidos_productos': id_pedidos_productos
    }

    var url = './ajax/ajax_marcarDespachado.php';
    $.post(url,data,function(response){
    if(response.status!="OK") {
        alert("Algo fallo");
        return false;
    } else {
        location.reload();
    }
    },"json").fail(function(){
    alert("No funciono");
    });
});



$(document).on('click','.eliminar-pedido-btn',function(e){
  e.preventDefault();
  id_eliminar = $(e.currentTarget).data('idpedidos');
  $('#eliminar-modal').modal('toggle');
})

$(document).on('click','#eliminar-aceptar',function(e){
  var data = {
    'id': id_eliminar,
    'modo': 'pedidos'
  }
  var url = './ajax/ajax_eliminarEntidad.php';
  $.post(url,data,function(response){
    if(response.status!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=pedidos&msg=2";
    }
  },"json").fail(function(){
    alert("No funciono");
  });
});

function toggle_asignar_barril_btn() {
    if ($('#id_barriles').val() === '' || $('#id_usuarios_repartidor').val() === '') {
        $('#asignar-barril-btn').prop('disabled', true);
    } else {
        $('#asignar-barril-btn').prop('disabled', false);
    }
}


$('#id_barriles, #id_usuarios_repartidor').on('change', function() {
    toggle_asignar_barril_btn();
});

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

  var barriles_cerveza = barriles_enplanta_cerveza.filter((b) => b.tipo_barril == $('#tipos_barril_select').val());
  console.log(barriles_cerveza);
  barriles_cerveza.forEach(function(barril) {
    html += '<option value="' + barril.id + '">' + barril.codigo + '</option>';
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

  //console.log(data);

  $.post(url,data,function(response){
    //console.log(response);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=central-despacho&msg=1";
    }
  },'json').fail(function(){
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

  //console.log(data);

  $.post(url,data,function(response){
    //console.log(response);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=nuevo-despachos&msg=1";
    }
  },'json').fail(function(){
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




</script>
