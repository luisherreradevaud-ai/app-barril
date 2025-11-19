<?php

    $proveedores = Proveedor::getAll();
    $tipos_de_insumos = TipoDeInsumo::getAll();
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
        Nueva Compra de Insumos
      </b>
    </h1>
  </div>
  <div>
    <div>
      <?php $usuario->printReturnBtn(); ?>
    </div>
  </div>
</div>
<hr />
<?php 
  Msg::show(1,'Compra de Insumos guardada con &eacute;xito','primary');
?>
<form id="insumos-form">
  <input type="hidden" name="id_usuarios" value="<?= $GLOBALS['usuario']->id; ?>">
  <div class="row">
    <div class="col-md-8 row">
    <div class="col-6 mb-1">
        Fecha:
      </div>
      <div class="col-6 mb-1">
        <input type="date" value="<?= date('Y-m-d'); ?>" class="form-control" name="date">
      </div>
      <div class="col-6 mb-1">
        Proveedor:
      </div>
      <div class="col-6 mb-1">
        <select name="id_proveedores" class="form-control">
            <?php
            foreach($proveedores as $proveedor) {
                print "<option value='".$proveedor->id."'>".$proveedor->nombre."</option>";
            }
            ?>
        </select>
      </div>
      <div class="col-6 mb-1">
        Factura:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" name="factura">
      </div>
      <div class="col-6 mb-1">
        Estado:
      </div>
      <div class="col-6 mb-1">
        <select name="estado" class="form-control">
            <option>Pagado</option>
            <option>Por Pagar</option>
        </select>
      </div>
      <div class="col-12 mb-1">
        Comentarios:
      </div>
      <div class="col-12 mb-1">
        <textarea name="comentarios" class="form-control"></textarea>
      </div>
      <div class="col-12">
        <table class="table table-striped mt-4" id="insumos-table">
        </table>
      </div>
      <div class="col-12 mt-3 mb-1 d-flex justify-content-between">
        <button class="btn btn-sm btn-primary" id="agregar-insumos-btn"><i class="fas fa-fw fa-plus"></i> Agregar Insumos</button>
        <div>
          <button class="btn btn-sm btn-primary guardar-btn" id="guardar-insumos-aceptar" disabled="true"><i class="fas fa-fw fa-save"></i> Guardar</button>
          <button class="btn btn-sm btn-primary guardar-btn" id="guardar-insumos-y-agregar-aceptar" disabled="true"><i class="fas fa-fw fa-save"></i> Guardar y agregar Nueva</button>
        </div>
      </div>
    </div>
  </div>
  </form>

<div class="modal modal-fade" tabindex="-1" role="dialog" id="agregar-insumos-modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Agregar Insumo</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
          <div class="col-6 mb-1">
            Tipo de Insumo:
          </div>
          <div class="col-6 mb-1">
            <select name="id_tipos_de_insumos" class="form-control">
            </select>
          </div>
          <div class="col-6 mb-1">
            Insumo:
          </div>
          <div class="col-6 mb-1">
            <select name="id_insumos" class="form-control">
            </select>
          </div>
          <div class="col-6 mb-1">
            Ingresar a:
          </div>
          <div class="col-6 mb-1">
            <select name="ingresar_a" class="form-control">
              <option>Bodega</option>
              <option>Despacho</option>
            </select>
          </div>
          <div class="col-6 mb-1">
            Cantidad:
          </div>
          <div class="col-6 mb-1">
            <div class="input-group">
              <input type="number" class="form-control acero-float" name="cantidad" value="0">
              <span class="input-group-text" style="border-radius: 0px 10px 10px 0px" id="agregar-insumos-unidad-de-medida">ml</span>
            </div>
          </div>
          <div class="col-6 mb-1">
            Monto:
          </div>
          <div class="col-6 mb-1">
            <div class="input-group">
              <span class="input-group-text" style="border-radius: 10px 0px 0px 10px">$</span>
              <input type="number" class="form-control acero" name="monto" value="0">
            </div>
          </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="agregar-insumos-aceptar" data-bs-dismiss="modal">Agregar</button>
        </div>
      </div>
    </div>
  </div>


<script>

var proveedores = <?= json_encode($proveedores,JSON_PRETTY_PRINT); ?>;
var proveedor = {};
var tipos_de_insumos = [];
var tipo_de_insumo = {};
var insumos = [];
var lista = [];
var insumo = {};

$(function() {
  changeProveedoresSelect();
  changeTiposDeInsumosSelect();
  changeInsumosSelect();
});

$(document).on('change','select[name="id_proveedores"]',function(){
  changeProveedoresSelect();
  changeTiposDeInsumosSelect();
  changeInsumosSelect();
});

$(document).on('change','select[name="id_tipos_de_insumos"]',function(){
  changeTiposDeInsumosSelect();
  changeInsumosSelect();
});

