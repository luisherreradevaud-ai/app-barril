<?php

$usuario = $GLOBALS['usuario'];

?>
<style>
.tr-barriles {
  cursor: pointer;
}
.tr-batches {
  cursor: pointer;
}
.tr-entregas {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800">
      <b>
        <i class="fas fa-fw fa-plus"></i> Nuevo Barril CO2
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
  Msg::show(1,'Barril guardado con &eacute;xito','primary');
?>
<form id="barriles-form">
  <input type="hidden" name="id" value="">
  <input type="hidden" name="entidad" value="barriles">
  <input type="hidden" name="clasificacion" value="CO2">
  <div class="row">
    <div class="col-md-6 row">
      <div class="col-6 mb-2">
        C&oacute;digo de barril:
      </div>
      <div class="col-6 mb-2">
        <input type="text" class="form-control" name="codigo">
      </div>
      <div class="col-6 mb-2">
        Litraje:
      </div>
      <div class="col-6 mb-2">
        <select class="form-control" name="tipo_barril">
            <option>7L</option>
            <option>9L</option>
            <option>25L</option>
        </select>
      </div>
      <div class="col-6 mb-2">
        Estado:
      </div>
      <div class="col-6 mb-2">
        <select class="form-control" name="estado">
            <option>En planta</option>
            <option>Perdido</option>
        </select>
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

  if($('input[name="codigo"]').val().length < 1) {
    alert("El codigo de barril debe tener al menos 1 caracter.");
    return false;
  }

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("barriles");

  $.post(url,data,function(response){
    console.log(response);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-barriles-co2&id=" + response.obj.id + "&msg=1";
    }
  },'json').fail(function(){
    alert("No funciono");
  });
});

$(document).on('click','#guardar-y-agregar-btn',function(e){

  e.preventDefault();

  if($('input[name="codigo"]').val().length < 1) {
    alert("El codigo de barril debe tener al menos 1 caracter.");
    return false;
  }

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("barriles");

  $.post(url,data,function(response){
    console.log(response);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=nuevo-barriles-co2&msg=1";
    }
  },'json').fail(function(){
    alert("No funciono");
  });
});


</script>
