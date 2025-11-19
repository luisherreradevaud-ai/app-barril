<?php

    if(!validaIdExists($_GET,'id')) {
        die();
    }

    $obj = new Tarea($_GET['id']);

    if($obj->id == '') {
        die();
    }

    $usuario = $GLOBALS['usuario'];

    if($usuario->nivel == "Cliente") {
      die();
    }

    if( (($obj->tipo_envio == 'Usuario' && $usuario->id != $obj->destinatario ) || ($obj->tipo_envio == 'Nivel' && $usuario->nivel != $obj->destinatario )) && $obj->id_usuarios_emisor != $usuario->id ) {
        die();
    }

    $niveles_usuario = $GLOBALS['niveles_usuario'];
    $usuarios = Usuario::getAll("WHERE nivel!='Cliente' AND estado='Activo' ORDER BY nombre asc");

    $usuario_emisor = new Usuario($obj->id_usuarios_emisor);

    $tareas_comentarios = TareaComentario::getAll("WHERE id_tareas='".$obj->id."' ORDER BY id desc");

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
        <i class="fas fa-fw fa-list"></i> Detalle Tarea
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
Msg::show(1,'Tarea editada con &eacute;xito.','info');
Msg::show(2,'Comentario agregado con &eacute;xito.','info');
Msg::show(23,'Comentario eliminado con &eacute;xito.','danger');

?>
<form id="tareas-form">
  <input type="hidden" name="id" value="">
  <input type="hidden" name="destinatario" value="">
  <input type="hidden" name="entidad" value="tareas">
  <input type="hidden" name="id_usuarios_emisor" value="<?= $GLOBALS['usuario']->id; ?>">
  <div class="row">
    <div class="col-md-6 row">
      <div class="col-6 mb-1">
        Emisor:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" value="<?= $usuario_emisor->nombre; ?>" DISABLED >
      </div>
    <div class="col-6 mb-1">
        Tipo Destinatario:
      </div>
      <div class="col-6 mb-1">
        <select name="tipo_envio" class="form-control">
            <option>Usuario</option>
            <option>Nivel</option>
        </select>
      </div>
      <div class="col-6 mb-1">
        Destinatario
      </div>
      <div class="col-6 mb-1">
        <select name="destinatario_id_usuarios" class="form-control destinatario" DISABLED>
            <option></option>
            <?php
            foreach($usuarios as $usuario_2) {
                print "<option value='".$usuario_2->id."'>".$usuario_2->nombre."</option>";
            }
            ?>
        </select>
        <select name="destinatario_nivel" class="form-control destinatario" DISABLED>
            <option></option>
            <?php
            foreach($niveles_usuario as $nivel) {
              if($nivel == "Cliente") {
                continue;
              }
              print "<option>".$nivel."</option>";
            }
            ?>
            <option>Usuarios Internos</option>
        </select>
      </div>
      <div class="col-6 mb-1">
        Importancia:
      </div>
      <div class="col-6 mb-1">
        <select name="importancia" class="form-control">
            <option>Normal</option>
            <option>URGENTE</option>
        </select>
      </div>
      <div class="col-6 mb-1">
        Plazo m&aacute;ximo:
      </div>
      <div class="col-6 mb-1">
        <input type="date" name="plazo_maximo" class="form-control">
      </div>
      <div class="col-6 mb-1">
        Estado:
      </div>
      <div class="col-6 mb-1">
        <select name="estado" class="form-control">
            <option>Pendiente</option>
            <option>Realizada</option>
        </select>
      </div>
      <div class="col-12 mb-1">
        Tarea:
      </div>
      <div class="col-12 mb-1">
      <?php
      if($obj->id_usuarios_emisor == $usuario->id) {
        ?>
        <textarea name="tarea" class="form-control" style='heigth: 200px'></textarea>
        <?php
        } else {
          print "<div class='card' style='padding:10px'>";
          print nl2br($obj->tarea);
          print "</div>";
        }
      ?>
      </div>

      <div class="col-12 mt-3 mb-1 d-flex justify-content-between">
      <?php
      if($obj->id_usuarios_emisor == $usuario->id) {
        ?>
        <button class="btn btn-sm btn-danger eliminar-obj-btn"><i class="fas fa-fw fa-trash"></i> Eliminar</button>
        <div>
          <button class="btn btn-sm btn-primary" id="re-enviar-correo-btn"><i class="fas fa-fw fa-envelope"></i> Reenviar Correo</button>
          <button class="btn btn-sm btn-primary" id="guardar-btn"><i class="fas fa-fw fa-save"></i> Guardar</button>
        </div>
        <?php
      }
      ?>
      </div>
    </div>
    <div class="col-6">
      <div class="d-flex justify-content-between">
        <h1 class="h6 mb-0 text-gray-800"><b>Comentarios</b></h1>
        <button class="btn btn-sm btn-primary agregar-comentario-btn"><i class="fas fa-fw fa-plus"></i> Agregar Comentario</button>
      </div>
      <table class="table table-sm table-striped mt-2 mb-3">
      <?php
        foreach($tareas_comentarios as $tc) {
          $tc_usuario = new Usuario($tc->id_usuarios);
          ?>
          <tr>
            <td>
              <div class="d-flex justify-content-between">
                <div>
                  <b><?= $tc_usuario->nombre; ?></b> <small><?= datetime2fechayhora($tc->creada); ?></small>
                </div>
                <?php
                  if($tc->id_usuarios == $usuario->id) {
                    print "<a href='#' style='color: red' class='eliminar-tareas-comentarios-btn' data-idtareascomentarios='".$tc->id."'>Eliminar</a>";
                  }
                ?>
              </div>
              <?= $tc->comentario; ?>
            </td>
          </tr>
          <?php
        }
      ?>
      </table>
    </div>
  </div>
