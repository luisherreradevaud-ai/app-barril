<?php

  $usuario = $GLOBALS['usuario'];

  if($usuario->nivel == "Administrador" || $usuario->nivel == "Jefe de Planta") {
    $activos = Activo::getAll();
  } else {
    $activos = Activo::getAll("WHERE id_usuarios_control='".$usuario->id."' ORDER BY id asc");
  }

  $id_activos = 0;
  if(validaIdExists($_GET,'id_activos')){
      $id_activos = $_GET['id_activos'];
  }

  $tarea = "";
  if(isset($_GET['tarea'])){
      $tarea = $_GET['tarea'];
  }

  if($usuario->nivel == "Administrador" || $usuario->nivel == "Jefe de Planta") {
    $ejecutores = Usuario::getAll("WHERE nivel!='Cliente' AND estado='Activo'");
  } else {
    $ejecutores[] = $usuario;
  }

?>

<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-plus"></i> <b>Nueva Mantenci&oacute;n</b></h1>
  </div>
  <div>
      <?php $usuario->printReturnBtn(); ?>
    </div>
  
</div>
<hr />

<!-- ///////////////////////////////////////////////////////////////////// -->


  <div class="row">
    <form id="mantenciones-form" class="col-md-8 row">
      <input type="hidden" name="id" value="">
      <input type="hidden" name="entidad" value="mantenciones">
      <input type="hidden" name="id_clientes_ubicacion" value="">
      <div class="col-6 mb-1">
        Activo:
      </div>
      <div class="col-6 mb-1">
        <select class="form-control" name="id_activos">
            <?php
            foreach($activos as $activo) {
                print "<option value='".$activo->id."'";
                if($activo->id == $id_activos) {
                    print " selected";
                }
                print ">".$activo->nombre." ".$activo->marca." ".$activo->modelo."</option>";
            }
            ?>
        </select>       
      </div>
      <div class="col-6 mb-1">
        Tarea:
      </div>
      <div class="col-6 mb-1">
        <select class="form-control" name="tarea">
            <option<?= $tarea == "inspeccion" ? " selected" : ""; ?>>Inspeccion</option>
            <option<?= $tarea == "mantencion" ? " selected" : ""; ?>>Mantencion</option>
        </select>       
      </div>
      <div class="col-6 mb-1">
        Ubicaci&oacute;n:
      </div>
      <div class="col-6 mb-1">
        <input type="text" name="ubicacion" value="" class="form-control" readonly>
      </div>
      <div class="col-6 mb-1">
        Fecha:
      </div>
      <div class="col-6 mb-1">
        <input type="date" name="date" value="<?= date('Y-m-d'); ?>" class="form-control">
      </div>
      <div class="col-6 mb-1">
        Hora de Inicio:
      </div>
      <div class="col-6 mb-1">
        <input type="time" name="hora_inicio" value="<?= date('H:i'); ?>" class="form-control">
      </div>
      <div class="col-6 mb-1">
        Ejecutor:
      </div>
      <div class="col-6 mb-1">
        <select name="ejecutor"  class="form-control">
        <?php
        foreach($ejecutores as $ejecutor) {
          print "<option value='".$ejecutor->id."'>".$ejecutor->nombre."</option>";
        }
        ?>
        </select>
      </div>
      <div class="col-12 mb-1 mt-2 text-right">
        <button class="btn btn-primary btn-sm" id="guardar-btn"><i class="fas fa-fw fa-save"></i> Iniciar Mantenci&oacute;n</button>
      </div>
    </form>
  </div>

<script>

var id_activos = '<?= $id_activos; ?>';

$(document).ready(function(){

  if(id_activos != '0') {
    $('select[name="id_activos"]').val(id_activos);
  }

  var url = "./ajax/ajax_getActivo.php";
  var data = {
    'id_activos': $('select[name="id_activos"]').val()
  };

  console.log(data);

  $.get(url,data,function(raw){
    var response = JSON.parse(raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      $('input[name="id_clientes_ubicacion"]').val(response.id_clientes_ubicacion);
      $('input[name="ubicacion"]').val(response.ubicacion);
    }
  }).fail(function(){
    alert("No funciono");
  });
  
});


$(document).on('click','#guardar-btn',function(e){

  e.preventDefault();

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("mantenciones");

  $.post(url,data,function(raw){
    var response = JSON.parse(raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-mantenciones&id=" + response.obj.id + "&msg=1";
    }
  }).fail(function(){
    alert("No funciono");
  });
});


$(document).on('change','select[name="id_activos"]',function(e){

  e.preventDefault();

  var url = "./ajax/ajax_getActivo.php";
  var data = {
    'id_activos': $(e.currentTarget).val()
  };

  $.get(url,data,function(raw){
    var response = JSON.parse(raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      $('input[name="id_clientes_ubicacion"]').val(response.id_clientes_ubicacion);
      $('input[name="ubicacion"]').val(response.ubicacion);
    }
  }).fail(function(){
    alert("No funciono");
  });
});

</script>
