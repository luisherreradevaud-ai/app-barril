<?php

//checkAutorizacion(["Jefe de Planta","Administrador","Jefe de Cocina","Operario","Repartidor"]);

if(!validaIdExists($_GET,'id')) {
  die();
}

$obj = new Activo($_GET['id']);
$obj->getAccesorios();
$usuario = $GLOBALS['usuario'];

if($usuario->nivel != "Administrador" && $usuario->nivel!= "Jefe de Planta") {
  if($obj->id_usuarios_control != $usuario->id) {
    die();
  }
}
$media_arr = $obj->getMedia();
$media_header = $obj->getMediaHeader();

$mantenciones = Mantencion::getAll("WHERE id_activos='".$obj->id."'");

?>
<style>
.tr-proveedor {
  cursor: pointer;
}
.media-activo {
  border: 2px solid red;
}

.obj-archivo-img {
  cursor: pointer;
}
</style>
<script src="./js/ckeditor/ckeditor.js"></script>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800">
      <b>
        <i class="fas fa-fw fa-industry"></i> Ficha #<?= $obj->id; ?> <?= $obj->nombre; ?>
      </b>
    </h1>
  </div>
  <div>
  <?php
  if($usuario->nivel == "Administrador") {
    ?>
    <a href="./?s=detalle-activos&id=<?=$obj->id; ?>" class="btn btn-warning btn-sm" type="button">
        Editar
    </a>
    <?php
  }
  ?>
    <a href="./?s=activos" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i class="fas fa-fw fa-backward"></i> Volver a Activos</a>

  </div>
</div>
<hr />
<form id="activos-form">
  <input type="hidden" name="id" value="">
  <input type="hidden" name="entidad" value="activos">
  <input type="hidden" name="id_usuarios" value="<?= $GLOBALS['usuario']->id; ?>">
  <div class="card">
    <div class="card-body">
  <div class="row">
    <div class="col-md-7">
        <div class="row">
      <div class="col-6 mb-1">
        Nombre:
      </div>
      <div class="col-6 mb-1">
        <b><?= $obj->nombre; ?></b>
      </div>
      <div class="col-6 mb-1">
        Clase:
      </div>
      <div class="col-6 mb-1">
        <?= $obj->clase; ?>
      </div>
      <div class="col-6 mb-1">
        Marca:
      </div>
      <div class="col-6 mb-1">
        <b><?= $obj->marca; ?></b>
      </div>
      <div class="col-6 mb-1">
        Modelo:
      </div>
      <div class="col-6 mb-1">
        <b><?= $obj->modelo; ?></b>
      </div>
      <div class="col-6 mb-1">
        C&oacute;digo:
      </div>
      <div class="col-6 mb-1">
        <b><?= $obj->codigo; ?></b>
      </div>
      <div class="col-6 mb-1">
        Capacidad:
      </div>
      <div class="col-6 mb-1">
        <b><?= $obj->capacidad; ?></b>
      </div>
      <div class="col-6 mb-1">
        Clasificaci&oacute;n:
      </div>
      <div class="col-6 mb-1">
        <b><?= $obj->clasificacion; ?></b>
      </div>
      <div class="col-6 mb-1">
        Valorizaci&oacute;n:
      </div>
      <div class="col-6 mb-1">
        <b><?= $obj->valorizacion; ?></b>
      </div>
      <div class="col-6 mb-1">
        Estado:
      </div>
      <div class="col-6 mb-1">
        <b><?= $obj->estado; ?></b>
      </div>
      <div class="col-6 mb-1">
        Ubicaci&oacute;n:
      </div>
      <div class="col-6 mb-1">
        <b><?= $obj->ubicacion; ?></b>
      </div>
      <?php
      if($obj->ubicacion == "En terreno" && $obj->id_clientes_ubicacion != 0) {
        $cliente_ubicacion = new Cliente($obj->id_clientes_ubicacion);
      ?>
      <div class="col-6 mb-1">
        Cliente:
      </div>
      <div class="col-6 mb-1">
        <b><?= $cliente_ubicacion->nombre; ?></b>
      </div>
      <?php
      }
      ?>
      <div class="col-12 mb-2 mt-2">
        <hr/>
      </div>
      <div class="col-6 mb-1">
        Propietario:
      </div>
      <div class="col-6 mb-1">
        <b><?= $obj->propietario; ?></b>
      </div>
      <div class="col-6 mb-1">
        Fecha adquisici&oacute;n:
      </div>
      <div class="col-6 mb-1">
        <b><?= date2fecha($obj->adquisicion_date); ?></b>
      </div>
      <div class="col-6 mb-1">
        Responsabilidad / control:
      </div>
      <div class="col-6 mb-1">
        <b>
          <?php
            $usuario_control = new Usuario($obj->id_usuarios_control);
            print $usuario_control->nombre;
          ?>
        </b>
      </div>
      <div class="col-12 mb-2 mt-2">
        <hr/>
      </div>
      <div class="col-6 mb-1">
        Periodicidad inspecci&oacute;n:
      </div>
      <div class="col-6 mb-1">
        <b><?= $obj->inspeccion_periodicidad; ?></b>
      </div>
      <div class="col-6 mb-1">
        &Uacute;ltima inspecci&oacute;n:
      </div>
      <div class="col-6 mb-1">
        <b><?= date2fecha($obj->ultima_inspeccion); ?></b>
      </div>
      <div class="col-6 mb-1">
        Pr&oacute;xima inspecci&oacute;n:
      </div>
      <div class="col-6 mb-1">
        <b><?= date2fecha($obj->proxima_inspeccion); ?></b>
      </div>
      <div class="col-6 mt-4 mb-1">
        Periodicidad mantenci&oacute;n:
      </div>
      <div class="col-6 mt-4 mb-1">
        <b><?= $obj->mantencion_periodicidad; ?></b>
      </div>
      <div class="col-6 mb-1">
        &Uacute;ltima mantenci&oacute;n:
      </div>
      <div class="col-6 mb-1">
        <b><?= date2fecha($obj->ultima_mantencion); ?></b>
      </div>
      <div class="col-6 mb-1">
        Pr&oacute;xima mantenci&oacute;n:
      </div>
      <div class="col-6 mb-1">
        <b><?= date2fecha($obj->proxima_mantencion); ?></b>
      </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card">
            <img src="./media/thumbnails/640/<?= $media_header->url; ?>" class="w-100">
        </div>
    </div>
  </div>
