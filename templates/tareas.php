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
      $tareas_pendientes = Tarea::getAll("WHERE ((tipo_envio='Usuario' AND destinatario='".$usuario->id."') OR (tipo_envio='Nivel' AND destinatario='".$usuario->nivel."')) AND (estado='Pendiente' OR estado='Recibida') ORDER BY importancia desc, id desc");
    } else 
    if($order_by == "emisor") {
      $tareas_pendientes = Tarea::getAll("INNER JOIN usuarios ON tareas.id_usuarios_emisor = usuarios.id WHERE ((tareas.tipo_envio='Usuario' AND tareas.destinatario='".$usuario->id."') OR (tareas.tipo_envio='Nivel' AND tareas.destinatario='".$usuario->nivel."')) AND (tareas.estado='Pendiente' OR tareas.estado='Recibida') ORDER BY usuarios.nombre ".$order);
    } else {
      $tareas_pendientes = Tarea::getAll("WHERE ((tipo_envio='Usuario' AND destinatario='".$usuario->id."') OR (tipo_envio='Nivel' AND destinatario='".$usuario->nivel."')) AND (estado='Pendiente' OR estado='Recibida') ORDER BY ".$order_by." ".$order);
    }

    if($order_by == "por defecto") {
      $tareas_enviadas = Tarea::getAll("WHERE id_usuarios_emisor='".$usuario->id."' AND estado!='Realizada' ORDER BY importancia desc, id desc");
    } else {
      $tareas_enviadas = Tarea::getAll("WHERE id_usuarios_emisor='".$usuario->id."' AND estado!='Realizada' ORDER BY ".$order_by." ".$order);
    }

