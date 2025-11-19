<?php

  $usuario = $GLOBALS['usuario'];

  $order_by = "por defecto";
  $order = "desc";

  if(isset($_GET['order_by'])) {
    if($_GET['order_by'] == "creada") {
      $order_by = "creada";
    } else
    if($_GET['order_by'] == "emisor") {
      $order_by = "emisor";
    } else
    if($_GET['order_by'] == "plazo_maximo") {
      $order_by = "plazo_maximo";
    }
    if($_GET['order_by'] == "estado") {
      $order_by = "estado";
    }
  }

  if(isset($_GET['order'])) {
    if($_GET['order'] == "asc") {
      $order = "asc";
    }
  }
  
  if($order_by == "por defecto") {
    $tareas = Tarea::getAll("WHERE ((tipo_envio='Usuario' AND destinatario='".$usuario->id."') OR (tipo_envio='Nivel' AND destinatario='".$usuario->nivel."')) AND estado='Realizada' ORDER BY importancia desc, id desc");
  } else 
  if($order_by == "emisor") {
    $tareas = Tarea::getAll("INNER JOIN usuarios ON tareas.id_usuarios_emisor = usuarios.id WHERE ((tareas.tipo_envio='Usuario' AND tareas.destinatario='".$usuario->id."') OR (tareas.tipo_envio='Nivel' AND tareas.destinatario='".$usuario->nivel."')) AND estado='Realizada' ORDER BY usuarios.nombre ".$order);
  } else {
    $tareas = Tarea::getAll("WHERE ((tipo_envio='Usuario' AND destinatario='".$usuario->id."') OR (tipo_envio='Nivel' AND destinatario='".$usuario->nivel."')) AND estado='Realizada' ORDER BY ".$order_by." ".$order);
  }
    
