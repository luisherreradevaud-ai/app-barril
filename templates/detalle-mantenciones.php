<?php

  //checkAutorizacion(["Jefe de Planta","Administrador","Jefe de Cocina","Repartidor"]);

  if(!validaIdExists($_GET,'id')) {
      die();
  }

  $obj = new Mantencion($_GET['id']);
  $activo = new Activo($obj->id_activos);
  $activo->getAccesorios();
  $media_arr = $obj->getMedia();
  $media_header = $obj->getMediaHeader();

  $activos = Activo::getAll();
  $usuario = $GLOBALS['usuario'];


  if($usuario->nivel == "Administrador" || $usuario->nivel == "Jefe de Planta") {
    $ejecutores = Usuario::getAll("WHERE nivel!='Cliente' AND estado='Activo'");
  } else {
    $ejecutores[] = $usuario;
  }

  $accesorios_renovados = $obj->getAccesoriosRenovados();

?>


<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800">Mantenci&oacute;n #<?= $obj->id; ?></b></h1>
  </div>
  <div>
      <?php $usuario->printReturnBtn(); ?>
    </div>
</div>
<hr />
<?php 
  Msg::show(1,'Mantenci&oacute;n creada con &eacute;xito','primary');
  Msg::show(2,'Media eliminada con &eacute;xito','danger');
  Msg::show(2,'Mantenci&oacute;n guardada con &eacute;xito','primary');
?>
<form id="mantenciones-form">
  <input type="hidden" name="id" value="">
  <input type="hidden" name="entidad" value="mantenciones">
  <input type="hidden" name="id_clientes_ubicacion" value="">

  <div class="row">
    <div class="col-6">
        <div class="card">
          <div class="card-body">
          <h4>Datos generales</h4>
            <div class="row mt-4">
              <div class="col-6 mb-1">
                Activo:
              </div>
              <div class="col-6 mb-1">
                <select class="form-control" name="id_activos">
                    <?php
                    foreach($activos as $activox) {
                        print "<option value='".$activox->id."'>".$activox->nombre." ".$activox->marca." ".$activox->modelo."</option>";
                    }
                    ?>
                </select>       
              </div>
              <div class="col-6 mb-1">
                Tarea:
              </div>
              <div class="col-6 mb-1">
                <select class="form-control" name="tarea">
                    <option>Inspeccion</option>
                    <option>Mantencion</option>
                </select>       
              </div>
              <div class="col-6 mb-1">
                Ubicaci&oacute;n:
              </div>
              <div class="col-6 mb-1">
                <input type="text" name="ubicacion" value="" class="form-control" READONLY>
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
                <input type="time" name="hora_inicio" value="" class="form-control">
              </div>
              <div class="col-6 mb-1">
                Hora de Termino:
              </div>
              <div class="col-6 mb-1">
                <input type="time" name="hora_termino" value="" class="form-control">
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
              <div class="col-12 mb-1">
                Observaciones:
              </div>
              <div class="col-12 mb-1">
                <textarea name="observaciones" class="form-control"></textarea>
              </div>
              <div class="col-12 mb-1 mt-2 d-flex justify-content-between">

              </div>
            </div>
        </div>
      </div>
    </div>
    <div class="col-6">
      <div class="card">
        <div class="card-body">
          <h4>Accesorios</h4>
          <table class="table table-sm table-stripped mt-4">
              <thead>
                <tr>
                  <th>
                    Renovar
                  </th>
                  <th>
                    Nombre 
                  </th>
                  <th>
                    Marca
                  </th>
                  <th>
                    Último cambio 
                  </th>
                </tr>
              </thead>
              <tbody>
                <?php
                foreach($activo->accesorios as $accesorio) {
                ?>
                <tr>
                  <td>
                    <input type="checkbox" class="form-check-input" name="accesorios_renovados[<?= $accesorio->id; ?>]" value="<?= $accesorio->id; ?>" <?= (in_array($accesorio->id,$accesorios_renovados)) ? ' CHECKED DISABLED' : ''; ?>
                  </td>
                  <td>
                    <?= $accesorio->nombre; ?>
                  </td>
                  <td>
                    <?= $accesorio->marca; ?>
                  </td>
                  <td>
                    <?= $accesorio->ultimo_cambio; ?>
                  </td>
                </tr>
                <?php
                }
                ?>
              </tbody>
            </table>
            <button class="btn btn-outline-primary btn-sm mt-3" id="nuevo-accesorio-abrir-modal-btn">
              <i class="fas fa-plus"></i> Nuevo Accesorio
            </button>
        </div>
      </div>
    </div>
  </div>

