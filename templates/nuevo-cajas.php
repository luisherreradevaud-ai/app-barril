<?php



$tipos_caja = $GLOBALS['tipos_caja'];
$productos = Producto::getAll("WHERE tipo='Caja' ORDER BY clasificacion asc, cantidad asc");
$clasificaciones = array();
foreach($productos as $p) {
  if(!in_array($p->clasificacion,$clasificaciones)) {
    $clasificaciones[] = $p->clasificacion;
  }
}
$usuario = $GLOBALS['usuario'];


?>
<style>
.tr-cajas {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800">
      <b>
        Nueva Caja
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
  Msg::show(1,'Caja guardada con &eacute;xito','primary');
?>
<form id="cajas-form">
  <input type="hidden" name="id" value="">
  <input type="hidden" name="entidad" value="cajas">
  <div class="row">
    <div class="col-md-6 row">
    <div class="col-6 mb-1">
        Clasificaci&oacute;n:
      </div>
      <div class="col-6 mb-1">
        <select class="form-control" name="clasificacion">
        </select>
      </div>
      <div class="col-6 mb-1">
        Tipo de Caja:
      </div>
      <div class="col-6 mb-1">
        <select name="cantidad" class="form-control">
        </select>
      </div>
      <div class="col-6 mb-1">
        Producto:
      </div>
      <div class="col-6 mb-1">
        <select name="id_productos" class="form-control">
        </select>
      </div>
      <div class="col-6 mb-1">
        C&oacute;digo de caja:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" name="codigo">
      </div>
      <div class="col-6 mb-1">
        Estado:
      </div>
      <div class="col-6 mb-1">
        <select name="estado" class="form-control">
          <option>Por despachar</option>
          <option>Despachada</option>
        </select>
      </div>
      <div class="col-12 mt-3 mb-1 d-flex justify-content-between">
        &nbsp;
        <div>
          <button class="btn btn-sm btn-primary" id="guardar-btn"><i class="fas fa-fw fa-save"></i> Guardar</button>
          <button class="btn btn-sm btn-primary" id="guardar-y-agregar-btn"><i class="fas fa-fw fa-save"></i> Guardar y agregar Nueva</button>
        </div>
      </div>
    </div>
  </div>
</form>



<script>

var productos = <?= json_encode($productos,JSON_PRETTY_PRINT); ?>;
var clasificaciones = <?= json_encode($clasificaciones,JSON_PRETTY_PRINT); ?>;

$(document).ready(function(){

  armarClasificacionSelect();
  armarCantidadSelect();
  armarProductosSelect();

  $.each(obj,function(key,value){
    if(key!="table_name"&&key!="table_fields"){
      $('input[name="'+key+'"]').val(value);
      $('textarea[name="'+key+'"]').val(value);
      $('select[name="'+key+'"]').val(value);
    }
  });

});

$(document).on('click','#guardar-btn',function(e){

  e.preventDefault();

  /*if($('input[name="nombre"]').val().length < 2) {
    alert("El nombre debe tener mas de 2 caracteres.");
    return false;
  }*/

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("cajas");

  $.post(url,data,function(response){
    console.log(response);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-cajas&id=" + response.obj.id;
    }
  },'json').fail(function(){
    alert("No funciono");
  });
});

$(document).on('click','#guardar-y-agregar-btn',function(e){

  e.preventDefault();

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("cajas");

  $.post(url,data,function(response){
    console.log(response);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=nuevo-cajas&msg=1";
    }
  },'json').fail(function(){
    alert("No funciono");
  });
});



$(document).on('click','.eliminar-obj-btn',function(){
  var data = {
    'id': obj.id,
    'modo': obj.table_name
  }
  var url = './ajax/ajax_eliminarEntidad.php';
  $.post(url,data,function(response){
    if(response.status!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=" + response.table_name;
    }
  },"json").fail(function(){
    alert("No funciono");
  });
});


function armarClasificacionSelect() {

  var html = '';

  clasificaciones.forEach(function(clasificacion) {
    html += '<option>' + clasificacion + '</option>';
  });

  $('select[name="clasificacion"]').html(html);
}

function armarCantidadSelect() {
  var clasificacion = $('select[name="clasificacion"]').val();
  var productos_filtered = productos.filter((p) => p.clasificacion == clasificacion);
  console.log(productos_filtered);
  var cantidades = [];
  var html = '';
  productos_filtered.forEach(function(p) {
    if(!cantidades.includes(p.cantidad)) {
      cantidades.push(p.cantidad);
      html += '<option>' + p.cantidad + '</option>';
    }
  });
  $('select[name="cantidad"]').html(html);
}

function armarProductosSelect() {
  var clasificacion = $('select[name="clasificacion"]').val();
  var cantidad = $('select[name="cantidad"]').val();
  var productos_filtered = productos.filter((p) => p.clasificacion == clasificacion && p.cantidad == cantidad);
  console.log(productos_filtered);
  var ids_productos = [];
  var html = '';
  productos_filtered.forEach(function(p) {
    if(!ids_productos.includes(p.cantidad)) {
      ids_productos.push(p.id);
      html += '<option value="' + p.id + '">' + p.nombre + '</option>';
    }
  });
  $('select[name="id_productos"]').html(html);
}

$(document).on('change','select[name="clasificacion"]',function(e) {
  armarCantidadSelect();
  armarProductosSelect();
});

$(document).on('change','select[name="cantidad"]',function(e) {
  armarProductosSelect();
});

</script>
