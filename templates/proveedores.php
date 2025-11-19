<?php

  //checkAutorizacion(["Administrador"]);

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
    if($_GET['order_by'] == "email") {
      $order_by = "email";
    } else 
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
  $proveedores = Proveedor::getAll($query);
  $proveedores_total = Proveedor::getAll();

  $pages = count($proveedores_total) / 20;

?>
<style>
.tr-proveedores {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-handshake"></i> <b>Proveedores</b></h1>
  </div>
  <div>
    <div>
      <a href="./?s=nuevo-proveedores" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm mb-2"><i class="fas fa-fw fa-plus"></i> Nuevo Proveedor</a>
    </div>
  </div>
</div>
<hr />
<?php
if($msg == 2) {
?>
<div class="alert alert-danger" role="alert" >Proveedor eliminado.</div>
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
      <th>
        <a href="./?s=<?= $section; ?>&order_by=email&order=<?= $order_2; ?>&page=<?= $page; ?>" style="color: white !important">
          Email
        </a>
      </th>
      <th>
        Telefono
      </th>
    </tr>
  </thead>
  <tbody>
    <?php
      foreach($proveedores as $proveedor) {
    ?>
    <tr class="tr-proveedores" data-idproveedores="<?= $proveedor->id; ?>">
      <td>
        <?= $proveedor->nombre; ?>
      </td>
      <td>
        <?= $proveedor->email; ?>
      </td>
      <td>
        <?= $proveedor->telefono; ?>
      </td>
    </tr>
    <?php
      }
    ?>
  </tbody>
</table>
<div class="d-flex justify-content-between mt-4 mb-5">
  <div>
    <b>Mostrando <?= count($proveedores); ?> de <?= count($proveedores_total); ?></b>
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
$(document).on('click','.tr-proveedores',function(e){
  window.location.href = "./?s=detalle-proveedores&id=" + $(e.currentTarget).data('idproveedores');
});
$(document).on('change','.page-select',function(e){
  window.location.href = "./?s=<?= $section; ?>&order_by=<?= $order_by; ?>&order=<?= $order; ?>&page=" + $(e.currentTarget).val();
});
</script>
