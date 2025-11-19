<?php

$date = date('Y-m-d');
if(isset($_GET['date'])) {
    $date = $_GET['date'];
}

$registros_asistencia = RegistroAsistencia::getAll("WHERE date='".$date."' order by entrada asc");

?>


<div class="d-flex justify-content-between mb-5">
    <input type="date" id="date-select" class="form-control" style="max-width: 300px" value="<?= $date; ?>">
</div>

<table class="table table-bordered table-striped table-hover" id="registro_asistencia-table">
    <thead>
        <tr>
            <th>
                Usuario
            </th>
            <th>
                Fecha
            </th>
            <th>
                Entrada
            </th>
            <th>
                Salida
            </th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach($registros_asistencia as $ra) {
            $usuario_ra = new Usuario($ra->id_usuarios);
        ?>
        <tr>
            <td>
                <?= $usuario_ra->nombre; ?>
            </td>
            <td>
                <?= date2fechaEscrita($ra->date); ?>
            </td>
            <td>
                <?= $ra->entrada; ?>
            </td>
            <td>
                <?= $ra->salida; ?>
            </td>
        </tr>
        <?php
        }
        ?>
    </tbody>
</table>

<script>

$(document).ready(function() {
  new DataTable('#registro_asistencia-table', {
    paging: false
  });
});

$(document).on('change','#date-select',function(e){
    e.preventDefault();
    window.location.href = './?s=asistencia-registro&date=' + $(e.currentTarget).val();
});

</script>