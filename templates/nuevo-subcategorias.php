<?php

  $categorias = Categoria::getAll();

?>
<style>
.tr-subcategorias {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-plus"></i> <b>Nueva SubCategoria</b></h1>
  </div>
  <div>
    <div>
      <a href="./?s=subcategorias" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm mb-2"><i class="fas fa-fw fa-backward"></i> Volver a SubCategorias</a>
    </div>
  </div>
</div>
<hr />
<form id="subcategorias-form">
  <input type="hidden" name="id" value="">
  <input type="hidden" name="modo" value="subcategorias">
  <div class="row">
    <div class="col-md-6 row">
      <div class="col-6 mb-1">
        Nombre:
      </div>
      <div class="col-6 mb-1">
        <input type="text" name="nombre" class="form-control">
      </div>
      <div class="col-6 mb-1">
        Categoria:
      </div>
      <div class="col-6 mb-1">
        <select name="id_categorias" class="form-control">
          <?php
            foreach($categorias as $key=>$categoria) {
              print "<option value='".$categoria->id."'>";
              print $categoria->nombre;
              print "</option>";
            }
          ?>
        </select>
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

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("subcategorias");

  $.post(url,data,function(response){
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-subcategorias&id=" + response.obj.id;
    }
  },"json").fail(function(){
    alert("No funciono");
  });
});
</script>