</form>


  <div class="row">
    <div class="col-12 mt-2" id="media">
            <div class="card shadow mb-4">
            <!-- Card Header - Dropdown -->
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-fw fa-images"></i> Imagenes</h6>
            </div>
            <div class="card-body">
                <div class="row text-center text-lg-left">
                <?php
                foreach($media_arr as $key=>$media) {
                    if($media['id'] == 0) {
                      continue;
                    }
                    $media = new Media($media['id']);
                    ?>
                    <div class="col-lg-3 col-md-4 col-6">
                    <img class="img-fluid img-thumbnail obj-media-img" src="../media/images/<?= $media->url; ?>" width="300" alt="<?= $media->nombre; ?>" data-idmedia="<?= $media->id; ?>" data-url="<?= $media->url; ?>">
                    </div>
                    <?php } ?>
                </div>
                <br />
                <div class="d-sm-flex align-items-center mb-4">
                    <button class="d-sm-inline-block btn btn-sm btn-primary shadow-sm subir-media-btn">+ Subir Imágen</button>
                </div>
            </div>
          </div>
      </div>
  <div class="col-12 mt-2">
    <div class="card">
      <div class="card-body d-flex justify-content-between">
      <?php
        if($usuario->nivel == "Administrador" || $usuario->nivel == "Jefe de Planta" ) {
        ?>
        <button class="btn btn-sm btn-danger eliminar-obj-btn"><i class="fas fa-fw fa-trash"></i> Eliminar</button>
        <?php
        } else
        {
          print "&nbsp;";
        }
        ?>
        <button class="btn btn-primary" id="guardar-btn"><i class="fas fa-fw fa-save"></i> Finalizar Mantenci&oacute;n</button>
      </div>
    </div>
  </div>

  </div>

  

        <div class="modal fade" tabindex="-1" role="dialog" id="eliminar-obj-modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Eliminar Mantenci&oacute;n</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <center><h5>Desea eliminar esta Mantenci&oacute;n?<br/>Este paso no es reversible.</h5></center>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" id="eliminar-obj-aceptar" data-bs-dismiss="modal">Eliminar</button>
        </div>
      </div>
    </div>
  </div>

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
            <form id="agregar-media-form" action="./php/procesar.php" method="POST" enctype="multipart/form-data">
              <input type="hidden" name="modo" value="subir-media">
              <input type="hidden" name="entidad" value="<?= $obj->table_name; ?>">
              <input type="hidden" name="id_<?= $obj->table_name; ?>" value="<?= $obj->id; ?>">
            <div class="form-group">
              <label for="nombre-name" class="control-label">Nombre:</label>
              <input type="text" class="form-control" name="media_nombre" value="Mantencion #<?= $obj->id; ?> <?= $activo->nombre." ".(count($media_arr)+1); ?>">
            </div>
            <div class="form-group">
              <label for="descripcion-text" class="control-label">Descripci&oacute;n:</label>
              <textarea type="text" class="form-control" name="media_descripcion">Imagen de Mantencion #<?= $obj->id; ?> <?= $activo->nombre; ?></textarea>
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

