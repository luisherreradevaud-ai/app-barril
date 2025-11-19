<?php
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
        Nuevo Tipo de Insumo
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
  Msg::show(1,'Tipo de Insumo guardado con &eacute;xito','primary');
?>
<form id="tipos_de_insumos-form">
  <input type="hidden" name="id" value="">
  <input type="hidden" name="entidad" value="tipos_de_insumos">
  <div class="row">
    <div class="col-md-6 row">
      <div class="col-6 mb-1">
        Nombre:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" name="nombre">
      </div>
      <div class="col-6 mb-1">
        Visible:
      </div>
      <div class="col-6 mb-1">
        <select name="visible" class="form-control">
            <option value="1">Si</option>
            <option value="0">No</option>
        </select>
      </div>
      <div class="col-12 mb-1">
        Comentarios:
      </div>
      <div class="col-12 mb-1">
        <textarea name="comentarios" class="form-control"></textarea>
      </div>
      <div class="col-12 mt-3 mb-1 d-flex justify-content-between">
        &nbsp;
        <div>
          <button class="btn btn-sm btn-primary" id="guardar-btn"><i class="fas fa-fw fa-save"></i> Guardar</button>
          <button class="btn btn-sm btn-primary" id="guardar-y-agregar-btn"><i class="fas fa-fw fa-save"></i> Guardar y agregar Nuevo</button>
        </div>
      </div>
    </div>
  </div>
</form>

<script>

$(document).on('click','#guardar-btn',function(e){

  e.preventDefault();

  if($('input[name="nombre"]').val().length < 2) {
    alert("El nombre de tener mas de 2 caracteres.");
    return false;
  }

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("tipos_de_insumos");

  $.post(url,data,function(response){
    console.log(response);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-tipos_de_insumos&id=" + response.obj.id + "&msg=1";
    }
  },'json').fail(function(){
    alert("No funciono");
  });
});

$(document).on('click','#guardar-y-agregar-btn',function(e){

  e.preventDefault();

  if($('input[name="nombre"]').val().length < 2) {
    alert("El nombre de tener mas de 2 caracteres.");
    return false;
  }

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("tipos_de_insumos");

  $.post(url,data,function(response){
    console.log(response);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=nuevo-tipos_de_insumos&msg=1";
    }
  },'json').fail(function(){
    alert("No funciono");
  });
});

</script>
