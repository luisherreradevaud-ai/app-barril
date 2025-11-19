<?php

  if(!validaIdExists($_GET,'id')) {
    die();
  }

  $obj = new Documento($_GET['id']);

  $clientes = Cliente::getAll("WHERE estado='Activo'");
  $media_arr = $obj->getMedia();

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
        <i class="fas fa-fw fa-plus"></i> Detalle Documento
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
  Msg::show(1,'Documento guardado con &eacute;xito','primary');
  Msg::show(2,'Documento aprobado como Pago con &eacute;xito','success');
  Msg::show(3,'Documento guardado como Rechazado con &eacute;xito','danger');
?>

<form id="documentos-form" action="./php/procesar.php" method="post" enctype="multipart/form-data">
  <input type="hidden" name="id" value="">
  <input type="hidden" name="entidad" value="documentos">
  <input type="hidden" name="modo" value="nuevo-entidad-con-media">
  <input type="hidden" name="redirect" value="">
  <div class="row">
    <div class="col-md-6 row">
      <div class="col-6 mb-1">
        Cliente
      </div>
      <div class="col-6 mb-1">
        <select name="id_clientes" class="form-control">
        <option></option>
        <?php
                foreach($clientes as $cliente) {
                    print "<option value='".$cliente->id."'>".$cliente->nombre."</option>";
                }
            ?>
        </select>
      </div>
      <div class="col-6 mb-1">
        Folio
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" name="folio">
      </div>
      <div class="col-6 mb-1">
        Monto
      </div>
      <div class="col-6 mb-1">
        <div class="input-group">
          <span class="input-group-text" id="basic-addon1" style="border-radius: 10px 0px 0px 10px">$</span>
          <input type="text" class="form-control acero" name="monto" value="0">
        </div>
      </div>
      <div class="col-6 mb-1">
        Estado
      </div>
      <div class="col-6 mb-1">
          <input type="text" class="form-control" value="<?= $obj->estado; ?>" DISABLED>
      </div>
      <?php
      if($usuario->nivel == "Administrador" && $obj->estado == "Por Aprobar") {
      ?>
      <div class="col-6 mb-1">
      </div>
      <div class="col-6 mb-1">
          <button class="btn btn-info btn-sm w-100 aprobar-documento-pago-btn" data-accion="Aprobado"><i class="fas fa-fw fa-check"></i> Aprobar como Pago</button>
          <button class="btn btn-danger btn-sm w-100 mt-1 aprobar-documento-pago-btn" data-accion="Rechazado"><i class="fas fa-fw fa-xmark"></i> Rechazar</a>

      </div>
      <?php
      } else
      if($obj->estado == "Aprobado") {
      ?>
      <div class="col-6 mb-1">
      </div>
      <div class="col-6 mb-1">
          <a class="btn btn-info btn-sm w-100" href="./?s=detalle-pagos&id=<?= $obj->id_pagos; ?>"><i class="fas fa-fw fa-eye"></i> Ver Pago generado</a><br/>

      </div>
      <?php
      }
      ?>
      <div class="col-12 mt-3 mb-1 d-flex justify-content-between">
        <?php
        if($usuario->nivel == "Administrador") {
          ?>
          <button class="btn btn-sm btn-danger eliminar-obj-btn"><i class="fas fa-fw fa-trash"></i> Eliminar</button>

          <?php
        } else {
          print "&nbsp;";
        }
        ?>
        <button class="btn btn-sm btn-primary guardar-btn" data-redirect="detalle-documentos"><i class="fas fa-fw fa-save"></i> Guardar</button>
      </div>
    </div>
  </div>
</form>

