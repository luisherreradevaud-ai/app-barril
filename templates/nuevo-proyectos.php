<?php

  $recetas['Cerveza'] = Receta::getAll('WHERE clasificacion="Cerveza"');
  $recetas['Kombucha'] = Receta::getAll('WHERE clasificacion="Kombucha"');
  $recetas['Agua saborizada'] = Receta::getAll('WHERE clasificacion="Agua saborizada"');
  $recetas['Agua fermentada'] = Receta::getAll('WHERE clasificacion="Agua fermentada"');
  $usuario = $GLOBALS['usuario'];


 ?>
<style>
.tr-productos {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-plus"></i> <b>Nuevo Proyecto</b></h1>
  </div>
  <div>
    <div>
      <?php $usuario->printReturnBtn(); ?>
    </div>
  </div>
</div>
<hr />
<?php 
  Msg::show(1,'Proyecto guardado con &eacute;xito','primary');
?>
<form id="proyectos-form">
  <input type="hidden" name="id" value="">
  <input type="hidden" name="entidad" value="proyectos">
  <div class="row">
    <div class="col-md-6">
    <div class="row">
    <div class="col-6 mb-1">
        Nombre:
      </div>
      <div class="col-6 mb-1">
        <input type="text" name="nombre" class="form-control">
      </div>
      <div class="col-6 mb-1">
        Clasificaci&oacute;n:
      </div>
      <div class="col-6 mb-1">
        <select class="form-control" name="clasificacion">
            <option>Feria</option>
            <option>Otro</option>
        </select>
      </div>
      <div class="col-6 mb-1">
        Estado:
      </div>
      <div class="col-6 mb-1">
        <select class="form-control" name="estado">
            <option>Activo</option>
            <option>Finalizado con pendientes</option>
            <option>Finalizado y cerrado</option>
        </select>
      </div>
      <div class="col-6 mb-1">
        Fecha de inicio:
      </div>
      <div class="col-6 mb-1">
        <input name="date_inicio" class="form-control" type="date">
      </div>
      <div class="col-6 mb-1">
        Fecha de finalizaci&oacute;n:
      </div>
      <div class="col-6 mb-1">
        <input name="date_finalizacion" class="form-control" type="date">
      </div>
      <div class="col-12 mb-1 mt-3 d-flex justify-content-between">
        <button class="btn btn-primary btn-sm" id="guardar-btn"><i class="fas fa-fw fa-save"></i> Guardar</button>
        <button class="btn btn-primary btn-sm" id="guardar-y-agregar-btn"><i class="fas fa-fw fa-save"></i> Guardar y agregar Nuevo</button>
        </div>
      </div>
    </div>
  </div>
</form>


<script>

$(document).on('click','#guardar-btn',function(e){

  e.preventDefault();

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("proyectos");

  console.log(data);

  if(data['nombre'] == '') {
    alert("Debe especificar un nombre para el Proyecto.");
    return false;
  }

  if(data['date_inicio'] == '') {
    alert("Debe especificar una fecha de inicio para el Proyecto.");
    return false;
  }

  if(data['date_finalizacion'] == '') {
    alert("Debe especificar una fecha de inicio para el Proyecto.");
    return false;
  }


  $.post(url,data,function(raw){
    console.log(raw);
    //return false;
    var response = JSON.parse(raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-proyectos&id=" + response.obj.id + "&msg=1";
    }
  }).fail(function(){
    alert("No funciono");
  });
});

$(document).on('click','#guardar-y-agregar-btn',function(e){

  e.preventDefault();

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("proyectos");

  console.log(data);

  if(data['nombre'] == '') {
    alert("Debe especificar un nombre para el Proyecto.");
    return false;
  }

  if(data['date_inicio'] == '') {
    alert("Debe especificar una fecha de inicio para el Proyecto.");
    return false;
  }

  if(data['date_finalizacion'] == '') {
    alert("Debe especificar una fecha de inicio para el Proyecto.");
    return false;
  }


  $.post(url,data,function(raw){
    console.log(raw);
    //return false;
    var response = JSON.parse(raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=nuevo-proyectos&msg=1";
    }
  }).fail(function(){
    alert("No funciono");
  });
});


</script>
