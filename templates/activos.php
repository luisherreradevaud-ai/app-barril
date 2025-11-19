<?php

  $usuario = $GLOBALS['usuario'];

  $clase_seleccionada = '';
  $activos_clases = Activo::getClases();

  $query = '';

  if($usuario->nivel != "Administrador" && $usuario->nivel != "Jefe de Planta") {
    $query .= 'WHERE id_usuarios_control='.$usuario->id;
  }

  if(isset($_GET['clase']) && in_array($_GET['clase'],$activos_clases)) {
    $clase_seleccionada = $_GET['clase'];
    if($query == '') {
      $query .= 'WHERE clase="'.$clase_seleccionada.'"';
    } else {
      $query .= 'AND clase="'.$clase_seleccionada.'"';
    }
  }

  $activos = Activo::getAll($query);

  
?>
<style>
.tr-activos {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-industry"></i> <b>Control de Activos</b></h1>
  </div>
  <?php
    if($usuario->nivel == "Administrador" || $usuario->nivel == "Jefe de Planta") {
      ?>
        <a href="./?s=nuevo-activos" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm mb-2"><i class="fas fa-fw fa-plus"></i> Nuevo Activo</a>
      <?php
    }
    ?>
</div>
<div class="d-flex justify-content-start w-100 mb-4"  style="overflow-x: scroll">
<?php

  foreach($activos_clases as $clase) {
    ?>
    <a  href="./?s=activos&clase=<?= $clase; ?>" class="btn btn-outline-secondary btn-sm me-2 <?= ($clase_seleccionada == $clase) ? 'active' : ''; ?>">
      <?= $clase; ?>
    </a>
    <?php
  }

?>
</div>
<?php 
  Msg::show(2,'Activo eliminado con &eacute;xito','danger');
?>
<table class="table table-hover table-striped table-sm" id="objs-table">
  <thead class="thead-dark">
    <tr>
      <th>
        Clase
      </th>
      <th>
        Código
      </th>
      <th>
        Ubicaci&oacute;n
      </th>
      <th>
      </th>
    </tr>
  </thead>
  <tbody>
    <?php
      $total = 0;
      foreach($activos as $activo) {
        $ubicacion = $activo->ubicacion;
        if($ubicacion == "En terreno") {
          $cliente = new Cliente($activo->id_clientes_ubicacion);
          $ubicacion .= ": ".$cliente->nombre;
        } else
        if($ubicacion == "En planta") {
          $locacion = new Locacion($activo->id_locaciones);
          $ubicacion .= ": ".$locacion->nombre;
        }
    ?>
    <tr class="tr-activos" data-idactivos="<?= $activo->id; ?>">
      <td>
        <?= $activo->clase; ?>
      </td>
      <td>
        <?= $activo->codigo; ?>
      </td>
      <td>
        <?= $ubicacion; ?>
      </td>
      <td class="text-end">
        <div class="dropdown">
          <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            Acciones
          </button>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="./?s=nuevo-mantenciones&id_activos=<?= $activo->id; ?>">Realizar Mantenci&oacute;n</a></li>
            <li><a class="dropdown-item" href="./?s=ficha-activos&id=<?= $activo->id; ?>">Ver Ficha</a></li>
            <li><a class="dropdown-item" href="./?s=detalle-activos&id=<?= $activo->id; ?>">Editar</a></li>
          </ul>
        </div>
      </td>
    </tr>
    <?php
      }
    ?>
  </tbody>
</table>
<script>

new DataTable('#objs-table', {
    language: {
        url: '//cdn.datatables.net/plug-ins/2.1.3/i18n/es-CL.json'
    },
    pageLength: 50,
    stateSave: true,
    "order": [[2, "desc"]],
    
});

</script>
        