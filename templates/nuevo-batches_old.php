<?php

    $recetas['Cerveza'] = Receta::getAll('WHERE clasificacion="Cerveza"');
    $recetas['Kombucha'] = Receta::getAll('WHERE clasificacion="Kombucha"');
    $recetas['Agua saborizada'] = Receta::getAll('WHERE clasificacion="Agua saborizada"');
    $recetas['Agua fermentada'] = Receta::getAll('WHERE clasificacion="Agua fermentada"');
    $activos = Activo::getAll();
    $barriles = Barril::getAll("WHERE estado='En planta' AND (tipo_barril='30L' OR tipo_barril='50L')");
    $tipos_caja = $GLOBALS['tipos_caja'];
    $tipos_caja_cerveza = $GLOBALS['tipos_caja_cerveza'];
    $insumos = Insumo::getAll();
    $usuarios_ejecutores = Usuario::getAll("WHERE nivel='Jefe de Cocina' AND estado='Activo' ORDER BY nombre asc");
    $usuario = $GLOBALS['usuario'];


?>
<style>
.tr-gasto {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800">
      <b>
      <i class="fas fa-fw fa-plus"></i> Nuevo Batch
      </b>
    </h1>
  </div>
      <?php $usuario->printReturnBtn(); ?>
</div>
<hr />
<?php 
  Msg::show(1,'Batch guardado con &eacute;xito','primary');
?>
<div class="alert alert-info" role="alert" id="alert-insumos">Insumos insuficientes. Revise receta y disponibilidad en inventario.</div>
<form id="batches-form">
  <input type="hidden" name="entidad" value="batches">
  <input type="hidden" name="id" value="">
  <div class="row">
    <div class="col-md-8 row">
      <div class="col-6 mb-1">
        Clasificaci&oacute;n:
      </div>
      <div class="col-6 mb-1">
        <select class="form-control" name="clasificacion">
            <option>Cerveza</option>
            <option>Kombucha</option>
            <option>Agua saborizada</option>
            <option>Agua fermentada</option>
        </select>
      </div>
      <div class="col-6 mb-1">
        Receta:
      </div>
      <div class="col-6 mb-1">
        <select class="form-control" name="id_recetas">
        </select>
      </div>
      <div class="col-6 mb-1">
        Cantidad de Litros:
      </div>
      <div class="col-6 mb-1">
        <div class="input-group">
          <input type="text" class="form-control acero" name="litros" disabled>
          <span class="input-group-text" id="basic-addon1" style="border-radius: 0px 10px 10px 0px">L</span>
        </div>
      </div>
      <div class="col-6 mb-1">
        Fermentador:
      </div>
      <div class="col-6 mb-1">
        <select name="id_activos" class="form-control">
        </select>
      </div>
      <div class="col-6 mb-1">
        Fecha de Inicio:
      </div>
      <div class="col-6 mb-1">
        <input type="date" name="fecha_inicio" value="<?= date('Y-m-d'); ?>" class="form-control">
      </div>
      <div class="col-6 mb-1">
        Fecha de Termino:
      </div>
      <div class="col-6 mb-1">
        <input type="date" name="fecha_inicio" value="<?= date('Y-m-d'); ?>" class="form-control">
      </div>
      <div class="col-6 mb-1">
        Ejecutor:
      </div>
      <div class="col-6 mb-1">
        <select class="form-control" name="id_usuarios_ejecutor">
          <?php
            foreach($usuarios_ejecutores as $ue) {
              print "<option value='".$ue->id."'>".$ue->nombre."</option>";
            }
          ?>
        </select>
      </div>
      <div class="col-6 mb-1">
        Estado:
      </div>
      <div class="col-6 mb-1">
        <select name="estado" class="form-control">
          <option>Fermentacion</option>
          <option>Maduracion</option>
          <option>Embarrilado</option>
          <option>Embotellado</option>
        </select>
      </div>
      <div class="col-12 mb-1">
        Observaciones:
      </div>
      <div class="col-12 mb-1">
        <textarea name="observaciones" class="form-control"></textarea>
      </div>
      <div class="col-12 mt-1">
        <table class="table table-striped mt-4" id="pedidos-table">
        </table>
      </div>
      <div class="col-12 mt-3 mb-1 d-flex justify-content-between">
        <div>
            <button class="btn btn-sm btn-primary" id="agregar-barriles-btn"><i class="fas fa-fw fa-plus"></i> Agregar Barriles</button>
            <button class="btn btn-sm btn-primary" id="agregar-cajas-btn"><i class="fas fa-fw fa-plus"></i> Agregar Botellas</button>
        </div>
        <button class="btn btn-sm btn-primary" id="guardar-batches-aceptar"><i class="fas fa-fw fa-save"></i> Guardar</button>
        <button class="btn btn-sm btn-primary" id="guardar-batches-y-agregar-aceptar"><i class="fas fa-fw fa-save"></i> Guardar y agregar Nuevo</button>
      </div>
    </div>
  </div>
  </form>
  
  

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
                <?php
                foreach($tipos_caja as $tb) {
                  print "<option>".$tb."</option>";
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


var recetas = <?= json_encode($recetas,JSON_PRETTY_PRINT); ?>;
var recetas_p = [];
var lista = [];
var activos = <?= json_encode($activos,JSON_PRETTY_PRINT); ?>;
var productos = [];
var enplanta_30_l = <?= json_encode($barriles,JSON_PRETTY_PRINT); ?>;
var insumos = <?= json_encode($insumos,JSON_PRETTY_PRINT); ?>;

$(function() {
  armarRecetasSelect();
  armarActivosSelect();
});


$(document).on('change','select[name="clasificacion"]',function(){
    armarRecetasSelect();
});

$(document).on('change','select[name="id_recetas"]',function(){
    armarReceta();
});

function armarRecetasSelect() {

  recetas_p = recetas[$('select[name="clasificacion"]').val()];

  $('select[name="id_recetas"]').empty();
  recetas_p.forEach(function(r) {
    $('select[name="id_recetas"]').append("<option value='" + r.id + "'>" + r.nombre + "</option>");
  });

  armarReceta();

}

function armarActivosSelect() {

    activos.forEach(function(activo) {
      $('select[name="id_activos"]').append("<option value='" + activo.id + "'>" + activo.nombre + "</option>");
    });



}

function armarReceta() {

    $('input[name="litros"]').val('');
    $('input[name="observaciones"]').val('');

    var id_recetas = $('select[name="id_recetas"]').val();
    var receta = recetas_p.find((r) => id_recetas == r.id);
    $('input[name="litros"]').val(receta.litros);
    $('input[name="observaciones"]').val(receta.observaciones);

    var preparable = true;
    receta.insumos_arr.forEach(function(ri){
      var insumo = insumos.find((i => i.id == ri.id_insumos));
      if(parseInt(ri.cantidad) > parseInt(insumo.bodega)) {
        console.log("insumo insuficiente: " + insumo.nombre);
        console.log("cantidad necesaria: " + ri.cantidad);
        console.log("disponible: " + insumo.bodega);
        preparable = false;
      }
    });

    if(!preparable) {
      $('#alert-insumos').show();
      $('#guardar-batches-aceptar').attr('disabled',true);
    } else {
      $('#alert-insumos').hide();
      $('#guardar-batches-aceptar').attr('disabled',false);
    }
}

$(document).on('click','#guardar-batches-aceptar',function(e){

  e.preventDefault();

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("batches");
  data['productos'] = productos;

  $.post(url,data,function(response_raw){
    console.log(response_raw);
    var response = JSON.parse(response_raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-batches&id=" + response.obj.id + "&msg=1";
    }
  }).fail(function(){
    alert("No funciono");
  });
});

$(document).on('click','#guardar-batches-y-agregar-aceptar',function(e){

  e.preventDefault();

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("batches");
  data['productos'] = productos;

  $.post(url,data,function(response_raw){
    console.log(response_raw);
    var response = JSON.parse(response_raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=nuevo-batches&msg=1";
    }
  }).fail(function(){
    alert("No funciono");
  });
});

$(document).on('click','#agregar-barriles-aceptar',function() {
  var barril_agregar = enplanta_30_l.find((b) => b.id == $('#codigo_barril option:selected').val());
  productos.push({
    'tipo': 'Barril',
    'cantidad': barril_agregar.tipo_barril,
    'tipos_cerveza': '',
    'codigo': $('#codigo_barril option:selected').text(),
    'id_barriles': $('#codigo_barril').val()
  });
  renderTable();
});

$(document).on('click','#agregar-cajas-aceptar',function() {
  productos.push({
    'tipo': 'Caja',
    'cantidad': $('#tipos_caja_select').val(),
    'tipos_cerveza': '',
    'codigo': '',
    'id_barriles': ''
  });
  renderTable();
});

function renderTable() {
  var html = '';
  productos.forEach(function(producto,index){
    html += '<tr class="productos-tr" data-index="' + index +'">';
    html += '</td><td>#' + (index+1);
    html += '<td>' + producto.tipo;
    html += '</td><td>' + producto.cantidad;
        html += '</td><td>' + producto.codigo;
    html += '</td><td><a href="#" class="item-eliminar-btn" data-index="' + index + '">x</a>';
    html += '</td></tr>';
  });
  $('#pedidos-table').html(html);
}

$(document).on('click','.item-eliminar-btn',function(e){
  var index = $(e.currentTarget).data('index');
  productos.splice(index,1);
  renderTable();
});

$(document).on('click','#agregar-barriles-btn',function(e) {
  e.preventDefault();
  $('#barrilesModal').modal('toggle');
  var html = '';
    enplanta_30_l.forEach(function(barril) {
        html += '<option value="' + barril.id + '">' + barril.codigo + '</option>';
    });
  $('#codigo_barril').html(html);
});

$(document).on('click','#agregar-cajas-btn',function(e) {
    e.preventDefault();
  $('#cajasModal').modal('toggle');
});

$(document).on('click','#traspasar-btn',function(e) {
  e.preventDefault();
  $('#barrilesModal').modal('toggle');
  var html = '';
    productos.forEach(function(p) {
      console.log(p);
    });

});

</script>
