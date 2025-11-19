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

$productos_todos = Producto::getAll();

$barriles_enplanta_co2  = Barril::getAll("WHERE clasificacion='CO2' AND estado='En planta'");
$barriles_enplanta_cerveza = Barril::getAll("WHERE clasificacion='Cerveza' AND estado='En planta' AND id_batches!=0 AND litros_cargados>0 ORDER BY codigo asc");

foreach($barriles_enplanta_cerveza as $bepc) {
  $bepc->batch_info = new Batch($bepc->id_batches);
  $bepc->id_recetas = $bepc->batch_info->id_recetas;
}
$pedidos = Pedido::getAll("WHERE estado!='Entregado' ORDER BY id desc");

$barriles_en_planta = Barril::getAll("WHERE id_batches!=0 AND estado='En planta' AND clasificacion='Cerveza' ORDER BY codigo asc");

$barriles_disponibles = Barril::getAll("WHERE litros_cargados!=litraje AND estado='En planta' AND clasificacion='Cerveza' ORDER BY codigo asc");

$batches_activos_maduracion = BatchActivo::getAll("WHERE estado='Maduración' ORDER BY id_batches asc");
$ba_maduracion = [];
foreach($batches_activos_maduracion as $baf) {
  $ba_maduracion[$baf->id_batches][$baf->id] = $baf;
}

$activos_traspaso = [];
$activos_traspaso_disponibles = Activo::getAll("WHERE clase='Fermentador' AND id_batches='0' ORDER BY codigo asc");

?>
<style>
.tr-entregas {
  cursor: pointer;
}
</style>
<?php 
  Msg::show(1,'Pedido guardado con &eacute;xito','primary');
?>
<!--
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

-->
<script>

var productos_lista = [];
var vasos = false;
var tipos_barril = <?= json_encode($tipos_barril,JSON_PRETTY_PRINT); ?>;
var tipos_caja = <?= json_encode($tipos_caja,JSON_PRETTY_PRINT); ?>;
var barriles_enplanta_co2  = <?= json_encode($barriles_enplanta_co2,JSON_PRETTY_PRINT); ?>;

var productos = <?= json_encode($productos,JSON_PRETTY_PRINT); ?>;
var barriles_co2_cantidad = <?= json_encode($barriles_co2_cantidad,JSON_PRETTY_PRINT); ?>;

var barriles_enplanta_cerveza  = <?= json_encode($barriles_enplanta_cerveza,JSON_PRETTY_PRINT); ?>;
var barriles_enplanta_cerveza_utilizados = [];

var productos_todos = <?= json_encode($productos_todos, JSON_PRETTY_PRINT); ?>;

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
  return false;
  if(productos_lista.length == 0) {
    $('.guardar-btn').attr('DISABLED',true);
  } else {
    $('.guardar-btn').attr('DISABLED',false);
  }
}



function armarTiposBarriles() {
  return false;

  $('#tipos_barril_select').empty();
  var html = '';

  tipos_barril.forEach(function(tb) {
    html += '<option>' + tb + '</option>';
  });

  $('#tipos_barril_select').html(html);

}

function armarProductosBarriles() {
  return false;

  $('#tipos_barril_cerveza_select').empty();
  var html = '';

  return false;

  productos_barril = productos['Barril'][$('#tipos_barril_select').val()];

  productos_barril.forEach(function(pb) {
    html += '<option value="' + pb.id + '">' + pb.nombre + '</option>';
  });

  $('#tipos_barril_cerveza_select').html(html);

}

function armarCodigosBarriles() {
  return false;
  return false;

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
  return false;

  $('#tipos_caja_select').empty();
  var html = '';

  tipos_caja.forEach(function(tb) {
    html += '<option>' + tb + '</option>';
  });

  $('#tipos_caja_select').html(html);

}

function armarProductosCajas() {
  return false;
  return false;

  $('#tipos_caja_cerveza_select').empty();
  var html = '';



}

function armarBarrilesCO2CantidadSelect() {
  return false;

  $('#barriles_co2_cantidad_select').empty();
  var html = '';

  barriles_co2_cantidad.forEach(function(c) {
    html += '<option>' + c + '</option>';
  });

  $('#barriles_co2_cantidad_select').html(html);

}

