<?php

  if(!validaIdExists($_GET,'id_productos')) {
    include404();
  }

  $producto = new Producto($_GET['id_productos']);

  if($producto->nombre=="") {
    include404();
  }

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
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-plus"></i> <b>Nuevo SubProducto</b></h1>
    <h1 class="h4 mb-0 text-gray-800"><b><?= $marca->nombre." ".$producto->nombre; ?></b></h1>
  </div>
  <div>
    <div>
      <a href="./?s=detalle-productos&id=<?= $producto->id; ?>" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm mb-2"><i class="fas fa-fw fa-backward"></i> Volver a <?= $marca->nombre." ".$producto->nombre; ?></a>
    </div>
  </div>
</div>
<hr />
<form id="subproductos-form">
  <input type="hidden" name="id" value="">
  <input type="hidden" name="modo" value="subproductos">
  <input type="hidden" name="id_productos" value="<?= $producto->id; ?>">
  <div class="row">
    <div class="col-md-6 row">
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
          <input type="text" class="form-control" value="<?= $producto->monto; ?>" name="monto">
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
        <textarea name="descripcion" class="form-control"></textarea>
      </div>
      <div class="col-12 mb-1 text-right">
        <button class="btn btn-primary" id="guardar-btn">Guardar</button>
      </div>
    </div>
  </div>
</form>
<script>

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
  var data = getDataForm("subproductos");

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
</script>