?>
<style>
.tr-tareas {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-folder"></i> <b>Tareas Realizadas</b></h1>
  </div>
  <div>
    <a href="./?s=planificacion" class="d-sm-inline-block btn btn-secondary shadow-sm btn-sm"><i class="fas fa-fw fa-calendar"></i> Vista Planificaci&oacute;n</a>
    <a href="./?s=nuevo-tareas" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm mb-2"><i class="fas fa-fw fa-plus"></i> Nueva Tarea</a>
  </div>
</div>
<a href="./?s=planificacion" class="d-sm-inline-block btn btn-sm btn-secondary shadow-sm mb-2"><i class="fas fa-fw fa-list"></i> Pendientes</a>
<a href="./?s=tareas-realizadas" class="d-sm-inline-block btn btn-sm btn-secondary shadow-sm mb-2"><i class="fas fa-fw fa-folder"></i> Realizadas</a>
<a href="./?s=tareas-enviadas-y-archivadas" class="d-sm-inline-block btn btn-sm btn-secondary shadow-sm mb-2"><i class="fas fa-fw fa-save"></i> Enviadas y Archivadas</a>
<hr />
<?php 
Msg::show(1,'Tarea enviada con &eacute;xito','info');
Msg::show(2,'Tarea eliminada con &eacute;xito','danger');
Msg::show(3,'Tarea marcada como Pendiente','info');
?>
<table class="table table-hover table-striped table-sm" id="tareas-table">
  <thead class="thead-dark">
    <tr>
      <th style="width: 30px">
      </th>
      <th>
        <a href="#" class="sort" data-sortorderby="creada">Creada</a>
      </th>
      <th>
        <a href="#" class="sort" data-sortorderby="plazo_maximo">Plazo m&aacute;ximo</a>
      </th>
      <th style="width: 40%">
        Tarea
      </th>
      <th>
        <a href="#" class="sort" data-sortorderby="emisor">Emisor</a>
      </th>
      <th>
      </th>
    </tr>
  </thead>
  <tbody>
    <?php
      foreach($tareas as $tarea) {
        $usuario_emisor = new Usuario($tarea->id_usuarios_emisor);
        $tr_class = "";
        $badge = "";
        if($tarea->importancia == "URGENTE") {
          //$tr_class = " table-danger";
          //$badge = "<span class='badge bg-danger' style='color: white !important'>URGENTE</span>";
        }
    ?>
    <tr class="tr-tareas<?= $tr_class; ?>" data-idtareas="<?= $tarea->id; ?>">
      <td>
        <input class="tareas-pendientes-checkbox" type="checkbox" data-idtareas="<?= $tarea->id; ?>">
      </td>
      <td>
        <?= datetime2fecha($tarea->creada); ?>
      </td>
      <td>
        <?= datetime2fecha($tarea->plazo_maximo); ?>
      </td>
      <td>
        <?= $badge; ?> <b style="color: black"><?= $tarea->tarea; ?></b>
      </td>
      <td>
        <?= $usuario_emisor->nombre; ?>
      </td>
      <td>
        <a href="#" class="cambiar-estado-btn" data-idtareas="<?= $tarea->id; ?>" data-estado="Pendiente">Marcar como Pendiente</a>
      </td>
    </tr>
    <?php
      }
    ?>
  </tbody>
</table>

<div class="mt-3 mb-3" style="font-size: 0.8em">
  Selecci&oacute;n (<span id="tareas_checkbox_total">0</span>): <a class="btn btn-sm btn-secondary accion-masiva" href="#tareas_checkbox_total" data-estado="Pendiente" data-accion="tareas-marcar-como">Marcar como Pendiente</a>
</div>

<script>

var order_by = "<?= $order_by; ?>";
var order = "<?= $order; ?>";
var change_checkbox_pendientes = [];

$(document).on('click','.tr-tareas',function(e) {
    window.location.href = "./?s=detalle-tareas&id=" + $(e.currentTarget).data('idtareas');
});

$(document).on('click','.cambiar-estado-btn',function(e) {

  e.preventDefault();
  e.stopPropagation();


  var url = "./ajax/ajax_cambiarEstadoTareas.php";
  var data = {
    'id_tareas': $(e.currentTarget).data('idtareas'),
    'estado': $(e.currentTarget).data('estado')
  };

  $.post(url,data,function(response_raw){
    console.log(response_raw);
    var response = JSON.parse(response_raw);
    if(response.status!="OK") {
      alert(response.mensaje);
      return false;
    } else {
      window.location.href = "./?s=tareas-realizadas&msg=3&id_tareas=" + response.obj.id;
    }
  }).fail(function(){
    alert("No funciono");
  });

});

$(document).on('click','.sort',function(e) {

  if(order == "asc") {
    order = "desc";
  } else {
    order = "asc";
  }

  window.location.href = "./?s=tareas-realizadas&order_by=" + $(e.currentTarget).data('sortorderby') + "&order=" + order;

});



$(document).on('click','.tareas-pendientes-checkbox',function(e){

  e.stopPropagation();

  change_checkbox_pendientes = [];
  total = 0;

  $('.tareas-pendientes-checkbox').each(function(){
    if($(this).is(':checked')){
      total += 1;
      change_checkbox_pendientes.push($(this).data('idtareas'));
    }
  })
  $('#tareas_checkbox_total').html(total);
  $('#accion-masiva-eliminar-modal-total').html(total);

});



$(document).on('click','.accion-masiva',function(e){

  if(change_checkbox_pendientes.length == 0) {
    return 0;
  }

  var url = "./ajax/ajax_accionMasiva.php";
  var data = {
    'table_name': 'tareas',
    'accion': 'tareas-marcar-como',
    'ids': change_checkbox_pendientes,
    'estado': $(e.currentTarget).data('estado')
  };

  console.log(data);
  

  $.post(url,data,function(response_raw){
    console.log(response_raw);
    var response = JSON.parse(response_raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=tareas-realizadas&msg=3";
    }
  }).fail(function(){
    alert("No funciono");
  });


});

</script>
