<?php
    
    //checkAutorizacion(["Cliente"]);

    $usuario = $GLOBALS['usuario'];
    $cliente = new Cliente($usuario->id_clientes);

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

    $mantenciones = Mantencion::getAll("INNER JOIN activos ON mantenciones.id_activos = activos.id WHERE activos.ubicacion='En terreno' AND activos.id_clientes_ubicacion='".$cliente->id."' AND mantenciones.date BETWEEN '".$ano."-".$mes."-01' AND '".$ano."-".$mes."-31'");

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
</div>
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
    </tr>
  </thead>
  <tbody>
    <?php
      $total = 0;
      foreach($mantenciones as $mantencion) {
        $activo = new Activo($mantencion->id_activos);
    ?>
    <tr>
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

$(document).on('change','.dates-select', function(e) {
  window.location.href = "./?s=central-clientes-mantenciones&mes=" + $('#mes-select').val() + "&ano=" + $('#ano-select').val();
});

$(document).ready(function(){
  $('#mes-select').val(<?= $mes; ?>);
  $('#ano-select').val(<?= $ano; ?>);
});

</script>