</form>

<div class="modal fade" tabindex="-1" role="dialog" id="eliminar-obj-modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Eliminar Tarea</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <center><h5>Desea eliminar esta Tarea?<br/>Este paso no es reversible.</h5></center>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" id="eliminar-obj-aceptar" data-bs-dismiss="modal"><i class="fas fa-fw fa-trash"></i> Eliminar</button>
        </div>
      </div>
    </div>
  </div>


  <div class="modal fade" tabindex="-1" role="dialog" id="eliminar-tareas-comentarios-modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Eliminar Comentario</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <center><h5>Desea eliminar este Comentario?<br/>Este paso no es reversible.</h5></center>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" id="eliminar-tareas-comentarios-aceptar-btn" data-bs-dismiss="modal"><i class="fas fa-fw fa-trash"></i> Eliminar</button>
        </div>
      </div>
    </div>
  </div>


    <div class="modal fade" tabindex="-1" role="dialog" id="agregar-comentario-modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Agregar Comentario</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="tareas_comentarios-form">
            <input type="hidden" name="id_tareas" value="<?= $obj->id; ?>">
            <input type="hidden" name="id_usuarios" value="<?= $usuario->id; ?>">
            <input type="hidden" name="entidad" value="tareas_comentarios">
            <input type="hidden" name="id" value="">
            <div class="row">
              <div class="col-6 mb-1">
                Fecha:
              </div>
              <div class="col-6 mb-1">
                <input type="date" name="date" class="form-control" value="<?= date('Y-m-d'); ?>">
              </div>
              <div class="col-12 mb-1">
                Comentario:
              </div>
              <div class="col-12">
                <textarea name="comentario" class="form-control"></textarea>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="agregar-comentario-aceptar-btn" data-bs-dismiss="modal"><i class="fas fa-fw fa-plus"></i> Agregar</button>
        </div>
      </div>
    </div>
  </div>

<script>

var obj = <?= json_encode($obj,JSON_PRETTY_PRINT); ?>;
var usuario = <?= json_encode($usuario,JSON_PRETTY_PRINT); ?>;
var id_tareas_comentarios = 0;

