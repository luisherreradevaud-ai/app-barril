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
      <i class="fas fa-fw fa-plus"></i> Nueva Receta
      </b>
    </h1>
  </div>
      <?php $usuario->printReturnBtn(); ?>
</div>
<hr />
<?php 
  Msg::show(1,'Receta guardada con &eacute;xito','primary');
?>
<form id="recetas-form">
  <input type="hidden" name="entidad" value="recetas">
  <input type="hidden" name="id" value="">
  <div class="row">
    <div class="col-md-8 row">
      <div class="col-6 mb-1">
        Nombre:
      </div>
      <div class="col-6 mb-1">
        <input type="text" value="" class="form-control" name="nombre">
      </div>
      <div class="col-6 mb-1">
        C&oacute;digo:
      </div>
      <div class="col-6 mb-1">
        <input type="text" value="" class="form-control" name="codigo">
      </div>
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
        Cantidad de Litros:
      </div>
      <div class="col-6 mb-1">
        <div class="input-group">
          <input type="text" class="form-control acero" name="litros" value="0">
          <span class="input-group-text" id="basic-addon1" style="border-radius: 0px 10px 10px 0px">L</span>
        </div>
      </div>
      <div class="col-12 mb-1">
        Observaciones:
      </div>
      <div class="col-12 mb-1">
        <textarea name="observaciones" class="form-control"></textarea>
      </div>
      <div class="col-12">
        <table class="table table-striped mt-4" id="insumos-table">
        </table>
      </div>
      <div class="col-12 mt-3 mb-1 d-flex justify-content-end">
        <!--<button class="btn btn-sm btn-primary" id="agregar-insumos-btn"><i class="fas fa-fw fa-plus"></i> Agregar Insumos</button>-->
        <button class="btn btn-sm btn-primary" id="guardar-recetas-aceptar"><i class="fas fa-fw fa-save"></i> Guardar</button>
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
            Cantidad:
          </div>
          <div class="col-6 mb-1">
            <div class="input-group">
              <input type="number" class="form-control acero-float" name="cantidad" value="0">
              <span class="input-group-text" style="border-radius: 0px 10px 10px 0px" id="agregar-insumos-unidad-de-medida">ml</span>
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


var tipos_de_insumos = <?= json_encode($tipos_de_insumos,JSON_PRETTY_PRINT); ?>;
var tipo_de_insumo = {};
var insumos = [];
var lista = [];
var insumo = {};

$(function() {
  armarTiposDeInsumosSelect();
  changeTiposDeInsumosSelect();
  changeInsumosSelect();
});


$(document).on('change','select[name="id_tipos_de_insumos"]',function(){
  changeTiposDeInsumosSelect();
  changeInsumosSelect();
});

function armarTiposDeInsumosSelect() {

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
  insumos.forEach(function(insumo) {
    $('select[name="id_insumos"]').append("<option value='" + insumo.id + "'>" + insumo.nombre + "</option>");
  });
  
}

function changeInsumosSelect() {

  var id_insumos = $('select[name="id_insumos"]').val();
  if(id_insumos == null) {
    return false;
  }

  insumo = insumos.find((i) => i.id == id_insumos);
  $('#agregar-insumos-unidad-de-medida').html(insumo.unidad_de_medida);
  $('input[name="cantidad"]').val('0');
  $('input[name="monto"]').val('0');

}



$(document).on('change','select[name="id_tipos_de_insumos"]',function(){
    changeTiposDeInsumosSelect();
    changeInsumosSelect();
});

$(document).on('change','select[name="id_insumos"]',function(){
    changeInsumosSelect();
});


$(document).on('click','#guardar-recetas-aceptar',function(e){

  e.preventDefault();

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("recetas");
  data['insumos'] = lista;

  $.post(url,data,function(response_raw){
    console.log(response_raw);
    var response = JSON.parse(response_raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-recetas&id=" + response.obj.id + "&msg=1";
    }
  }).fail(function(){
    alert("No funciono");
  });
});

$(document).on('click','#guardar-recetas-y-agregar-aceptar',function(e){

  e.preventDefault();

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("recetas");
  data['insumos'] = lista;

  $.post(url,data,function(response_raw){
    console.log(response_raw);
    var response = JSON.parse(response_raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=nuevo-recetas&msg=1";
    }
  }).fail(function(){
    alert("No funciono");
  });
});


$(document).on('keyup','.acero',function(){
  $(this).val($(this).val().replace(/\D/g,''));
  if($(this).val() == "") {
    $(this).val(0);
  }
  $(this).val(parseInt($(this).val()));
});

/*
$(document).on('click','#agregar-insumos-btn',function(e){
  e.preventDefault();
  $('#agregar-insumos-modal').modal('toggle');
});

$(document).on('click','#agregar-insumos-aceptar',function(e){

  e.preventDefault();

  var id_insumos = $('select[name="id_insumos"]').val();
  console.log("id_insumos: " + id_insumos);
  insumo = insumos.find((ins) => ins.id == id_insumos);
  console.log(insumos);
  console.log("insumo");
  console.log(insumo);
  insumo.cantidad = $('input[name="cantidad"').val();

  lista.push(insumo);
  console.log("lista");
  console.log(lista);
  renderLista();
});

function renderLista() {
  var html = '';
  lista.forEach(function(ins,index){
    html += '<tr class="insumos-tr" data-index="' + index +'"><td><b>' + ins.nombre;
    html += '</b></td><td><b>' + ins.cantidad + " " + ins.unidad_de_medida;
    html += '</b></td><td><b><button class="btn btn-sm item-eliminar-btn" data-index="' + index + '">x</button>';
    html += '</b></td></tr>';
  });
  $('#insumos-table').html(html);
  if(lista.length == 0) {
    $('#guardar-recetas-aceptar').attr('disabled',true);
  } else {
    $('#guardar-recetas-aceptar').attr('disabled',false);
  }
}

$(document).on('click','.item-eliminar-btn',function(e){
  e.preventDefault();
  var index = $(e.currentTarget).data('index');
  lista.splice(index,1);
  renderLista();
});

*/

</script>
