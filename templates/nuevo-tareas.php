<?php

    $niveles_usuario = $GLOBALS['niveles_usuario'];
    $usuarios = Usuario::getAll("WHERE nivel!='Cliente' AND estado='Activo' ORDER BY nombre asc");
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
        <i class="fas fa-fw fa-plus"></i> Nueva Tarea
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
<form id="tareas-form">
  <input type="hidden" name="id" value="">
  <input type="hidden" name="destinatario" value="">
  <input type="hidden" name="entidad" value="tareas">
  <input type="hidden" name="id_usuarios_emisor" value="<?= $GLOBALS['usuario']->id; ?>">
  <div class="row">
    <div class="col-md-6 row">
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
        <select name="destinatario_id_usuarios" class="form-control destinatario">
            <option></option>
            <?php
            foreach($usuarios as $usuario) {
                print "<option value='".$usuario->id."'>".$usuario->nombre."</option>";
            }
            ?>
        </select>
        <select name="destinatario_nivel" class="form-control destinatario">
            <option></option>
            <?php
            foreach($niveles_usuario as $nivel) {
              if($nivel == "Cliente") {
                continue;
              }
              print "<option>".$nivel."</option>";
            }
            if($usuario->nivel == "Administrador") {
            ?>
            <option>Usuarios Internos</option>
            <?php
            }
            ?>
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
        <input type="date" name="plazo_maximo" class="form-control" value="<?= date('Y-m-d'); ?>">
      </div>
      <div class="col-12 mb-1">
        Tarea:
      </div>
      <div class="col-12 mb-1">
        <textarea name="tarea" class="form-control"></textarea>
      </div>
      <div class="col-12 mt-3 mb-1 text-right">
        <input type="checkbox" name="enviar_email" CHECKED> <b>Enviar Email</b>
      </div>
      <div class="col-12 mt-3 mb-1 d-flex justify-content-between">
        <button class="btn btn-sm btn-primary" id="agregar-y-continuar-btn"><i class="fas fa-fw fa-plus"></i> Agregar y continuar</button>
        <button class="btn btn-sm btn-primary" id="guardar-btn"><i class="fas fa-fw fa-save"></i> Guardar</button>
      </div>
      
    </div>
    <div class="col-md-6">
      <table class="table table-striped mt-4" id="tareas-table">
      </table>
    </div>
  </div>
</form>

<script>

var tareas = [];

$(document).ready(function() {
    $('select[name="destinatario_nivel"]').hide();
    $('#guardar-btn').attr('disabled',true);
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

  var url = "./ajax/ajax_guardarTareas.php";
  var data = getDataForm("tareas");
  data['tareas'] = tareas;

  if($('select[name="tipo_envio"]').val() == "Usuario") {
    data['destinatario'] = $('select[name="destinatario_id_usuarios"]').val();
  } else {
    data['destinatario'] = $('select[name="destinatario_nivel"]').val();
  }

  $.post(url,data,function(raw){
    console.log(raw);
    var response = JSON.parse(raw);
    if(response.status!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      if(response.mensaje == 'Tarea') {
        window.location.href = "./?s=tareas&msg=4";
      } else {
        window.location.href = "./?s=tareas&msg=6";
      }
      
    }
  }).fail(function(){
    alert("No funciono");
  });
});


$(document).on('click','#agregar-y-continuar-btn',function(e) {

  e.preventDefault();

  tareas.push({
    'tarea': $('textarea[name="tarea"]').val(),
    'importancia': $('select[name="importancia"]').val(),
    'plazo_maximo': $('input[name="plazo_maximo"]').val()
  });
  $('textarea[name="tarea"]').val('');
  renderTable();
});

function renderTable() {
  var html = '';
  tareas.forEach(function(t,index){
    html += '<tr class="productos-tr';
    if(t.importancia == 'URGENTE') {
      html += "table-danger";
    }
    html += '" data-index="' + index +'"><td>' + t.plazo_maximo;
    html += '</td><td><b>' + t.tarea;
    html += '</td><td><b>' + t.importancia;
    html += '</b></td><td><button class="btn btn-sm item-eliminar-btn" data-index="' + index + '">x</button>';
    html += '</td></tr>';
  });
  $('#tareas-table').html(html);
}

$(document).on('click','.item-eliminar-btn',function(e){
  var index = $(e.currentTarget).data('index');
  tareas.splice(index,1);
  renderTable();
});



</script>