function armarBarrilesCO2CodigoSelect() {
  return false;

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

  console.log(data);
  //return false;

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

    <style>
        .dragger-container {
            min-height: 300px;
            border: 1px dashed #ccc;
            padding: 10px;
        }

        .dragger-container-2 {
            min-height: 150px !important;
            border: 1px dashed #aaa;
            border-top: 0px;
            transition: 0.7s;
        }

        .dragger-container-2:hover {
            border: 1px dashed #ddd;
            border-top: 0px;
        }

        .dragger-card {
            margin-bottom: 10px;
            cursor: grab;
        }
    </style>



        <input type="hidden" name="fecha" value="<?= date('Y-m-d'); ?>">
        <div class="row">
          <div class="col-4 px-2">
            <div class="w-100 text-center">
              <h2>Llenado de barriles</h2>
            </div>
            <br/>
            

              <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#llenar-barriles-modal" id="llenar-barriles-btn"> 
                  Llenar Barril
              </button>

              <table class="table table-sm table-striped table-bordered shadow mt-3">
                  <thead>
                      <tr>
                          <th>
                              Barril
                          </th>
                          <th>
                              Batch
                          </th>
                          <th>
                              Cargado
                          </th>
                      </tr>
                  </thead>
                  <tbody>
                      <?php
                      foreach($barriles_en_planta as $ba) {
                          $batch = new Batch($ba->id_batches);
                          $receta = new Receta($batch->id_recetas);
                          //print_r($batch);
                          ?>
                          <tr data-idbarriles="<?= $ba->id; ?>">
                              <td>
                                  <?= $ba->codigo; ?>
                              </td>
                              <td>
                                  <?= $receta->nombre; ?> - #<?= $batch->id; ?>
                              </td>
                              <td>
                                  <?= $ba->litros_cargados; ?>L
                          </tr>
                          <?php
                      }
                      ?>
                  </tbody>
              </table>


          </div>
          <div class="col-4 px-2">
            <div class="w-100 text-center">
              <h2>Pedidos</h2>
            </div>
            <br/>
            <div class="dragger-container" id="container1" data-dropzone-role="pedidos">
              <a href="./?s=nuevo-pedidos" class="btn btn-sm">
                + Crear Pedido
              </a>
              <?php
              $pedidos_productos = array();
              foreach($pedidos as $pedido) {
                  
                  $cliente = new Cliente($pedido->id_clientes);
                  $productos = PedidoProducto::getAll("WHERE id_pedidos='".$pedido->id."'");
                  $date1 = strtotime(date('Y-m-d'));
                  $date2 = strtotime(date('Y-m-d',strtotime($pedido->creada)));
                  $tiempo_pasado = floor(($date1 - $date2) / (60 * 60 * 24));
                ?>
                  <?php
                  foreach($productos as $pp) {
                      $pedidos_productos[] = $pp;
                      ?>
                      <div class="card dragger-card shadow border" draggable="true" data-id-pedidos-productos="<?= $pp->id; ?>" data-id-barriles="0" data-id-despachos-productos="0">
                          <div class="card-body">
                              <div class="d-flex justify-content-between">
                                  <div>
                                      <div class="fw-bold fs-4">
                                          <?= $cliente->nombre; ?>
                                      </div>
                                      <div class="text-muted fw-semibold">
                                          <?= $tiempo_pasado; ?> días de realizado.
                                      </div>
                                  </div>
                                  <div class="text-center px-4 py-1 shadow" style="border-radius: 5px; border: 1px solid gray; background-color:">
                                    <div class="fw-bold fs-4 dragger-card-barril-codigo-div" style="display: none"><span class='dragger-card-barril-codigo-span'></span><br/></div>
                                    <?= $pp->tipo; ?> <?= $pp->cantidad; ?>
                                    <br/>
                                    <?= $pp->tipos_cerveza; ?>
                                  </div>
                              </div>
                          </div>
                      </div>
                      <?php
                    }
                  }
                  ?>
          </div>
          </div>






        <div class="col-4 px-2">
            <div class="d-flex justify-content-between">
              <div class="w-100 text-center">
              <h2>Despachos</h2>
            </div>
            </div>
            <br/>
            <div class="repartos-dragger-container">
              <?php
              foreach($repartidores as $repartidor) {
                $despachos = Despacho::getAll("WHERE id_usuarios_repartidor='".$repartidor->id."'");
                ?>
                <div class="form-control">
                  <?= $repartidor->nombre; ?>
                </div>
                <div class="dragger-container dragger-container-2 mb-4" data-id-usuarios-repartidor="<?= $repartidor->id; ?>" data-dropzone-role="despachos">
                  <?php
                    foreach($despachos as $d) {
                      $dps = DespachoProducto::getAll("WHERE id_despachos='".$d->id."'");
                      foreach($dps as $dp) {
                        $pp = new PedidoProducto($dp->id_pedidos_productos);
                        $pedidi = new Pedido($pp->id_pedidos);
                        $cliente = new Cliente($pedidi->id_clientes);
                        $barrili = new Barril($dp->id_barriles);
                        $date1 = strtotime(date('Y-m-d'));
                        $date2 = strtotime(date('Y-m-d',strtotime($dp->creada)));
                        $tiempo_pasado = floor(($date1 - $date2) / (60 * 60 * 24));
                        ?>
                        <div class="card dragger-card shadow border" draggable="true" data-id-pedidos-productos="<?= $dp->id_pedidos_productos; ?>" data-id-barriles="<?= $barrili->id; ?>" data-id-despachos-productos="<?= $dp->id; ?>">
                          <div class="card-body">
                              <div class="d-flex justify-content-between">
                                  <div>
                                      <div class="fw-bold fs-4">
                                          <?= $cliente->nombre; ?>
                                      </div>
                                      <div class="text-muted fw-semibold">
                                          <?= $tiempo_pasado; ?> días de realizado.
                                      </div>
                                  </div>
                                  <div class="text-center px-4 py-1 shadow" style="border-radius: 5px; border: 1px solid gray; background-color:">
                                    <div class="fw-bold fs-4 dragger-card-barril-codigo-div">
                                      <span class='dragger-card-barril-codigo-span'>
                                        <?= $dp->codigo; ?>
                                      </span>
                                      <br/>
                                    </div>
                                    <?= $dp->tipo; ?> <?= $dp->cantidad; ?>
                                    <br/>
                                    <?= $dp->tipos_cerveza; ?>
                                  </div>
                              </div>
                          </div>
                      </div>
                        <?php
                      }
                    }
                  ?>
                </div>

                


                <?php
              }
              ?>
            </div>

            <div class="mt-3">
                    <!--<button class="btn btn-sm btn-primary" id="agregar-barriles"><i class="fas fa-fw fa-plus"></i> Barril</button>
                    <button class="btn btn-sm btn-primary" id="agregar-cajas"><i class="fas fa-fw fa-plus"></i> Caja</button>-->
                    <button class="btn btn-sm btn-outline-secondary" id="agregar-co2-btn"><i class="fas fa-fw fa-plus"></i> Agregar CO<sub>2</sub></button>
                    <button class="btn btn-sm btn-outline-secondary" id="agregar-vasos-aceptar"><i class="fas fa-fw fa-plus"></i> Agregar Vasos</button>
            </div>
        </div>
        <div class="col-4 px-2 d-none">
          <div class="w-100 text-center">
            <h2>Entregas</h2>
          </div>
          <br/>
          <?php
            $entregas = Entrega::getAll("ORDER BY id desc LIMIT 3");
            foreach($entregas as $entrega) {
              $cliente = new Cliente($entrega->id_clientes);
              $entrega->getEntregasProductos()
          ?>

          <div class="card shadow mb-3">
            <div class="card-body p-0">
              <div class="fw-bold fs-4 p-3 d-flex justify-content-between">
                  <div>
                    <?= $cliente->nombre; ?>
                    <div class="text-muted" style="font-size: 0.5em">
                      <?= datetime2fechayhora($entrega->creada); ?>
                    </div>
                  </div>
                  <div>
                    <?php
                    if(is_numeric($entrega->factura) && $entrega->factura > 0) {
                      ?>
                      <a href="./clientes/dte.php?folio=<?= $entrega->factura; ?>" class="text-danger">
                        <i class="bi bi-file-pdf"></i>
                      </a>
                    <?php
                    }
                    ?>
                  </div>
              </div>
              <div class="accordion" id="accordionExample<?= $entrega->id; ?>">
                <div class="accordion-item">
                  <h2 class="accordion-header" id="headingOne<?= $entrega->id; ?>">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne<?= $entrega->id; ?>" aria-expanded="true" aria-controls="collapseOne<?= $entrega->id; ?>" style="height: 30px">
                      <?= count($entrega->entregas_productos); ?> Productos
                    </button>
                  </h2>
                  <div id="collapseOne<?= $entrega->id; ?>" class="accordion-collapse collapse show" aria-labelledby="headingOne<?= $entrega->id; ?>" data-bs-parent="#accordionExample<?= $entrega->id; ?>">
                    <div class="accordion-body p-0">
                      <table class="table table-striped table-sm my-0">
                        <?php
                          foreach($entrega->getEntregasProductos() as $ep) {
                        ?>
                          <tr>
                            <td class="ps-3">
                              <?= $ep->codigo; ?> 
                            </td>
                            <td>
                              <?= $ep->cantidad; ?>
                            </td>
                            <td class="pe-3">
                              <?= $ep->tipos_cerveza; ?>
                            </td>
                          </tr>
                        <?php
                          }
                        ?>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
              

            </div>
          </div>

          <?php
            }
          ?>

            
        </div>
    </div>




<div class="modal modal-fade" tabindex="-1" role="dialog" id="llenar-barriles-modal">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Cargar Barril
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row align-items-bottom">
                    <div class="col-4 mb-1 border p-3 bg-light" style="border-radius: 10px">
                        <div class="mt-2 text-center fs-3 fw-bold">
                            Fermentador
                        </div>
                        <select class="form-control mt-2" id="llenar-barriles_id_batches_activos-select">
                            <?php
                            foreach($batches_activos_maduracion as $bam) {
                                $activo = new Activo($bam->id_activos);
                                $batch = new Batch($bam->id_batches);
                                $receta = new Receta($batch->id_recetas);
                            ?>
                            <option value="<?= $bam->id; ?>"><?= $activo->codigo; ?> (<?= $activo->litraje; ?>L - <?= $receta->nombre; ?>)</option>
                            <?php
                            }
                            ?>
                        </select>
                        <label class="mt-2">
                            Disponible:
                        </label>
                        <div class="input-group">
                            <input type="number" class="form-control fw-bold fs-3" id="llenar-barriles-fermentador-cantidad-disponible" value="0" READONLY>
                            <span class="input-group-text fs-3" id="basic-addon1" style="border-radius: 0px 10px 10px 0px">Litros</span>
                        </div>
                    </div>
                    <div class="col-4 mb-1 align-middle text-center fw-bold px-3">
                        <div class="w-100 text-center">
                        </div>
                        <label class="mt-2">
                            Cantidad a cargar:
                        </label>
                        <div class="input-group">
                            <input type="number" class="form-control fw-bold fs-3" id="llenar-barriles-cantidad-a-cargar" value="0">
                            <span class="input-group-text fs-3" id="basic-addon1" style="border-radius: 0px 10px 10px 0px">Litros</span>
                        </div>
                        <button type="button" class="btn btn-primary mt-3 w-100" id="llenar-barril-aceptar-btn" data-bs-dismiss="modal">
                            <i class='fas fa-fw fa-forward'></i> Cargar <i class='fas fa-fw fa-forward'></i>
                        </button>

                    </div>
                    <div class="col-4 mb-1 border p-3 bg-light" style="border-radius: 10px">
                        <div class="mt-2 text-center fs-3 fw-bold">
                            Barril
                        </div>
                        <select class="form-control mt-2" id="llenar-barriles_id_barriles-select">
                            <?php
                            foreach($barriles_disponibles as $bd) {
                            ?>
                            <option value="<?= $bd->id; ?>"><?= $bd->codigo; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                        <label class="mt-2">
                            Cargado:
                        </label>
                        <div class="input-group">
                            <input type="number" class="form-control fw-bold fs-3" id="llenar-barriles-barril-litros-cargados" READONLY>
                            <span class="input-group-text fs-3" id="basic-addon1" style="border-radius: 0px 10px 10px 0px">Litros</span>
                        </div>
                    </div> 
                </div>
            </div>
        </div>
    </div>
</div>



    <script>

        var pedidos_productos = <?= json_encode($pedidos_productos, JSON_PRETTY_PRINT); ?>;
        let draggedCard = null;
        let originalContainer = null;
        let isConfirmed = false;
        let repartidor_seleccionado = 0;

        const barriles_disponibles = <?= json_encode($barriles_disponibles, JSON_PRETTY_PRINT); ?>;
        const batches_activos_maduracion = <?= json_encode($batches_activos_maduracion, JSON_PRETTY_PRINT); ?>;

        $(document).ready(function() {

            
            
            $('.dragger-card').on('dragstart', function(e) {
                draggedCard = $(this);
                originalContainer = $(this).parent();
                e.originalEvent.dataTransfer.setData('text/plain', $(this).data('id-pedidos-productos'));
            });

            $('.dragger-container').on('dragover', function(e) {
                e.preventDefault();
            });

              $('.dragger-container').on('drop', function(e) {
                
                e.preventDefault();
                let container = $(this);

                if(container.data('dropzone-role') == 'despachos') {
                  repartidor_seleccionado = $(e.currentTarget).data('id-usuarios-repartidor');
                  if(draggedCard.data('id-barriles') == 0) {
                    var pedido_producto = pedidos_productos.find( (pp) => pp.id == draggedCard.data('id-pedidos-productos'));
                    if(pedido_producto.id_productos == 0) {
                      alert('No hay producto asociado a este pedido. Debe ser creado nuevamente.');
                      return false;
                    }
                    armarCodigosBarriles2(pedido_producto.id_productos);
                    container.append(draggedCard);
                  } else {
                    container.append(draggedCard);
                    getDraggeCards();
                  }
                } else {
                  var barril_index = barriles_enplanta_cerveza_utilizados.findIndex((b) => b.id == draggedCard.data('id-barriles'));
                  barriles_enplanta_cerveza.push(barriles_enplanta_cerveza_utilizados[barril_index]);
                  barriles_enplanta_cerveza_utilizados.splice(barril_index,1);
                  draggedCard.find('.dragger-card-barril-codigo-span').html('');
                  draggedCard.find('.dragger-card-barril-codigo-div').css('display','none');
                  draggedCard.data('id-barriles',0);
                  container.append(draggedCard);
                  getDraggeCards();
                }
                
              });

            $('#confirmBtn').on('click', function() {
                isConfirmed = true;
                var barril_index = barriles_enplanta_cerveza.findIndex((b) => b.id == $('#agregar-barriles-codigo-select').val());
                draggedCard.data('id-barriles',barriles_enplanta_cerveza[barril_index].id);
                draggedCard.find('.dragger-card-barril-codigo-span').html(barriles_enplanta_cerveza[barril_index].codigo);
                draggedCard.find('.dragger-card-barril-codigo-div').css('display','block');
                $('#agregar-barriles-modal').modal('hide');
                
                barriles_enplanta_cerveza_utilizados.push(barriles_enplanta_cerveza[barril_index]);
                barriles_enplanta_cerveza.splice(barril_index,1);

                getDraggeCards();

                draggedCard = null;

            });

            $('#cancelBtn').on('click', function() {
                isConfirmed = false;
                $('#agregar-barriles-modal').modal('hide');
                if (draggedCard && originalContainer) {
                    originalContainer.append(draggedCard);
                }
                draggedCard = null;
            });

            $('#agregar-barriles-modal').on('hidden.bs.modal', function () {
                if (!isConfirmed && draggedCard && originalContainer) {
                    originalContainer.append(draggedCard);
                }
                isConfirmed = false;
                draggedCard = null;
            });
            
        });

        function armarCodigosBarriles2(id_productos) {
          
          const prod = productos_todos.find((p) => p.id == id_productos);
          if(id_productos == '' || id_productos == 0 || prod === undefined) {
            alert('Producto inválido');
            return false;
          }
          if(prod.id_recetas == 0) {
            alert('Receta inválida inválido');
            return false;
          }

          orderBarrilesEnPlanta();

          $('#agregar-barriles-codigo-select').empty();

          var html = '';

          var barriles_cerveza = barriles_enplanta_cerveza.filter((b) => b.tipo_barril == prod.cantidad && b.id_recetas == prod.id_recetas);
          if(barriles_cerveza.length == 0) {
            alert('No hay barriles compatibles con el pedido.');
            return false;
          }
          barriles_cerveza.forEach(function(barril) {
              html += '<option value="' + barril.id + '">' + barril.codigo + '</option>';
          });

          $('#agregar-barriles-codigo-select').html(html);
          $('#agregar-barriles-modal').modal('show');

          if(html == '') {
              $('#confirmBtn').attr('DISABLED',true);
          } else {
              $('#confirmBtn').attr('DISABLED',false);
          }

        }


        function orderBarrilesEnPlanta() {
          barriles_enplanta_cerveza.sort((a, b) => {
            const codigoA = parseInt(a.codigo.replace(/[^0-9]/g, ''), 10);
            const codigoB = parseInt(b.codigo.replace(/[^0-9]/g, ''), 10);

            const letraA = a.codigo.replace(/[0-9\-]/g, '');
            const letraB = b.codigo.replace(/[0-9\-]/g, '');

            if (letraA === letraB) {
                return codigoA - codigoB;
            } else {
                return letraA.localeCompare(letraB);
            }
          });
        }

        $(document).on('click','#export',function(e){
          e.preventDefault();
          $('.repartos-dragger-container .dragger-container').each(function(index, element) {
              $(this).find('.dragger-card').each(function(dragger_card_index,dragger_card) {
              })
          });
        });



        const repartidores = <?= json_encode($repartidores,JSON_PRETTY_PRINT); ?>;
        function getDraggeCards() {
          var data = {
            'data': [],
            'repartidores': []
          };
          repartidores.forEach((repartidor) => {
            data.repartidores[repartidor.id] = false;
          })
          const draggerContainers = document.querySelectorAll(".dragger-container");
          var prods = [];
          
          draggerContainers.forEach(container => {
            if(container.getAttribute('data-id-usuarios-repartidor') !== null) {
              const cards = container.querySelectorAll(".dragger-card");
              cards.forEach((card) => {
                prods.push({
                  id_barriles: $(card).data('id-barriles'),
                  id_pedidos_productos: $(card).data('id-pedidos-productos'),
                  id_usuarios_repartidor: container.getAttribute('data-id-usuarios-repartidor')
                });
                data.repartidores[container.getAttribute('data-id-usuarios-repartidor')] = true;
              });
            }
          });
          data.data = prods;
          console.log(data);
          const url = './ajax/ajax_guardarDespachos.php';
          $.post(url,data,function(response){
            console.log(response);
            return false;
            if(response.mensaje!="OK") {
              alert("Algo fallo");
              return false;
            } else {
              window.location.href = "./?s=nuevo-despachos&msg=1";
            }
          }).fail(function(){
            alert("No funciono");
          });

        }





$(document).on('click','#llenar-barriles-btn',function(e){
    renderLlenarBarrilesDisponibles();
    renderLlenarBarrilesFermentadores();
});

$(document).on('change','#llenar-barriles_id_barriles-select',renderLlenarBarrilesDisponibles);
function renderLlenarBarrilesDisponibles() {
    const barril = barriles_disponibles.find((b) => b.id == $('#llenar-barriles_id_barriles-select').val());
    $('#llenar-barriles-barril-litros-cargados').val(barril.litros_cargados);
}

$(document).on('change','#llenar-barriles_id_batches_activos-select',renderLlenarBarrilesFermentadores);
function renderLlenarBarrilesFermentadores() {
    const bam = batches_activos_maduracion.find((b) => b.id == $('#llenar-barriles_id_batches_activos-select').val());
    $('#llenar-barriles-fermentador-cantidad-disponible').val(bam.litraje);
    console.log(bam);
}

$(document).on('click','#llenar-barril-aceptar-btn',function(e){

    e.preventDefault();

    var data = {
        'id_batches_activos': $('#llenar-barriles_id_batches_activos-select').val(),
        'id_barriles': $('#llenar-barriles_id_barriles-select').val(),
        'cantidad_a_cargar': $('#llenar-barriles-cantidad-a-cargar').val()
    };

    console.log(data);

    var url = './ajax/ajax_llenarBarriles.php';
    $.post(url,data,function(response){
        console.log(response);
        response = JSON.parse(response);
        window.location.href = './?s=inventario-de-productos&msg=1&msg_content=' + response.msg_content;
    });

});

</script>

  <!--<button id="export" class="btn btn-primary">
    Export
  </button>-->

  <div class="modal" tabindex="-1" role="dialog" id="agregar-barriles-modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Agregar Barril</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-6 mb-3">
              C&oacute;digo
            </div>
            <div class="col-6 mb-3">
              <select class="form-control" id="agregar-barriles-codigo-select">
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="cancelBtn" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-primary" id="confirmBtn">Aceptar</button>
        </div>
      </div>
    </div>
  </div>



  <button class="btn btn-primary" id="export">
    Exportar
  </button>