$(document).ready(function() {
    $.each(obj,function(key,value){
      if(key!="table_name"&&key!="table_fields"){
        $('#tareas-form input[name="'+key+'"]').val(value);
        $('#tareas-form textarea[name="'+key+'"]').val(value);
        $('#tareas-form select[name="'+key+'"]').val(value);
        if(obj.id_usuarios_emisor != usuario.id) {
            $('#tareas-form input[name="'+key+'"]').attr('disabled',true);
            $('#tareas-form textarea[name="'+key+'"]').attr('disabled',true);
            $('#tareas-form select[name="'+key+'"]').attr('disabled',true);
        }
      }
    });
    if(obj.tipo_envio == 'Usuario') {
        $('select[name="destinatario_id_usuarios"]').val(obj.destinatario);
    }
    if(obj.tipo_envio == 'Nivel') {
        $('select[name="destinatario_nivel"]').val(obj.destinatario);
    }
    $('select[name="destinatario_nivel"]').hide();
});

$(document).on('change','select[name="tipo_envio"]',function() {
    if($('select[name="tipo_envio"]').val() == "Usuario") {
        $('select[name="destinatario_nivel"]').hide(200);
        $('select[name="destinatario_id_usuarios"]').show(200);
        if($('select[name="destinatario_id_usuarios"]').val() == "") {
            $('#guardar-btn').attr('disabled',true);
        } else {
            $('#guardar-btn').attr('disabled',false);
        }
    } else {
        $('select[name="destinatario_nivel"]').show(200);
        $('select[name="destinatario_id_usuarios"]').hide(200);
        if($('select[name="destinatario_nivel"]').val() == "") {
            $('#guardar-btn').attr('disabled',true);
        } else {
            $('#guardar-btn').attr('disabled',false);
        }
    }
});

$(document).on('change','.destinatario',function(e) {
    if($(e.currentTarget).val() != "") {
        $('#guardar-btn').attr('disabled',false);
    }
})

$(document).on('click','#guardar-btn',function(e){

  e.preventDefault();

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("tareas");

  if($('select[name="tipo_envio"]').val() == "Usuario") {
    data['destinatario'] = $('select[name="destinatario_id_usuarios"]').val();
  } else {
    data['destinatario'] = $('select[name="destinatario_nivel"]').val();
  }

  $.post(url,data,function(response){
    console.log(response);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-tareas&msg=1&id=" + obj.id;
    }
  },'json').fail(function(){
    alert("No funciono");
  });
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
  $.post(url,data,function(response){
    if(response.status!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=tareas&msg=5";
    }
  },"json").fail(function(){
    alert("No funciono");
  });
});


$(document).on('click','#re-enviar-correo-btn',function(e){

  e.preventDefault();

  var data = {
    'id': obj.id,
    'modo': obj.table_name
  }
  var url = './ajax/ajax_reEnviarCorreoTareas.php';
  $.post(url,data,function(response){
    if(response.status!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      alert("Correo enviado con exito");
      return false;
    }
  },"json").fail(function(){
    alert("No funciono");
  });
});


$(document).on('click','.agregar-comentario-btn',function(e) {
  e.preventDefault();
  $('textarea[name="comentario"]').val('');
  $('#agregar-comentario-modal').modal('toggle');
});


$(document).on('click','#agregar-comentario-aceptar-btn',function(e){

  e.preventDefault();

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("tareas_comentarios");

  console.log(data);

  $.post(url,data,function(raw){
    console.log(raw);
    var response = JSON.parse(raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-tareas&id=" + obj.id + "&msg=2";
    }
  }).fail(function(){
    alert("No funciono");
  });
});


$(document).on('click','.eliminar-tareas-comentarios-btn',function(e){
  e.preventDefault();
  id_tareas_comentarios = $(e.currentTarget).data('idtareascomentarios');
  $('#eliminar-tareas-comentarios-modal').modal('toggle');
})

$(document).on('click','#eliminar-tareas-comentarios-aceptar-btn',function(e){

  e.preventDefault();

  var data = {
    'id': id_tareas_comentarios,
    'modo': 'tareas_comentarios'
  }
  var url = './ajax/ajax_eliminarEntidad.php';
  $.post(url,data,function(response){
    if(response.status!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-tareas&id=" + obj.id + "&msg=3";
    }
  },"json").fail(function(){
    alert("No funciono");
  });
});



</script>
