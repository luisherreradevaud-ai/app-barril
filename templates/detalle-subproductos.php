<?php

if(!validaIdExists($_GET,'id')) {
  die();
}

$obj = new SubProducto($_GET['id']);
$media_arr = $obj->getMedia();

$producto = new Producto($obj->id_productos);
$marca = new Marca($producto->id_marcas);
$categorias = Categoria::getAll();
$subcategorias = SubCategoria::getAll();
$marcas = Marca::getAll();

?>

<style>
.tr-productos {
  cursor: pointer;
}
</style>

<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-plus"></i> <b>Detalle SubProducto</b></h1>
    <h1 class="h4 mb-0 text-gray-800"><b><?= $marca->nombre." ".$producto->nombre; ?></b></h1>
  </div>
  <div>
    <div>
      <a href="./?s=detalle-productos&id=<?= $producto->id; ?>" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm mb-2"><i class="fas fa-fw fa-backward"></i> Volver a <?= $marca->nombre." ".$producto->nombre; ?></a>
    </div>
  </div>
</div>
<hr />

<!-- ///////////////////////////////////////////////////////////////////// -->


  <div class="row">
    <form id="productos-form" class="col-md-6 row">
      <input type="hidden" name="id" value="">
      <input type="hidden" name="modo" value="subproductos">
      <input type="hidden" name="id_productos" value="<?= $productos->id; ?>">
      <div class="col-6 mb-1">
        Nombre:
      </div>
      <div class="col-6 mb-1">
        <input type="text" name="nombre" class="form-control">
      </div>
      <div class="col-6 mb-1">
        Monto:
      </div>
      <div class="col-6 mb-1">
        <div class="input-group mr-sm-2">
          <div class="input-group-prepend">
            <div class="input-group-text">$</div>
          </div>
          <input type="text" class="form-control" value="0" name="monto">
        </div>
      </div>
      <div class="col-6 mb-1">
        Visible:
      </div>
      <div class="col-6 mb-1">
        <select name="visible" class="form-control">
          <option value="0">
            No
          </option>
          <option value="1">
            Si
          </option>
        </select>
      </div>

      <div class="col-12 mb-1">
        Descripcion:
      </div>
      <div class="col-12 mb-3">
        <textarea name="descripcion" class="form-control" placeholder="Max 600 caracteres"></textarea>
        <hr />
      </div>

      <div class="col-6 mb-4">
        <h2>Stock:</h2>
      </div>
      <div class="col-6 mb-4 text-right">
        <button class="btn" id="stock-menos">-</button>
        <input type="text" name="stock" id="stock-input" class="form-control" style="max-width: 70px; display:inline; text-align: center" value="<?= $obj->stock; ?>">
        <button class="btn" id="stock-mas">+</button>
      </div>
      <div class="col-12 mb-1 text-right">
        <hr />
        <button class="btn btn-primary" id="guardar-btn">Guardar</button>
      </div>
    </form>
    <div class="col-md-6">
    </div>
  </div>


<!-- ///////////////////////////////////////////////////////////////////// -->

<div class="row mt-5" id="media">
  <div class="col-xl-12 col-lg-12">
    <div class="card shadow mb-4">
      <!-- Card Header - Dropdown -->
      <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
          <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-fw fa-images"></i> Media asociada: <?= $obj->nombre; ?></h6>
      </div>
      <div class="card-body">
        <div class="row text-center text-lg-left">
          <?php
          foreach($media_arr as $key=>$media) {
            $media = new Media($media['id']);
            ?>
            <div class="col-lg-3 col-md-4 col-6">
              <a href="#" class="d-block mb-4 h-100" onclick="eliminarRelacionImagenDestino('<?= $media->id; ?>','<?= $media->url; ?>')">
                    <img class="img-fluid img-thumbnail" src="../media/images/<?= $media->url; ?>" width="300" alt="<?= $media->nombre; ?>">
                  </a>
            </div>
            <?php } ?>
          </div>
          <br />
          <div class="d-sm-flex align-items-center mb-4">
              <button class="d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#agregar-media-modal">+ Subir media</button>
          </div>
      </div>
    </div>
  </div>
