<?php
    $documentos_por_aprobar = Documento::getAll("WHERE estado='Por Aprobar' ORDER BY id desc");
    $documentos_aprobados = Documento::getAll("WHERE estado='Aprobado' ORDER BY id desc");
    $documentos_rechazados = Documento::getAll("WHERE estado='Rechazado' ORDER BY id desc");
    $usuario = $GLOBALS['usuario'];

?>
<style>
.tr-documentos {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><b>Documentos</b></h1>
  </div>
  <a href="./?s=nuevo-documentos" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i class="fas fa-fw fa-plus"></i> Ingresar Documento</a>
</div>
<hr />
<?php 

    Msg::show(1,'Documento <b>Aprobado</b> con &eacute;xito','success');
    Msg::show(2,'Documento eliminado con &eacute;xito','danger');
    Msg::show(3,'Documento <b>Rechazado</b> con &eacute;xito','danger');

    if(count($documentos_por_aprobar)>0) {
?>

<div class="card">
  <div class="card-header">
    <h5 class="card-title">(<?= count($documentos_por_aprobar); ?>) Documentos Por Aprobar</h5>
  </div>
  <div class="table-responsive">
    <table class="table table-hover table-striped table-sm mt-2 mb-5" id="documentos-table">
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
            <th>
            </th>
        </tr>
      </thead>
      <tbody>
        <?php
          $total = 0;
          foreach($documentos_por_aprobar as $documento) {
            $cliente = new Cliente($documento->id_clientes);
        ?>
        <tr class="tr-documentos" data-iddocumentos="<?= $documento->id; ?>">
            <td>
                <?= $documento->id; ?>
            </td>
            <td>
                <?= $documento->creada; ?>
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
            <td width="200" class="text-end">
            <?php
            if($usuario->nivel == "Administrador") {
            ?>
                <button class="btn btn-sm btn-success aprobar-documento-pago-btn" data-iddocumentos="<?= $documento->id; ?>" data-accion="Aprobado"><i class="fas fa-fw fa-check"></i> Aprobar</button>
                <button class="btn btn-sm btn-danger aprobar-documento-pago-btn" data-iddocumentos="<?= $documento->id; ?>" data-accion="Rechazado">Rechazar</button>
            <?php
            }
            ?>
            </td>
        </tr>
        <?php
          }
        ?>
      </tbody>
    </table>
    <br/>
  </div>
</div>
    <?php
        }
    ?>



<div class="card">
  <div class="card-header">
    <h5 class="card-title">Documentos Aprobados</h5>
  </div>
  <div class="table-responsive">
    <table class="table table-hover table-striped table-sm mt-2 mb-5" id="documentos-aprobados-table">
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
          foreach($documentos_aprobados as $documento) {
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
    <br/>
  </div>
</div>


<div class="card">
  <div class="card-header">
    <h5 class="card-title">Documentos Rechazados</h5>
  </div>
  <div class="table-responsive">
    <table class="table table-hover table-striped table-sm mt-2 mb-5" id="documentos-rechazados-table">
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
          foreach($documentos_rechazados as $documento) {
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
    <br/>
  </div>
</div>


<script>

new DataTable('#documentos-table', {
    language: {
        url: '//cdn.datatables.net/plug-ins/2.1.3/i18n/es-CL.json'
    },
    pageLength: 50,
    stateSave: true,
    "order": [[2, "desc"]]
});

new DataTable('#documentos-aprobados-table', {
    language: {
        url: '//cdn.datatables.net/plug-ins/2.1.3/i18n/es-CL.json'
    },
    pageLength: 50,
    stateSave: true,
    "order": [[2, "desc"]]
});

new DataTable('#documentos-rechazados-table', {
    language: {
        url: '//cdn.datatables.net/plug-ins/2.1.3/i18n/es-CL.json'
    },
    pageLength: 50,
    stateSave: true,
    "order": [[2, "desc"]]
});

$(document).on('click','.tr-documentos',function(e) {
    window.location.href = "./?s=detalle-documentos&id=" + $(e.currentTarget).data('iddocumentos');
});

$(document).on('click','.aprobar-documento-pago-btn',function(e){

  e.preventDefault();
  e.stopPropagation();

  var url = "./ajax/ajax_aprobarDocumentoPago.php";
  var data = {
    'id': $(e.currentTarget).data('iddocumentos'),
    'accion': $(e.currentTarget).data('accion')
  };

  $.post(url,data,function(raw){
    console.log(raw);
    var response = JSON.parse(raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      if(response.obj.estado == "Aprobado") {
        window.location.href = "./?s=documentos&id=" + response.obj.id + "&msg=1";
      }
      if(response.obj.estado == "Rechazado") {
        window.location.href = "./?s=documentos&id=" + response.obj.id + "&msg=3";
      }
      
    }
  }).fail(function(){
    alert("No funciono");
  });

});
</script>