?>
<style>
.tr-tareas {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-list"></i> <b>Tareas</b></h1>
  </div>
  <div>
    <a href="./?s=planificacion" class="d-sm-inline-block btn btn-secondary shadow-sm btn-sm"><i class="fas fa-fw fa-calendar"></i> Vista Planificaci&oacute;n</a>
  </div>
</div>
<a href="./?s=tareas" class="d-sm-inline-block btn btn-sm btn-secondary shadow-sm mb-2"><i class="fas fa-fw fa-list"></i> Pendientes</a>
<a href="./?s=tareas-realizadas" class="d-sm-inline-block btn btn-sm btn-secondary shadow-sm mb-2"><i class="fas fa-fw fa-folder"></i> Realizadas</a>
<a href="./?s=tareas-enviadas-y-archivadas" class="d-sm-inline-block btn btn-sm btn-secondary shadow-sm mb-2"><i class="fas fa-fw fa-save"></i> Enviadas y Archivadas</a>
<hr />
<?php 
Msg::show(1,'Tarea enviada con &eacute;xito','info');
Msg::show(2,'Tarea eliminada con &eacute;xito','danger');
Msg::show(3,'Tarea marcada como Recibida','info');
Msg::show(4,'Tarea marcada como Realizada','info');
?>
<h3 class="h5 mb-0 text-gray-800 mb-3">
  Recibidas pendientes (<?= (count($tareas_pendientes)); ?>)
</h3>
<table class="table table-hover table-striped table-sm" id="tareas-table">
  <thead class="thead-dark">
    <tr>
      <th style="width: 30px">
      </th>
      <th>
        <a href="#" class="sort" data-sortorderby="creada">Creada</a>
      </th>
      <th>
        <a href="#" class="sort" data-sortorderby="creada">Transcurrido</a>
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
        <a href="#" class="sort" data-sortorderby="estado">Estado</a>
      </th>
      <th>
      </th>
    </tr>
  </thead>
  <tbody>
    <?php
      foreach($tareas_pendientes as $tarea) {
        $usuario_emisor = new Usuario($tarea->id_usuarios_emisor);
        $tr_class = "";
        $badge = "";
        if($tarea->importancia == "URGENTE") {
          $tr_class = " table-danger";
          $badge = "<span class='badge bg-danger' style='color: white !important'>URGENTE</span>";
        }
        $date1 = strtotime(date('Y-m-d'));
        $date2 = strtotime(explode(' ',$tarea->creada)[0]);
        $transcurrido = floor(($date1 - $date2) / (60 * 60 * 24));

        $estado = $tarea->estado;

        if(floor((strtotime(date('Y-m-d')) - strtotime($tarea->plazo_maximo)) / (60 * 60 * 24)) > 0 ) {
          $estado = "Atrasada";
        }
    ?>
    <tr class="tr-tareas<?= $tr_class; ?>" data-idtareas="<?= $tarea->id; ?>">
      <td>
        <input class="tareas-pendientes-checkbox" type="checkbox" data-idtareas="<?= $tarea->id; ?>">
      <td>
        <?= datetime2fecha($tarea->creada); ?>
      </td>
      <td>
      <?= $transcurrido; ?> d&iacute;as
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
        <b>
          <?= $estado; ?>
        </b>
      </td>
      <td>
        <?php
          if($tarea->estado == "Pendiente") {
            ?>
            <a href="#" class="cambiar-estado-btn" data-idtareas="<?= $tarea->id; ?>" data-estado="Recibida">Marcar como Recibida</a>
            <?php
          } else 
          if($tarea->estado == "Recibida") {
            ?>
            <a href="#" class="cambiar-estado-btn" data-idtareas="<?= $tarea->id; ?>" data-estado="Realizada">Marcar como Realizada</a>
            <?php
          }
        ?>
      </td>
    </tr>
    <?php
      }
    ?>
  </tbody>
</table>

<div class="mt-3 mb-3" style="font-size: 0.8em">
  Selecci&oacute;n (<span id="tareas_checkbox_total">0</span>): <a class="btn btn-sm btn-secondary accion-masiva" href="#tareas_checkbox_total" data-estado="Recibida" data-accion="tareas-marcar-como">Marcar como Recibida</a> <a class="btn btn-sm btn-secondary accion-masiva" href="#tareas_checkbox_total" data-estado="Realizada" data-accion="tareas-marcar-como">Marcar como Realizada</a>
</div>

<div class="d-sm-flex align-items-center justify-content-between mt-5 mb-3">
  <h3 class="h5 mb-0 text-gray-800">
    Enviadas pendientes (<?= (count($tareas_enviadas)); ?>)
  </h3>
  <a href="./?s=nuevo-tareas" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i class="fas fa-fw fa-plus"></i> Nueva Tarea</a>
</div>
<?php 
Msg::show(4,'Tarea enviada con &eacute;xito','info');
Msg::show(5,'Tareas eliminadas con &eacute;xito','danger');
Msg::show(6,'Tareas enviadas con &eacute;xito','info');
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
        <a href="#" class="sort" data-sortorderby="creada">Transcurrido</a>
      </th>
      <th>
        <a href="#" class="sort" data-sortorderby="plazo_maximo">Plazo m&aacute;ximo</a>
      </th>
      <th style="width: 40%">
        Tarea
      </th>
      <th>
        <a href="#" class="sort" data-sortorderby="destinatario">Destinatario</a>
      </th>
      <th>
        <a href="#" class="sort" data-sortorderby="estado">Estado</a>
      </th>
    </tr>
  </thead>
  <tbody>
    <?php
      foreach($tareas_enviadas as $tarea) {
        if($tarea->tipo_envio == "Usuario") {
            $usuario_destinatario = new Usuario($tarea->destinatario);
            $destinatario = $usuario_destinatario->nombre;
        } else
        if($tarea->tipo_envio == "Nivel") {

            $destinatario = "Nivel: <b>".$tarea->destinatario."</b>";
        }
        $tr_class = "";
        $badge = "";
        if($tarea->importancia == "URGENTE") {
          $tr_class = " table-danger";
          $badge = "<span class='badge bg-danger' style='color: white !important'>URGENTE</span>";
        }
        $date1 = strtotime(date('Y-m-d'));
        $date2 = strtotime(explode(' ',$tarea->creada)[0]);
        $transcurrido = floor(($date1 - $date2) / (60 * 60 * 24));

        $estado = $tarea->estado;

        if((floor((strtotime(date('Y-m-d')) - strtotime($tarea->plazo_maximo)) / (60 * 60 * 24)) > 0 ) && $estado!= "Realizada") {
          $estado = "Atrasada";
        }
    ?>
    <tr class="tr-tareas<?= $tr_class; ?>" data-idtareas="<?= $tarea->id; ?>">
      <td>
        <input class="tareas-enviadas-checkbox" type="checkbox" data-idtareas="<?= $tarea->id; ?>">
      </td>
      <td>
        <?= datetime2fecha($tarea->creada); ?>
      </td>
      <td>
        <?= $transcurrido; ?> d&iacute;as
      </td>
      <td>
        <?= datetime2fecha($tarea->plazo_maximo); ?>
      </td>
      <td>
        <?= $badge; ?> <b style="color: black"><?= $tarea->tarea; ?></b>
      </td>
      <td>
        <?= $destinatario; ?>
      </td>
      <td>
        <b>
          <?= $estado; ?>
        </b>
      </td>
    </tr>
    <?php
      }
    ?>
  </tbody>
</table>
<div class="mt-3 mb-3" style="font-size: 0.8em">
  Selecci&oacute;n (<span id="tareas_checkbox_enviadas_total">0</span>): <a class="btn btn-sm btn-danger" href="#tareas_checkbox_total_enviadas" id="eliminar-btn">Eliminar</a>
</div>

<script>

var order_by = "<?= $order_by; ?>";
var order = "<?= $order; ?>";
var change_checkbox_pendientes = [];
var change_checkbox_enviadas = [];

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
      var msg = 3;
      if(response.obj.estado == "Realizada") {
        msg = 4;
      }
      window.location.href = "./?s=tareas&msg=" + msg + "&id_tareas=" + response.obj.id;
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

  window.location.href = "./?s=tareas&order_by=" + $(e.currentTarget).data('sortorderby') + "&order=" + order;

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

$(document).on('click','.tareas-enviadas-checkbox',function(e){

  e.stopPropagation();

  change_checkbox_enviadas = [];
  total = 0;

  $('.tareas-enviadas-checkbox').each(function(){
    if($(this).is(':checked')){
      total += 1;
      change_checkbox_enviadas.push($(this).data('idtareas'));
    }
  })
  $('#tareas_checkbox_enviadas_total').html(total);
  //$('#accion-masiva-eliminar-modal-total').html(total);

});

$(document).on('click','#eliminar-btn',function(e){

  if(change_checkbox_enviadas.length == 0) {
    return 0;
  }

  var url = "./ajax/ajax_accionMasiva.php";
  var data = {
    'table_name': 'tareas',
    'ids': change_checkbox_enviadas,
    'accion': 'eliminar'
  };

  console.log(data);

  $.post(url,data,function(response_raw){
    console.log(response_raw);
    var response = JSON.parse(response_raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=tareas&msg=5";
    }
  }).fail(function(){
    alert("No funciono");
  });


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
      window.location.href = "./?s=tareas&msg=3";
    }
  }).fail(function(){
    alert("No funciono");
  });


});


</script>
