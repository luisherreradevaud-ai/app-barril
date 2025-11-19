<?php

  $msg = 0;

  if(isset($_GET['msg'])) {
    $msg = $_GET['msg'];
  }

  $vendedores = Usuario::getAll("WHERE nivel='Vendedor'");

?>
<style>
.tr-vendedores {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-handshake"></i> <b>Vendedores</b></h1>
  </div>
</div>
<hr />
<?php
if($msg == 2) {
?>
<div class="alert alert-danger" role="alert" >Vendedor eliminado.</div>
<?php
}
Widget::printWidget('usuarios-menu');

?>
<table class="table table-hover table-striped table-sm" id="objs-table">
  <thead>
    <tr>
      <th>
          Nombre
        </a>
      </th>
      <th>
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
      foreach($vendedores as $vendedor) {
    ?>
    <tr class="tr-vendedores" data-idvendedores="<?= $vendedor->id; ?>">
      <td>
        <?= $vendedor->nombre; ?>
      </td>
      <td>
        <?= $vendedor->email; ?>
      </td>
      <td>
        <?= $vendedor->telefono; ?>
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
    stateSave: true
});

$(document).on('click','.tr-vendedores',function(e){
  window.location.href = "./?s=detalle-vendedores&id=" + $(e.currentTarget).data('idvendedores');
});

</script>