</div>
    
</div>



  <div class="row">
    <div class="col-12 mb-2 mt-3">
              <div class="card">
        <div class="card-body">
          <h1 class="h4 mb-0 text-gray-800">
            <b>
                Accesorios
            </b>
          </h1>
          <table class="table table-sm table-stripped mt-4">
            <thead>
              <tr>
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
              foreach($obj->accesorios as $accesorio) {
              ?>
              <tr>
                <td>
                  <?= $accesorio->nombre; ?>
                </td>
                <td>
                  <?= $accesorio->marca; ?>
                </td>
                <td>
                  <?= $accesorio->ultimo_cambio; ?>
                </td>
                <td>
              </tr>
              <?php
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="col-12 mb-4 mt-2">
      <div class="d-sm-flex align-items-center justify-content-between mb-3">
      <div>
        <h1 class="h4 mb-0 text-gray-800">
          <b>
              Registro de Inspecci&oacute;n y Mantenci&oacute;n
          </b>
        </h1>
      </div>
      <div>
        <button class="btn btn-primary btn-sm" id="inspeccion_procedimiento_abrir">
          Procedimiento de Inspecci&oacute;n
        </button>
        <button class="btn btn-primary btn-sm" id="mantencion_procedimiento_abrir">
          Procedimiento de Mantenci&oacute;n
        </button>
        </div>
      </div>
    </div>

    <div class="col-12">
    <table class="table table-hover table-striped table-sm" id="insumos-table">
  <thead class="thead-dark">
    <tr>
        <th>
      <a href="#" class="sort" data-show="insumos" data-sort="nombre">ID</a>
      </th>
      <th>
      <a href="#" class="sort" data-show="insumos" data-sort="nombre">Fecha</a>
      </th>
      <th>
      <a href="#" class="sort" data-show="insumos" data-sort="nombre">Tarea</a>
      </th>
      <th>
      <a href="#" class="sort" data-show="insumos" data-sort="nombre">Ejecutor</a>
      </th>

    </tr>
  </thead>
  <tbody>
    <?php
      $total = 0;
      foreach($mantenciones as $mantencion) {
    ?>
    <tr class="tr-mantenciones" data-idmantenciones="<?= $mantencion->id; ?>">
        <td>
            #<?= $mantencion->id; ?>
      </td>
      <td>
        <?= date2fecha($mantencion->date); ?>
      </td>
      <td>
        <?= $mantencion->tarea; ?>
      </td>
      <td>
        <?= $mantencion->ejecutor; ?>
      </td>
    </tr>
    <?php
      }
    ?>
  </tbody>
</table>


    </div>


    <div class="col-12 row mt-5" id="media">
        <div class="col-xl-12 col-lg-12">
            <div class="card shadow mb-4">
            <!-- Card Header - Dropdown -->
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-fw fa-images"></i> Imagenes</h6>
            </div>
            <div class="card-body">
                <div class="row text-center text-lg-left">
                <?php
                foreach($media_arr as $key=>$media) {
                    $media = new Media($media['id']);
                    $activo = "";
                    if(( $obj->id_media_header == 0 && $key == 0 ) || $obj->id_media_header == $media->id ) {
                    $activo = " media-activo";
                    }
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
</form>

<div class="modal fade" tabindex="-1" role="dialog" id="inspeccion_procedimiento_modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Procedimiento de Inspecci&oacute;n</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <?= $obj->inspeccion_procedimiento; ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" tabindex="-1" role="dialog" id="mantencion_procedimiento_modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Procedimiento de Mantenci&oacute;n</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <?= $obj->mantencion_procedimiento; ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>


<div class="modal fade" tabindex="-1" role="dialog" id="eliminar-obj-modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Eliminar Activo</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <center><h5>Desea eliminar este Activo?<br/>Este paso no es reversible.</h5></center>
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
                <br/>
                <button class="btn btn-success" id="elegir-header-btn">Elegir como Imagen Principal</button>
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

var id_media_eliminar = 0;
var editar = false;


$('#editar-btn').click(function(e){

    e.preventDefault();

    if(editar == false) {

        $.each(obj,function(key,value){
        console.log(key);
        if(key!="table_name"&&key!="table_fields"){
            $('input[name="'+key+'"]').attr('disabled',false);
            $('textarea[name="'+key+'"]').attr('disabled',false);
            $('select[name="'+key+'"]').attr('disabled',false);
        }
        });

        CKEDITOR.instances.inspeccion_procedimiento.setReadOnly(false);
        CKEDITOR.instances.mantencion_procedimiento.setReadOnly(false);

        $('#guardar-btn').attr('disabled',false);
        $('.eliminar-obj-btn').attr('disabled',false);

        $(e.currentTarget).html('<i class="fas fa-fw fa-unlock"></i> Editando');
        $(e.currentTarget).removeClass('btn-warning');
        $(e.currentTarget).addClass('btn-danger');

        editar = true;

    } else {

        $.each(obj,function(key,value){
        console.log(key);
        if(key!="table_name"&&key!="table_fields"){
            $('input[name="'+key+'"]').attr('disabled',true);
            $('textarea[name="'+key+'"]').attr('disabled',true);
            $('select[name="'+key+'"]').attr('disabled',true);
        }
        });

        CKEDITOR.instances.inspeccion_procedimiento.setReadOnly();
        CKEDITOR.instances.mantencion_procedimiento.setReadOnly();

        $('#guardar-btn').attr('disabled',true);
        $('.eliminar-btn').attr('disabled',true);

        $(e.currentTarget).html('<i class="fas fa-fw fa-lock"></i> Editar');
        $(e.currentTarget).addClass('btn-warning');
        $(e.currentTarget).removeClass('btn-danger');

        editar = false;


    }

})

var obj = <?= json_encode($obj,JSON_PRETTY_PRINT); ?>;



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
    window.location.href = "./?s=detalle-activos&msg=2&id=" + obj.id;
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
    window.location.href = "./?s=detalle-activos&msg=6&id=" + obj.id;
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

$(document).on('click','.tr-mantenciones',function(e) {
    window.location.href = "./?s=detalle-mantenciones&id=" + $(e.currentTarget).data('idmantenciones');
});

$(document).on('click','#inspeccion_procedimiento_abrir',function(e){
  e.preventDefault();
  $('#inspeccion_procedimiento_modal').modal('toggle');
})

$(document).on('click','#mantencion_procedimiento_abrir',function(e){
  e.preventDefault();
  $('#mantencion_procedimiento_modal').modal('toggle');
})

</script>
