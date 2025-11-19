<?php
    
    //checkAutorizacion(["Jefe de Planta","Administrador","Jefe de Cocina","Operario","Repartidor"]);

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

    $usuario = $GLOBALS['usuario'];

    if($usuario->nivel != "Administrador" && $usuario->nivel != "Jefe de Planta") {
      $activos_inspecciones = Activo::getAll("WHERE id_usuarios_control='".$usuario->id."' AND inspeccion_periodicidad!='Inmediata Tras Uso' AND (proxima_inspeccion BETWEEN '".$ano."-".$mes."-01' AND '".$ano."-".$mes."-31')");
      $activos_mantenciones = Activo::getAll("WHERE id_usuarios_control='".$usuario->id."' AND mantencion_periodicidad!='Inmediata Tras Uso' AND (proxima_mantencion BETWEEN '".$ano."-".$mes."-01' AND '".$ano."-".$mes."-31')");
    } else {
      $activos_inspecciones = Activo::getAll("WHERE inspeccion_periodicidad!='Inmediata Tras Uso' AND (proxima_inspeccion BETWEEN '".$ano."-".$mes."-01' AND '".$ano."-".$mes."-31')");
      $activos_mantenciones = Activo::getAll("WHERE mantencion_periodicidad!='Inmediata Tras Uso' AND (proxima_mantencion BETWEEN '".$ano."-".$mes."-01' AND '".$ano."-".$mes."-31')");
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
    <h1 class="h4 mb-0 text-gray-800"><b>Por Realizar</b></h1>
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
      for($i = 2023; $i<=date('Y')+1; $i++) {
        print "<option>".$i."</option>";
      }
      ?>
    </select>
  </div>
</div>
<hr />
<table class="table table-hover table-striped table-sm" id="insumos-table">
  <thead class="thead-dark">
    <tr>
      <th>
      <a href="#" class="sort" data-show="insumos" data-sort="nombre">Fecha</a>
      </th>
      <th>
      <a href="#" class="sort" data-show="insumos" data-sort="nombre">Tarea</a>
      </th>
      <th>
      <a href="#" class="sort" data-show="insumos" data-sort="nombre">Nombre</a>
      </th>
      <th>
      <a href="#" class="sort" data-show="insumos" data-sort="tipos_de_insumos">Marca</a>
      </th>
      <th>
      <a href="#" class="sort" data-show="insumos" data-sort="tipos_de_insumos">Modelo</a>
      </th>
      <th>
      <a href="#" class="sort" data-show="insumos" data-sort="cantidad">C&oacute;digo</a>
      </th>
      <th>
      <a href="#" class="sort" data-show="insumos" data-sort="monto">Clasificaci&oacute;n</a>
      </th>
      <th>
      </th>
    </tr>
  </thead>
  <tbody>
    <?php
      $total = 0;
      foreach($activos_inspecciones as $activo) {
    ?>
    <tr>
      <td>
        <?= date2fecha($activo->proxima_inspeccion); ?>
      </td>
      <td>
        Inspecci&oacute;n
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
      <td>
        <a href="./?s=nuevo-mantenciones&tarea=inspeccion&id_activos=<?= $activo->id; ?>" class="btn btn-primary btn-sm">Realizar Mantenci&oacute;n</button>
      </td>
    </tr>
    <?php
      }
    ?>
    <?php
      $total = 0;
      foreach($activos_mantenciones as $activo) {
    ?>
    <tr>
      <td>
        <?= date2fecha($activo->proxima_inspeccion); ?>
      </td>
      <td>
        Inspecci&oacute;n
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
      <td>
        <a href="./?s=nuevo-mantenciones&tarea=mantencion&id_activos=<?= $activo->id; ?>" class="btn btn-primary btn-sm">Realizar Mantenci&oacute;n</button>
      </td>
    </tr>
    <?php
      }
    ?>
  </tbody>
</table>

<script>

$(document).on('change','.dates-select', function(e) {
  window.location.href = "./?s=mantenciones-por-realizar&mes=" + $('#mes-select').val() + "&ano=" + $('#ano-select').val();
});

$(document).ready(function(){
  $('#mes-select').val(<?= $mes; ?>);
  $('#ano-select').val(<?= $ano; ?>);
});

</script>
