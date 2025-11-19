<?php


  //checkAutorizacion("Administrador");

  $msg = 0;

  if(isset($_GET['msg'])) {
    $msg = $_GET['msg'];
  }

  $section = $_GET['s'];
  $order = "asc";
  $order_2 = "desc";
  $order_by = "id";
  $offset = "0";
  $page = 0;

  if(isset($_GET['order_by'])) {

    if(isset($_GET['order'])) {
      if($_GET['order'] == "desc") {
        $order = "desc";
        $order_2 = "asc";
      }
    }
    if($_GET['order_by'] == "nombre") {
      $order_by = "nombre";
    }

    if(isset($_GET['page'])) {
      if(is_numeric($_GET['page'])) {
        $page = $_GET['page'];
        $offset = $page * 20;
      }
    }

  }
  $query = "ORDER BY ".$order_by." ".$order." LIMIT 20 OFFSET ".$offset;
  $tipos = TipoDeInsumo::getAll($query);
  $tipos_total = TipoDeInsumo::getAll();

  $pages = count($tipos_total) / 20;

?>
<style>
.tr-tipos {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-handshake"></i> <b>Tipos de Insumos</b></h1>
  </div>
  <?php
    Widget::printWidget("insumos-menu-btn");
  ?>
</div>
<hr />
<?php
if($msg == 2) {
?>
<div class="alert alert-danger" role="alert" >Tipo de Insumo eliminado.</div>
<?php
}
?>
<table class="table table-hover table-striped table-sm">
  <thead class="thead-dark">
    <tr>
      <th>
        <a href="./?s=<?= $section; ?>&order_by=nombre&order=<?= $order_2; ?>&page=<?= $page; ?>" style="color: white !important">
          Nombre
        </a>
      </th>
    </tr>
  </thead>
  <tbody>
    <?php
      foreach($tipos as $tipo) {
    ?>
    <tr class="tr-tipos" data-idtipos="<?= $tipo->id; ?>">
      <td>
        <?= $tipo->nombre; ?>
      </td>
    </tr>
    <?php
      }
    ?>
  </tbody>
</table>
<div class="d-flex justify-content-between mt-4 mb-5">
  <div>
    <b>Mostrando <?= count($tipos); ?> de <?= count($tipos_total); ?></b>
  </div>
  <div>
    <b>P&aacute;gina:</b> 
    <select class="form-control page-select" style="width: 80px; display: inline">
    <?php
      for($i=0; $i<$pages; $i++) {
        print "<option";
        if($page == $i) {
          print " SELECTED";
        }
        print ">".$i."</option>";
      }
    ?>
    </select>
  </div>
</div>
<script>
$(document).on('click','.tr-tipos',function(e){
  window.location.href = "./?s=detalle-tipos_de_insumos&id=" + $(e.currentTarget).data('idtipos');
});
$(document).on('change','.page-select',function(e){
  window.location.href = "./?s=<?= $section; ?>&order_by=<?= $order_by; ?>&order=<?= $order; ?>&page=" + $(e.currentTarget).val();
});
</script>
