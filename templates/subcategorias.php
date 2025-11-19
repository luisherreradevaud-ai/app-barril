<?php

  $subcategorias = SubCategoria::getAll();

?>
<style>
.tr-subcategorias {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-coins"></i> <b>SubCategorias</b></h1>
  </div>
  <div>
    <div>
      <a href="./?s=nuevo-subcategorias" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm mb-2"><i class="fas fa-fw fa-plus"></i> Nueva SubCategoria</a>
    </div>
  </div>
</div>
<hr />
<table class="table table-hover table-striped table-sm">
  <thead class="thead-dark">
    <tr>
      <th>
        Nombre
      </th>
      <th>
        Categoria
      </th>
      <th>
        Visible
      </th>
    </tr>
  </thead>
  <tbody>
    <?php
      foreach($subcategorias as $subcategoria) {
        $categoria = new Categoria($subcategoria->id_categorias);
    ?>
    <tr class="tr-subcategorias" data-idsubcategorias="<?= $subcategoria->id; ?>">
      <td>
        <?= $subcategoria->nombre; ?>
      </td>
      <td>
        <?= $categoria->nombre; ?>
      </td>
      <td>
        <?php
          print $subcategoria->visible ? "Si" : "No";
        ?>
      </td>
    </tr>
    <?php
      }
    ?>
  </tbody>
</table>
<script>
$(document).on('click','.tr-subcategorias',function(e){
  window.location.href = "./?s=detalle-subcategorias&id=" + $(e.currentTarget).data('idsubcategorias');
})
</script>
