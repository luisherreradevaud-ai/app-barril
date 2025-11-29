<?php

  $productos_barriles = Producto::getAll("WHERE tipo='Barril' ORDER BY nombre asc");
  $productos_cajas = Producto::getAll("WHERE tipo='Caja' ORDER BY nombre asc");

?>
<style>
.tr-productos {
  cursor: pointer;
}
.nav-tabs .nav-link.active {
  font-weight: bold;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-coins"></i> <b>Productos</b></h1>
  </div>
  <div>
    <div>
      <a href="./?s=nuevo-productos" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm mb-2"><i class="fas fa-fw fa-plus"></i> Nuevo Producto</a>
    </div>
  </div>
</div>
<hr />
<?php
Msg::show(2,'Producto eliminado con &eacute;xito','danger');
?>

<!-- Tabs para tipo de producto -->
<ul class="nav nav-tabs mb-3" id="productos-tabs" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="barriles-tab" data-bs-toggle="tab" data-bs-target="#barriles-content" type="button" role="tab">
      <i class="fas fa-fw fa-beer"></i> Barriles (<?= count($productos_barriles); ?>)
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="cajas-tab" data-bs-toggle="tab" data-bs-target="#cajas-content" type="button" role="tab">
      <i class="fas fa-fw fa-box"></i> Cajas (<?= count($productos_cajas); ?>)
    </button>
  </li>
</ul>

<div class="tab-content" id="productos-tabs-content">
  <!-- Tab Barriles -->
  <div class="tab-pane fade show active" id="barriles-content" role="tabpanel">
    <table class="table table-hover table-striped table-sm mb-4" id="productos-barriles-table">
      <thead class="thead-dark">
        <tr>
          <th>Nombre</th>
          <th>Clasificaci&oacute;n</th>
          <th>Cantidad</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($productos_barriles as $producto) { ?>
        <tr class="tr-productos" data-idproductos="<?= $producto->id; ?>">
          <td><?= $producto->nombre; ?></td>
          <td><?= $producto->clasificacion; ?></td>
          <td><?= $producto->cantidad; ?></td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>

  <!-- Tab Cajas -->
  <div class="tab-pane fade" id="cajas-content" role="tabpanel">
    <table class="table table-hover table-striped table-sm mb-4" id="productos-cajas-table">
      <thead class="thead-dark">
        <tr>
          <th>Nombre</th>
          <th>Clasificaci&oacute;n</th>
          <th>Tipo Envase</th>
          <th>Formato</th>
          <th>Envases</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($productos_cajas as $producto) {
          $formato = $producto->getFormatoDeEnvases();
        ?>
        <tr class="tr-productos" data-idproductos="<?= $producto->id; ?>">
          <td><?= $producto->nombre; ?></td>
          <td><?= $producto->clasificacion; ?></td>
          <td><?= $producto->tipo_envase; ?></td>
          <td><?= $formato ? $formato->nombre : '-'; ?></td>
          <td><?= $producto->cantidad_de_envases; ?></td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>

<script>

  // DataTable para Barriles
  new DataTable('#productos-barriles-table', {
    language: {
        url: '//cdn.datatables.net/plug-ins/2.1.3/i18n/es-CL.json'
    },
    pageLength: 50,
    stateSave: true,
    order: [[0, 'asc']]
  });

  // DataTable para Cajas
  new DataTable('#productos-cajas-table', {
    language: {
        url: '//cdn.datatables.net/plug-ins/2.1.3/i18n/es-CL.json'
    },
    pageLength: 50,
    stateSave: true,
    order: [[0, 'asc']]
  });

  $(document).on('click','.tr-productos',function(e){
    window.location.href = "./?s=detalle-productos&id=" + $(e.currentTarget).data('idproductos');
  })

</script>