<div class="row">
      <div class="col-12 row mt-5" id="media">
        <div class="col-xl-12 col-lg-12">
          <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-fw fa-images"></i> Imagenes</h6>
            </div>
            <div class="card-body">
                <div class="row text-center text-lg-left">
                <?php
                foreach($media_arr as $key=>$media) {
                    $media = new Media($media['id']);
                    $activo = "";
                    ?>
                    <div class="col-lg-3 col-md-4 col-6">
                    <img class="img-fluid img-thumbnail obj-media-img<?= $activo; ?>" src="../media/images/<?= $media->url; ?>" width="300" alt="<?= $media->nombre; ?>" data-idmedia="<?= $media->id; ?>" data-url="<?= $media->url; ?>">
                    </div>
                    <?php } ?>
                </div>
                <br />
                <div class="d-sm-flex align-items-center mb-4">
                    <button class="d-sm-inline-block btn btn-sm btn-primary shadow-sm subir-media-btn">+ Subir imagen</button>
                </div>
            </div>
            </div>
        </div>
      </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="eliminar-obj-modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Eliminar Documento</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <center><h5>Desea eliminar este documento?<br/>Este paso no es reversible.</h5></center>
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
              <input type="text" class="form-control" name="media_nombre" value="Documento #<?= $obj->id." - ".(count($media_arr)+1); ?>">
            </div>
            <div class="form-group">
              <label for="descripcion-text" class="control-label">Descripci&oacute;n:</label>
              <textarea type="text" class="form-control" name="media_descripcion">Imagen de Documento <?= $obj->id; ?></textarea>
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
              <div class="col-12">
                <img src="" id="eliminar-media-img" width=100%">
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

<script>

var obj = <?= json_encode($obj,JSON_PRETTY_PRINT); ?>;

$(document).ready(function(){
  if(obj.id!="") {
    $.each(obj,function(key,value){
      if(key!="table_name"&&key!="table_fields"){
        $('input[name="'+key+'"]').val(value);
        $('textarea[name="'+key+'"]').val(value);
        $('select[name="'+key+'"]').val(value);
        <?php
        if($usuario->nivel != "Administrador") {
        ?>
        $('input[name="'+key+'"]').attr('disabled',true);
        $('textarea[name="'+key+'"]').attr('disabled',true);
        $('select[name="'+key+'"]').attr('disabled',true);
        <?php
        }
        ?>
      }
    });
  }
});

$(document).on('click','.guardar-btn',function(e){

  e.preventDefault();

  if($('select[name="id_clientes"]').val() == "") {
    alert("Debe ingresar un Cliente");
    return false;
  }

  if($('input[name="monto"]').val() == "") {
    $('input[name="monto"]').val(0);
  }

  if($('input[name="monto"]').val() < 1) {
    alert("El monto debe ser mayor a 0.");
    return false;
  }

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("documentos");

  $.post(url,data,function(response){
    console.log(response);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-documentos&id=" + response.obj.id + "&msg=1";
    }
  },'json').fail(function(){
    alert("No funciono");
  });

});


$(document).on('click','.aprobar-documento-pago-btn',function(e){

  e.preventDefault();

  var url = "./ajax/ajax_aprobarDocumentoPago.php";
  var data = {
    'id': obj.id,
    'accion': $(e.currentTarget).data('accion')
  };

  console.log(data);

  $.post(url,data,function(raw){
    console.log(raw);
    var response = JSON.parse(raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      if(response.obj.estado == "Aprobado") {
        window.location.href = "./?s=detalle-documentos&id=" + response.obj.id + "&msg=2";
      }
      if(response.obj.estado == "Rechazado") {
        window.location.href = "./?s=detalle-documentos&id=" + response.obj.id + "&msg=3";
      }
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


$(document).on('click','.eliminar-obj-btn',function(e){
  e.preventDefault();
  $('#eliminar-obj-modal').modal('toggle');
})

$(document).on('click','#eliminar-obj-aceptar',function(e){

  e.preventDefault();

  var data = {
    'id': obj.id,
    'modo': obj.table_name
  }
  var url = './ajax/ajax_eliminarEntidad.php';
  $.post(url,data,function(raw){
    console.log(raw);
    var response = JSON.parse(raw);
    if(response.status!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=" + response.table_name + "&msg=2";
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
    window.location.href = "./?s=detalle-documentos&msg=2&id=" + obj.id;
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
    window.location.href = "./?s=detalle-documentos&msg=6&id=" + obj.id;
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

</script>
