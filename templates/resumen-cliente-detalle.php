<?php

$id = "";

if(validaIdExists($_GET,'id')) {
  $id = $_GET['id'];
}

$msg = 0;

if(isset($_GET['msg'])) {
  $msg = $_GET['msg'];
}

$ano = date('Y');
$mes = date('m');

if(validaIdExists($_GET,'ano')) {
  $ano = $_GET['ano'];
}

if(validaIdExists($_GET,'mes')) {
  $mes = $_GET['mes'];
}

$date = date($ano."-".$mes.'-d');

$datetime = new DateTime($date);
$ano = $datetime->format('Y');
$mes = $datetime->format('m');

$obj = new Cliente($id);

$entregas_anteriores_sin_pagar = Entrega::getAll("WHERE id_clientes='".$obj->id."'  AND creada < '".$ano."-".$mes."-01 00:00:00' AND estado!='Pagada' ORDER BY id asc");
$entregas = Entrega::getAll("WHERE id_clientes='".$obj->id."'  AND creada BETWEEN '".$ano."-".$mes."-01 00:00:00' AND '".$ano."-".$mes."-31 23:59:59' ORDER BY id asc");
$pagos = Pago::getAll("WHERE id_clientes='".$obj->id."'  AND creada BETWEEN '".$ano."-".$mes."-01 00:00:00' AND '".$ano."-".$mes."-31 23:59:59' ORDER BY id asc");

$usuario = $GLOBALS['usuario'];
?>
<style>
.tr-entregas {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800">
      <b>
        <?= $obj->nombre; ?>
      </b>
    </h1>
  </div>
  <div>
    <div>
      <?php $usuario->printReturnBtn(); ?>
    </div>
  </div>
</div>
<div class="d-sm-flex mb-3">
  <div>
    <select class="form-control" id="mes-select">
      <?php
      for($i = 1; $i<=12; $i++) {
        print "<option value='".$i."'>".int2mes($i)."</option>";
      }
      ?>
    </select>
  </div>
  <div>
    <select class="form-control" id="ano-select">
      <?php
      for($i = 2023; $i<=date('Y'); $i++) {
        print "<option>".$i."</option>";
      }
      ?>
    </select>
  </div>
</div>
<hr />
<div class="d-flex justify-content-between">
<h3>
  Detalle Entregas
</h3>
</div>
    <?php

    $total = 0;
    $adeudado = 0;

      foreach($entregas_anteriores_sin_pagar as $entrega) {

        if($entrega->monto == 0) {
          continue;
        }

        $adeudado += $entrega->monto - $entrega->abonado;
        $entregas_productos = EntregaProducto::getAll("WHERE id_entregas='".$entrega->id."'");
    ?>
    <div class="card shadow">
    <table class="table table-sm">
    <thead class="thead-dark">
        <tr>
        <th>
        <b>#<?= $entrega->id; ?></b>
        </th>

        </tr>
    </thead>
    </table>
    <table class="mt-1 ml-3">
        <tr>
            <td>
                Fecha:
            </td>
            <td>
                <b><?= datetime2fechayhora($entrega->creada); ?></b>
            </td>
        </tr>
        <tr>
            <td>
                Monto:
            </td>
            <td>
                <b>$<?= number_format($entrega->monto); ?></b>
            </td>
        </tr>
        <tr>
            <td>
                Adeudado:
            </td>
            <td>
                <b>$<?= number_format($entrega->monto - $entrega->abonado); ?></b>
            </td>
        <tr>
            <td>
                Estado:
            </td>
            <td>
                <b><?= $entrega->estado; ?>
            </td>
        </tr>
    </table>
    <table class="table mt-3">
        <?php
        foreach($entregas_productos as $ep) {
          ?>
          <tr>
            <td>
              <?= $ep->tipo; ?>
            </td>
            <td>
              <?= $ep->cantidad; ?>
            </td>
            <td>
              <?= $ep->tipos_cerveza; ?>
            </td>
            <td>
              <b><?= $ep->codigo; ?></b>
            </td>
            <td>
              <b>$<?= number_format($ep->monto); ?></b>
            </td>
          </tr>
          <?php
        }
        ?>
      </table>
    </div>
    <?php
      }
    ?>
    <?php

      foreach($entregas as $entrega) {

        if($entrega->monto == 0) {
          continue;
        }

        $total += $entrega->monto;

        if($entrega->estado!="Pagada") {
          $adeudado += $entrega->monto;
        }
        $entregas_productos = EntregaProducto::getAll("WHERE id_entregas='".$entrega->id."'");

    ?>
    <div class="card shadow">
    <table class="table table-sm">
    <thead class="thead-dark">
        <tr>
        <th>
        <b>#<?= $entrega->id; ?></b>
        </th>

        </tr>
    </thead>
    </table>
    <table class="mt-1 ml-3">
        <tr>
            <td>
                Fecha:
            </td>
            <td>
                <b><?= datetime2fechayhora($entrega->creada); ?></b>
            </td>
        </tr>
        <tr>
            <td>
                Monto:
            </td>
            <td>
                <b>$<?= number_format($entrega->monto); ?></b>
            </td>
        </tr>
        <tr>
            <td>
                Adeudado:
            </td>
            <td>
                <b>$<?= number_format($entrega->monto - $entrega->abonado); ?></b>
            </td>
        <tr>
            <td>
                Estado:
            </td>
            <td>
                <b><?= $entrega->estado; ?>
            </td>
        </tr>
    </table>
    <table class="table mt-3">
        <?php
        foreach($entregas_productos as $ep) {
          ?>
          <tr>
            <td>
              <?= $ep->tipo; ?>
            </td>
            <td>
              <?= $ep->cantidad; ?>
            </td>
            <td>
              <?= $ep->tipos_cerveza; ?>
            </td>
            <td>
              <b><?= $ep->codigo; ?></b>
            </td>
            <td>
              <b>$<?= number_format($ep->monto); ?></b>
            </td>
          </tr>
          <?php
        }
        ?>
      </table>
    </div>
    <?php
      }
    ?>
    <table class="table table-sm mt-3">
    <tr style="border: 1px solid black; background-color: white; height: 30px">
      <td>
      </td>
      <td>
      </td>
      <td>
        <b>
          TOTAL MENSUAL: <b>$<?= number_format($total); ?></b>
        </b>
      </td>
      <td>
        <b>
          ADEUDADO: <b>$<?= number_format($adeudado); ?></b>
        </b>
      </td>
      <td>
      </td>
    </tr>
  </tbody>
</table>


<script>

$(document).on('change','#mes-select', function(e) {
  window.location.href = "./?s=resumen-cliente&id=<?= $obj->id; ?>&mes=" + $('#mes-select').val() + "&ano=" + $('#ano-select').val();
});
$(document).on('change','#ano-select', function(e) {
  window.location.href = "./?s=resumen-cliente&id=<?= $obj->id; ?>&mes=" + $('#mes-select').val() + "&ano=" + $('#ano-select').val();
});
$(document).ready(function(){
  $('#mes-select').val(<?= $mes; ?>);
  $('#ano-select').val(<?= $ano; ?>);
});

</script>
