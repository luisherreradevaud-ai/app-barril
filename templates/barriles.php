<?php

  //checkAutorizacion(["Jefe de Planta","Administrador"]);

  $msg = 0;

  if(isset($_GET['msg'])) {
    $msg = $_GET['msg'];
  }

  $section = $_GET['s'];
  $order = "asc";
  $order_2 = "desc";
  $order_by = "estado";
  $offset = "0";
  $page = 0;

  if(isset($_GET['order_by'])) {

    if(isset($_GET['order'])) {
      if($_GET['order'] == "desc") {
        $order = "desc";
        $order_2 = "asc";
      }
    }
    if($_GET['order_by'] == "codigo") {
      $order_by = "codigo";
    } else 
    if($_GET['order_by'] == "tipo_barril") {
      $order_by = "tipo_barril";
    }

    if(isset($_GET['page'])) {
      if(is_numeric($_GET['page'])) {
        $page = $_GET['page'];
        $offset = $page * 20;
      }
    }

  }

  $query = "WHERE tipo_barril!='CO2' ORDER BY ".$order_by." ".$order." LIMIT 20 OFFSET ".$offset;
  $barriles = Barril::getAll($query);
  $barriles_total = Barril::getAll();

  $pages = count($barriles_total) / 20;

?>
<style>
.tr-barriles {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-coins"></i> <b>Barriles</b></h1>
  </div>
  <div>
    <div>
      <a href="./?s=detalle-barriles" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm mb-2"><i class="fas fa-fw fa-plus"></i> Nuevo Barril</a>
    </div>
  </div>
</div>
<a href="./?s=barriles-en-terreno" class="d-sm-inline-block btn btn-sm btn-secondary shadow-sm mb-2"><i class="fas fa-fw fa-industry"></i> Ver Barriles en Terreno</a>

<hr />
<?php
if($msg == 2) {
?>
<div class="alert alert-danger" role="alert" >Barril eliminado.</div>
<?php
}
?>
<table class="table table-hover table-striped table-sm">
  <thead class="thead-dark">
    <tr>
      <th>
        <a href="./?s=<?= $section; ?>&order_by=codigo&order=<?= $order_2; ?>&page=<?= $page; ?>" style="color: white !important">
          C&oacute;digo
        </a>
      </th>
      <th>
        <a href="./?s=<?= $section; ?>&order_by=tipo_barril&order=<?= $order_2; ?>&page=<?= $page; ?>" style="color: white !important">
          Tipo Barril
        </a>
      </th>
      <th>
      <a href="./?s=<?= $section; ?>&order_by=estado&order=<?= $order_2; ?>&page=<?= $page; ?>" style="color: white !important">
          Estado
        </a>
      </th>
    </tr>
  </thead>
  <tbody>
    <?php
      foreach($barriles as $barril) {
    ?>
    <tr class="tr-barriles" data-idbarriles="<?= $barril->id; ?>">
      <td>
        <?= $barril->codigo; ?>
      </td>
      <td>
        <?= $barril->tipo_barril; ?>
      </td>
      <td>
        <?php
        print $barril->estado;
        if($barril->estado == "En terreno") {
          $cliente = new Cliente($barril->id_clientes);
          print "<br/><b>".$cliente->nombre."</b>";
        }
        ?>
      </td>
    </tr>
    <?php
      }
    ?>
  </tbody>
</table>
<div class="d-flex justify-content-between mt-4 mb-5">
  <div>
    <b>Mostrando <?= count($barriles); ?> de <?= count($barriles_total); ?></b>
  </div>
  <div>
    <b>P&aacute;gina:</b> 
    <select class="form-control page-select" style="width: 80px; display: inline">
    <?php
      for($i=0; $i<$pages; $i++) {
        print "<option value='".$i."'";
        if($page == $i) {
          print " SELECTED";
        }
        print ">".($i+1)."</option>";
      }
    ?>
    </select>
  </div>
</div>
<script>

$(document).on('click','.tr-barriles',function(e){
  window.location.href = "./?s=detalle-barriles&id=" + $(e.currentTarget).data('idbarriles');
});
$(document).on('change','.page-select',function(e){
  window.location.href = "./?s=<?= $section; ?>&order_by=<?= $order_by; ?>&order=<?= $order; ?>&page=" + $(e.currentTarget).val();
});
</script>
