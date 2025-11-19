<?php

  $productos_barriles = Producto::getAll("WHERE tipo='Barril' ORDER BY tipo asc, nombre asc");
  $productos_24 = Producto::getAll("WHERE tipo='Caja' AND cantidad='24' ORDER BY tipo asc, nombre asc");
  $productos_tripack = Producto::getAll("WHERE tipo='Caja' AND cantidad='Tripack' ORDER BY tipo asc, nombre asc");
  $productos = Producto::getAll("order by tipo asc, nombre asc");

?>
<style>
.tr-productos {
  cursor: pointer;
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
<table class="table table-hover table-striped table-sm mb-4" id="objs-table">
  <thead class="thead-dark">
    <tr>
      <th>
        Nombre
      </th>
      <th>
        Clasificaci&oacute;n
      </th>
      <th>
        Cantidad
      </th>
      <th>
        Tipo
      </th>
    </tr>
  </thead>
  <tbody>
    <?php
      foreach($productos as $producto) {
    ?>
    <tr class="tr-productos" data-idproductos="<?= $producto->id; ?>">
     
      <td>
        <?= $producto->nombre; ?>
      </td>

      <td>
        <?= $producto->clasificacion; ?>
      </td>

      <td>
        <?= $producto->cantidad; ?>
      </td>

      <td>
        <?= $producto->tipo; ?>
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
    order: [[2, 'desc']]
  });

  $(document).on('click','.tr-productos',function(e){
    window.location.href = "./?s=detalle-productos&id=" + $(e.currentTarget).data('idproductos');
  })

</script>