<div class="modal fade" id="eliminar-media-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"><i class="fas fa-fw fa-images"></i> Media</h5>
                <button class="close" type="button" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
            <div class="row">
              <div class="col-6">
                <img src="" id="eliminar-media-img" width="70%">
              </div>
              <div class="col-6">
                <b>URL:</b>
                <br/>
                <textarea class="form-control" id="url-txt" READONLY></textarea>
                <br/>
              </div>
            </div>
              
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" type="button" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-danger" id="obj-media-eliminar-btn">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="eliminar-modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Eliminar Publicaci&oacute;n</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <center><h5>Desea eliminar esta publicaci&oacute;n?<br/>Este paso no es reversible.</h5></center>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" id="eliminar-aceptar" data-bs-dismiss="modal">Eliminar</button>
        </div>
      </div>
    </div>
  </div>
<script>


var obj = <?= json_encode($obj,JSON_PRETTY_PRINT); ?>;
var id_media_eliminar = 0;

$(document).ready(function(){

  $.each(obj,function(key,value){
      if(key!="table_name"&&key!="table_fields"){
      $('input[name="'+key+'"]').val(value);
      $('textarea[name="'+key+'"]').val(value);
      $('select[name="'+key+'"]').val(value);
      }
  });


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

  if($('input[name="hora_termino"]').val() == "") {
    const date = new Date();
    const hour = date.getHours();
    const min = date.getMinutes();
    $('input[name="hora_termino"]').val(hour + ":" + min);
  }

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("mantenciones");

  $.post(url,data,function(raw){
    console.log(raw);
    var response = JSON.parse(raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-mantenciones&id=" + response.obj.id + "&msg=3";
    }
  }).fail(function(){
    alert("No funciono");
  });
});


$(document).on('click','.obj-media-img',function(e) {
  e.preventDefault();
  id_media_eliminar = $(e.currentTarget).data('idmedia');
  var imagen = $('#eliminar-media-img')[0];
  imagen.src = "../media/images/" + $(e.currentTarget).data('url');
  $('#url-txt').val("https://app.barril.cl/media/images/" + $(e.currentTarget).data('url'));
  $('#eliminar-media-modal').modal('toggle');
});

$(document).on('click','#obj-media-eliminar-btn',function(e){

    e.preventDefault();

var data = {
  'id': obj.id,
  'entidad': obj.table_name,
  'id_media': id_media_eliminar
}

var url = './ajax/ajax_eliminarMedia.php';
$.post(url,data,function(raw){
  console.log(raw);
  var response = JSON.parse(raw);
  if(response.status!="OK") {
    alert("Algo fallo");
    return false;
  } else {
    window.location.href = "./?s=detalle-mantenciones&msg=2&id=" + obj.id;
  }
}).fail(function(){
  alert("No funciono");
});
});

$(document).on('click','#elegir-header-btn',function(e){

e.preventDefault();

var data = {
  'id': obj.id,
  'entidad': obj.table_name,
  'id_media': id_media_eliminar
}

var url = './ajax/ajax_cambiarMediaHeader.php';
$.post(url,data,function(raw){
  console.log(raw);
  var response = JSON.parse(raw);
  if(response.status!="OK") {
    alert("Algo fallo");
    return false;
  } else {
    window.location.href = "./?s=detalle-mantenciones&msg=6&id=" + obj.id;
  }
}).fail(function(){
  alert("No funciono");
});
});

$("#url-txt").focus(function() { $(this).select(); } );

$('.subir-media-btn').click(function(e) {
    e.preventDefault();
    $('#agregar-media-modal').modal('toggle');
});


$(document).on('click','.eliminar-obj-btn',function(e){

    e.preventDefault();
    $('#eliminar-obj-modal').modal('toggle');

});

$(document).on('click','#eliminar-obj-aceptar',function(e){

    e.preventDefault();

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
        window.location.href = "./?s=" + response.table_name + "&msg=2";
    }
    },"json").fail(function(){
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
