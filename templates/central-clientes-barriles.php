<?php

  //checkAutorizacion("Cliente");

  $usuario = $GLOBALS['usuario'];

  $cliente = new Cliente($usuario->id_clientes);
  $barriles = Barril::getAll("WHERE estado='En terreno' AND id_clientes='".$cliente->id."'");

?>
<style>
.tr-barriles {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-coins"></i> <b>Barriles Asignados</b></h1>
  </div>
</div>
<hr />

    <table class="table table-hover table-striped table-sm mb-5">
      <thead class="thead-dark">
        <tr>
          <th>
              C&oacute;digo
          </th>
          <th>
              Tipo
          </th>
          <th>
              Entrega
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
            <?= $barril->tipo_barril; ?>
          </td>
          <td>
            <?= $entrega->id; ?>
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