function changeProveedoresSelect() {

  $('select[name="id_tipos_de_insumos"]').empty();

  var id_proveedores = $('select[name="id_proveedores"]').val();
  if(id_proveedores == null) {
    return false;
  }

  proveedor = proveedores.find((proveedor) => proveedor.id == id_proveedores);
  tipos_de_insumos = proveedor.tipos_de_insumos;
  tipos_de_insumos.forEach(function(tdi) {
    $('select[name="id_tipos_de_insumos"]').append("<option value='" + tdi.id + "'>" + tdi.nombre + "</option>");
  });

}

function changeTiposDeInsumosSelect() {

  $('select[name="id_insumos"]').empty();

  var id_tipos_de_insumos = $('select[name="id_tipos_de_insumos"]').val();
  if(id_tipos_de_insumos == null) {
    return false;
  }

  tipo_de_insumo = tipos_de_insumos.find((tdi) => tdi.id == id_tipos_de_insumos);
  insumos = tipo_de_insumo.insumos;
  console.log(insumos);
  insumos.forEach(function(insumo) {
    $('select[name="id_insumos"]').append("<option value='" + insumo.id + "'>" + insumo.nombre + "</option>");
  });

  //console.log($('select[name="id_insumos"]')[0]);
  
}

function changeInsumosSelect() {

  var id_insumos = $('select[name="id_insumos"]').val();
  if(id_insumos == null) {
    return false;
  }

  var insumo = insumos.find((i) => i.id == id_insumos);
  $('#agregar-insumos-unidad-de-medida').html(insumo.unidad_de_medida);

  $('input[name="cantidad"]').val('0');
  $('input[name="monto"]').val('0');

}


$(document).on('change','select[name="id_proveedores"]',function(){
    changeProveedoresSelect();
    changeTiposDeInsumosSelect();
    changeInsumosSelect();
});

$(document).on('change','select[name="id_tipos_de_insumos"]',function(){
    changeTiposDeInsumosSelect();
    changeInsumosSelect();
});

$(document).on('change','select[name="id_insumos"]',function(){
    changeInsumosSelect();
});


$(document).on('click','#guardar-insumos-aceptar',function(e){

  e.preventDefault();

  var url = "./ajax/ajax_guardarComprasDeInsumos.php";
  var data = getDataForm("insumos");
  data['insumos'] = lista;

  $.post(url,data,function(response_raw){
    console.log(response_raw);
    var response = JSON.parse(response_raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-compras_de_insumos&id=" + response.obj.id + "&msg=1";
    }
  }).fail(function(){
    alert("No funciono");
  });
});

$(document).on('click','#guardar-insumos-y-agregar-aceptar',function(e){

  e.preventDefault();

  var url = "./ajax/ajax_guardarComprasDeInsumos.php";
  var data = getDataForm("insumos");
  data['insumos'] = lista;

  $.post(url,data,function(response_raw){
    console.log(response_raw);
    var response = JSON.parse(response_raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=nuevo-compras_de_insumos&msg=1";
    }
  }).fail(function(){
    alert("No funciono");
  });
});


$(document).on('click','#agregar-insumos-btn',function(e){
  e.preventDefault();
  $('input[name="monto"]').val(0);
  $('input[name="cantidad"]').val(0);
  $('#agregar-insumos-modal').modal('toggle');
});

$(document).on('click','#agregar-insumos-aceptar',function(e){

  e.preventDefault();

  var id_insumos = $('select[name="id_insumos"] :selected').val();
  console.log(id_insumos);
  var insumo = insumos.find((ins) => ins.id == id_insumos);
  console.log(insumo);

  insumo.id_proveedores = proveedor.id;
  insumo.monto = $('input[name="monto"').val();
  insumo.cantidad = $('input[name="cantidad"').val();
  insumo.ingresar_a = $('input[name="ingresar_a"').val();

  lista.push(insumo);
  console.log(lista);
  renderLista();
});

function renderLista() {
  var html = '';
  lista.forEach(function(ins,index){
    html += '<tr class="insumos-tr" data-index="' + index +'"><td><b>' + ins.nombre;
    html += '</b></td><td><b>' + proveedor.nombre;
    html += '</b></td><td><b>' + ins.cantidad + " " + ins.unidad_de_medida;
    html += '</b></td><td><b>$' + parseInt(ins.monto).toLocaleString('en-US');
    html += '</b></td><td><b><button class="btn btn-sm item-eliminar-btn" data-index="' + index + '">x</button>';
    html += '</b></td></tr>';
  });
  $('#insumos-table').html(html);
  if(lista.length == 0) {
    $('select[name="id_proveedores"]').attr('disabled',false);
    $('.guardar-btn').attr('disabled',true);
  } else {
    $('select[name="id_proveedores"]').attr('disabled',true);
    $('.guardar-btn').attr('disabled',false);
  }
}

$(document).on('click','.item-eliminar-btn',function(e){
  e.preventDefault();
  var index = $(e.currentTarget).data('index');
  lista.splice(index,1);
  renderLista();
});

</script>