</div>

<button class="btn btn-danger mt-4 mb-4 eliminar-obj-btn" style="float: right">Eliminar</button>

<div class="modal fade" id="agregar-media-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-fw fa-images"></i> Subir media</h5>
                <button class="close" type="button" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body" style="font-weight: bold">
            <form id="agregar-media-form" action="php/procesar.php" method="POST" enctype="multipart/form-data">
              <input type="hidden" name="modo" value="subir-media">
              <input type="hidden" name="entidad" value="<?= $obj->table_name; ?>">
              <input type="hidden" name="id_<?= $obj->table_name; ?>" value="<?= $obj->id; ?>">
            <div class="form-group">
              <label for="nombre-name" class="control-label">Nombre:</label>
              <input type="text" class="form-control" name="media_nombre" value="<?= $obj->nombre." ".(count($media_arr)+1); ?>">
            </div>
            <div class="form-group">
              <label for="descripcion-text" class="control-label">Descripci&oacute;n:</label>
              <textarea type="text" class="form-control" name="media_descripcion">Imagen de <?= $obj->nombre; ?></textarea>
            </div>
            <div class="form-group">
              <label for="archivo-text" class="control-label">Archivo:</label>
              <input type="file" class="form-control" name="file" accept="image/jpeg image/jpg">
            </div>
          </form></div>
            <div class="modal-footer">
                <button class="btn btn-default" type="button" data-bs-dismiss="modal">Cancelar</button>
                <a class="btn btn-primary btn-sm shadow-sm" href="#" onclick="document.getElementById('agregar-media-form').submit()">Subir</a>
            </div>
        </div>
    </div>
</div>

<script>



$(document).ready(function(){
  $.each(obj,function(key,value){
    if(key!="table_name"&&key!="table_fields"){
      $('input[name="'+key+'"]').val(value);
      $('textarea[name="'+key+'"]').val(value);
      $('select[name="'+key+'"]').val(value);
    }
  });
});

$(document).on('click','.tr-subproductos',function(e){
  window.location.href = "./?s=detalle-subproductos&id=" + $(e.currentTarget).data('idsubproductos');
});

$(document).on('click','#guardar-btn',function(e){

  e.preventDefault();

  if($('input[name="nombre"]').val().length < 2) {
    alert("El nombre debe tener mas de 2 caracteres.");
    return false;
  }

  if(!Number.isInteger(parseInt($('input[name="monto"]').val()))) {
    alert("El monto debe ser numerico y entero.");
    return false;
  }

  if(parseInt($('input[name="monto"]').val()) < 1) {
    alert("El monto debe ser mayor a cero.");
    return false;
  }

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("productos");


  $.post(url,data,function(response){
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-subproductos&id=" + response.obj.id;
    }
  },"json").fail(function(){
    alert("No funciono");
  });
});

var obj = <?= json_encode($obj,JSON_PRETTY_PRINT); ?>;
$(document).ready(function(){

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

$(document).on('click','#stock-menos',function(e){
  e.preventDefault();
  modificarStock(-1);
});

$(document).on('click','#stock-mas',function(e){
  e.preventDefault();
  modificarStock(1);
});

function modificarStock(cantidad) {
  var stock,suma;
  stock = parseInt($('#stock-input').val());
  cantidad = parseInt(cantidad);
  suma = stock + cantidad;
  if(suma<0) {
    return false;
  }
  $('#stock-input').val(suma);
}

var stock_prev;

$(document).on('keydown','#stock-input',function(e){
  stock_prev = $(this).val();
});

$(document).on('keyup','#stock-input',function(e){

  var stock_after = parseInt($(this).val());
  if(Number.isNaN(stock_after)) {
    stock_after = 0;
  }
  $(this).val(stock_after);
});

</script>
