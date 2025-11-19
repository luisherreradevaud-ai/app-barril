<?php
  $usuario = $GLOBALS['usuario'];

  $order_by = "por defecto";
  $order = "desc";

  if(isset($_GET['order_by'])) {
    if($_GET['order_by'] == "creada") {
      $order_by = "creada";
    } else
    if($_GET['order_by'] == "destinatario") {
      $order_by = "destinatario";
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
    $tareas = Tarea::getAll("WHERE id_usuarios_emisor='".$usuario->id."' AND estado!='Realizada' ORDER BY importancia desc, id desc");
  } else {
    $tareas = Tarea::getAll("WHERE id_usuarios_emisor='".$usuario->id."' AND estado!='Realizada' ORDER BY ".$order_by." ".$order);
  }
  
?>
<style>
.tr-tareas {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-save"></i> <b>Tareas Enviadas</b></h1>
  </div>
  <div>
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
Msg::show(3,'Tareas enviadas con &eacute;xito','info');
?>
<table class="table table-hover table-striped table-sm" id="tareas-table">
  <thead class="thead-dark">
    <tr>
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
      foreach($tareas as $tarea) {
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
<script>

var order_by = "<?= $order_by; ?>";
var order = "<?= $order; ?>";

$(document).on('click','.tr-tareas',function(e) {
    window.location.href = "./?s=detalle-tareas&id=" + $(e.currentTarget).data('idtareas');
});

$(document).on('click','.sort',function(e) {

  if(order == "asc") {
    order = "desc";
  } else {
    order = "asc";
  }

  window.location.href = "./?s=tareas-enviadas&order_by=" + $(e.currentTarget).data('sortorderby') + "&order=" + order;

});
</script>
