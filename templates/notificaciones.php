<?php

    $usuario = $GLOBALS['usuario'];
    $notificaciones = Notificacion::getAll("WHERE id_usuarios='".$usuario->id."' ORDER BY id desc");

?>
<style>
.tr-notificaciones {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-check"></i> <b>Notificaciones</b></h1>
  </div>
  <div>
  </div>
</div>
<hr />
<table class="table table-hover table-striped table-sm mb-4">
  <thead class="thead-dark">
    <tr>
      <th>
        Fecha y hora
      </th>
      <th>
        Notificaci&oacute;n
      </th>
    </tr>
  </thead>
  <tbody>
    <?php
      foreach($notificaciones as $notificacion) {
    ?>
    <tr>
        <td>
            <?= datetime2fechayhora($notificacion->creada); ?>
        </td>
        <td>
            <?= $notificacion->texto; ?>
        </td>
    </tr>
    <?php
      }
    ?>
  </tbody>
</table>
