<?php
    
    //checkAutorizacion(["Jefe de Planta","Administrador","Jefe de Cocina","Operario","Repartidor"]);

    $usuario = $GLOBALS['usuario'];

    $ano = date('Y');
    $mes = date('m');
  
    if(validaIdExists($_GET,'ano')) {
      $ano = $_GET['ano'];
    }
  
    if(validaIdExists($_GET,'mes')) {
      $mes = $_GET['mes'];
    }
  
    $date = date($ano."-".$mes.'-d');
  
    $datetime = new DateTime($date);
    $ano = $datetime->format('Y');
    $mes = $datetime->format('m');

    if($usuario->nivel != "Administrador" && $usuario->nivel != "Jefe de Planta") {
      $mantenciones = Mantencion::getAll("INNER JOIN activos ON mantenciones.id_activos = activos.id WHERE activos.id_usuarios_control='".$usuario->id."' AND date BETWEEN '".$ano."-".$mes."-01' AND '".$ano."-".$mes."-31'");
    } else {
      $mantenciones = Mantencion::getAll("WHERE date BETWEEN '".$ano."-".$mes."-01' AND '".$ano."-".$mes."-31'");
    }

?>
<style>
.tr-mantenciones {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><b>Mantenciones</b></h1>
    <h1 class="h4 mb-0 text-gray-800"><b>Realizadas</b></h1>
  </div>
    <a href="./?s=nuevo-mantenciones" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm mb-2"><i class="fas fa-fw fa-plus"></i> Nueva Mantencion</a>

</div>
<?php Widget::printWidget("activos-menu"); ?>
<div class="d-sm-flex mb-3">
  <div>
    <select class="form-control dates-select" id="mes-select">
      <?php
      for($i = 1; $i<=12; $i++) {
        print "<option value='".$i."'>".int2mes($i)."</option>";
      }
      ?>
    </select>
  </div>
  <div>
    <select class="form-control dates-select" id="ano-select">
      <?php
      for($i = 2023; $i<=date('Y'); $i++) {
        print "<option>".$i."</option>";
      }
      ?>
    </select>
  </div>
</div>
<hr />
<?php 
  Msg::show(2,'Mantenci&oacute;n eliminada con &eacute;xito','danger');
?>
<table class="table table-hover table-striped table-sm" id="mantenciones-table">
  <thead class="thead-dark">
    <tr>
      <th>
        ID
      </th>
      <th>
        Fecha
      </th>
      <th>
        Tarea
      </th>
      <th>
        Nombre
      </th>
      <th>
        Marca
      </th>
      <th>
        Modelo
      </th>
      <th>
        C&oacute;digo
      </th>
      <th>
        Clasificaci&oacute;n
      </th>
    </tr>
  </thead>
  <tbody>
    <?php
      $total = 0;
      foreach($mantenciones as $mantencion) {
        $activo = new Activo($mantencion->id_activos);
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
        <?= $activo->nombre; ?>
      </td>
      <td>
        <?= $activo->marca; ?>
      </td>
      <td>
        <?= $activo->modelo; ?>
      </td>
      <td>
        <?= $activo->codigo; ?>
      </td>
      <td>
        <?= $activo->clasificacion; ?>
      </td>
    </tr>
    <?php
      }
    ?>
  </tbody>
</table>

<script>

new DataTable('#mantenciones-table', {
    language: {
        url: '//cdn.datatables.net/plug-ins/2.1.3/i18n/es-CL.json'
    },
    pageLength: 50,
    stateSave: true,
    paging: false
});

$(document).on('click','.tr-mantenciones',function(e) {
    window.location.href = "./?s=detalle-mantenciones&id=" + $(e.currentTarget).data('idmantenciones');
});

$(document).on('change','.dates-select', function(e) {
  window.location.href = "./?s=mantenciones&mes=" + $('#mes-select').val() + "&ano=" + $('#ano-select').val();
});

$(document).ready(function(){
  $('#mes-select').val(<?= $mes; ?>);
  $('#ano-select').val(<?= $ano; ?>);
});

</script>
