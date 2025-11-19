<?php
    $documentos = Documento::getAll("WHERE estado='Aprobado'");
    $usuario = $GLOBALS['usuario'];

?>
<style>
.tr-documentos {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-file"></i> <b>Documentos Aprobados</b></h1>
  </div>
    <?php $usuario->printReturnBtn(); ?>
</div>
<hr />
<?php 
    Msg::show(2,'Receta eliminada con &eacute;xito','danger');
?>
<?php
    Widget::printWidget('documentos-menu');
?>
<table class="table table-hover table-striped table-sm" id="documentos-table">
  <thead class="thead-dark">
    <tr>
        <th>
            # 
        </th>
        <th>
            Fecha y hora
        </th>
        <th>
            Folio
        </th>
        <th>
            Cliente
        </th>
        <th>
            Monto
        </th>
    </tr>
  </thead>
  <tbody>
    <?php
      $total = 0;
      foreach($documentos as $documento) {
        $cliente = new Cliente($documento->id_clientes);
    ?>
    <tr class="tr-documentos" data-iddocumentos="<?= $documento->id; ?>">
        <td>
            <?= $documento->id; ?>
        </td>
        <td>
            <?= datetime2fechayhora($documento->creada); ?>
        </td>
        <td>
            <?= $documento->folio; ?>
        </td>
        <td>
            <?= $cliente->nombre; ?>
        </td>
        <td>
            $<?= number_format($documento->monto); ?>
        </td>
    </tr>
    <?php
      }
    ?>
  </tbody>
</table>
<script>
$(document).on('click','.tr-documentos',function(e) {
    window.location.href = "./?s=detalle-documentos&id=" + $(e.currentTarget).data('iddocumentos');
});
</script>
