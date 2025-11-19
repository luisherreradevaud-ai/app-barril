<?php

  //checkAutorizacion(["Administrador"]);
  $usuario = $GLOBALS['usuario'];
  $sugerencias = Sugerencia::getAll("ORDER BY id desc");

?>
<style>
.tr-sugerencias {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-truck"></i> <b>Sugerencias y Reclamos</b></h1>
  </div>
</div>
<hr />
<?php
  foreach($sugerencias as $sugerencia) {
    $usuario_2 = new Usuario($sugerencia->id_usuarios);
    $cliente = new Cliente($sugerencia->id_clientes);
?>
<div class="card w-100 shadow mb-5">
  <div class="card-body">
    <h5 class="card-title mb-3 h5" style="text-transform: uppercase"><?= $sugerencia->tipo; ?> #<?= $sugerencia->id; ?></h5>
    <table>
      <tr>
        <td>
          Cliente:
        </td>
        <td>
          <b>
            <?= $cliente->nombre; ?>
          </b>
        </td>
      </tr>
      <tr>
        <td>
          Usuario:
        </td>
        <td>
          <b>
            <?= $usuario_2->nombre; ?>
          </b>
        </td>
      </tr>
      <tr>
        <td>
          Creado:
        </td>
        <td>
          <b>
            <?= datetime2fechayhora($sugerencia->creada); ?>
          </b>
        </td>
      </tr>
    </table>
    <br />
    Contenido:<br/><b><?= $sugerencia->contenido; ?></b>
    <br />
    <br />
  </div>
</div>
<?php
  }
?>
<script>

$(document).on('click','.tr-sugerencias',function(e){
  window.location.href = "./?s=detalle-sugerencias&id=" + $(e.currentTarget).data('idsugerencias');
});

</script>
