<?php

  //checkAutorizacion(["Jefe de Planta","Administrador","Repartidor"]);

  $clientes = Cliente::getAll();
  $barriles_en_planta = Barril::getAll("WHERE clasificacion='CO2' AND estado='En planta'");
  $barriles_perdidos = Barril::getAll("WHERE clasificacion='CO2' AND estado='Perdido'");
  $usuario = $GLOBALS['usuario'];

?>
<style>
.tr-barriles {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-coins"></i> <b>CO2</b></h1>
  </div>
  <a href="./?s=devolucion-barriles" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm mb-2"><i class="fas fa-fw fa-download"></i> Devolver Barriles</a>
  <?php
  if($usuario->nivel!= "Repartidor") {
    ?>
    <a href="./?s=nuevo-barriles-co2" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm mb-2"><i class="fas fa-fw fa-plus"></i> Nuevo Barril CO2</a>
<?php
  }
  ?>
</div>
<hr />
<div class="row mb-5">
  <?php
    foreach($clientes as $cliente) {
        $barriles = Barril::getAll("WHERE estado='En terreno' AND id_clientes='".$cliente->id."' AND clasificacion='CO2' ORDER BY codigo asc");

  ?>
  <div class="col-md-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-3" id="cliente-<?= $cliente->id; ?>">
      <div class="mb-2">
        <h1 class="h4 mb-0 text-gray-800"><b><?= $cliente->nombre; ?></b> (<?= count($barriles); ?>)</h1>
      </div>
    </div>
    <table class="table table-hover table-striped table-sm mb-5">
      <thead class="thead-dark">
        <tr>
          <th>
              C&oacute;digo
          </th>
          <th>
              Fecha Entrega
          </th>
        </tr>
      </thead>
      <tbody>
        <?php
          foreach($barriles as $barril) {
            $ep = EntregaProducto::getAll("WHERE id_barriles='".$barril->id."' ORDER BY id desc LIMIT 1");
            if(count($ep)>0) {
              $entrega = new Entrega($ep[0]->id_entregas);
              $datetime_entrega = datetime2fecha($entrega->creada);
            } else {
              $datetime_entrega = "-";
            }
            
        ?>
        <tr class="tr-barriles" data-idbarriles="<?= $barril->id; ?>">
          <td>
            <?= $barril->codigo; ?>
          </td>
          <td>
            <?= $datetime_entrega; ?>
          </td>
        </tr>
        <?php
          }
        ?>
      </tbody>
    </table>
  </div>
  <?php
    }
  ?>
  <div class="col-md-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-3" id="cliente-">
      <div class="mb-2">
        <h1 class="h4 mb-0 text-gray-800"><b>En Planta</b> (<?= count($barriles_en_planta); ?>)</h1>
      </div>
    </div>
    <table class="table table-hover table-striped table-sm mb-5">
      <thead class="thead-dark">
        <tr>
          <th>
              C&oacute;digo
          </th>
        </tr>
      </thead>
      <tbody>
        <?php
          foreach($barriles_en_planta as $barril) {
        ?>
        <tr class="tr-barriles" data-idbarriles="<?= $barril->id; ?>">
          <td>
            <?= $barril->codigo; ?>
          </td>
        </tr>
        <?php
          }
        ?>
      </tbody>
    </table>
  </div>
  <div class="col-md-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-3" id="cliente-<?= $cliente->id; ?>">
      <div class="mb-2">
        <h1 class="h4 mb-0 text-gray-800"><b>Perdidos</b> (<?= count($barriles_perdidos); ?>)</h1>
      </div>
    </div>
    <table class="table table-hover table-striped table-sm mb-5">
      <thead class="thead-dark">
        <tr>
          <th>
              C&oacute;digo
          </th>
        </tr>
      </thead>
      <tbody>
        <?php
          foreach($barriles_perdidos as $barril) {
        ?>
        <tr class="tr-barriles" data-idbarriles="<?= $barril->id; ?>">
          <td>
            <?= $barril->codigo; ?>
          </td>
        </tr>
        <?php
          }
        ?>
      </tbody>
    </table>
  </div>
</div>
<script>

var usuario = <?= json_encode($usuario,JSON_PRETTY_PRINT); ?>;

$(document).on('click','.tr-barriles',function(e){
  if(usuario.nivel == "Repartidor") {
    return false;
  }
  window.location.href = "./?s=detalle-barriles-co2&id=" + $(e.currentTarget).data('idbarriles');
});
</script>