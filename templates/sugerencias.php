<?php

  $usuario = $GLOBALS['usuario'];
  $autorizados = ["Cliente"];
  if(!in_array($usuario->nivel,$autorizados)) {
    die();
  }

  $msg = 0;

  if(isset($_GET['msg'])) {
    $msg = $_GET['msg'];
  }

  $usuario = $GLOBALS['usuario'];
  $cliente = new Cliente($usuario->id_clientes);
  $sugerencias = Sugerencia::getAll("WHERE id_clientes='".$cliente->id."' ORDER BY id desc");

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
  <div>
    <a href="./?s=nuevo-sugerencias" class="btn btn-sm btn-primary shadow">
      <i class="fas fa-fw fa-plus"></i> Nueva</a>
  </div>
</div>
<hr />

<?php
  foreach($sugerencias as $sugerencia) {
    $usuario_2 = new Usuario($sugerencia->id_usuarios);
?>
<div class="card w-100 shadow mb-5">
  <div class="card-body">
    <h5 class="card-title mb-3 h5" style="text-transform: uppercase"><?= $sugerencia->tipo; ?></h5>
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
