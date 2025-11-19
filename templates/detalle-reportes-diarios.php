<?php

    if(!validaIdExists($_GET,'id')) {
        die();
    }

    $usuario = $GLOBALS['usuario'];

    $obj = new ReporteDiario($_GET['id']);
    $discrepancias = json_decode($obj->json_discrepancias,JSON_PRETTY_PRINT);

    //

?>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-3">
            <h2>Reporte Diario: <?= date2fechaEscrita($obj->date); ?> </h2>
            <div>
                <?= $usuario->printReturnBtn(); ?>
            </div>
        </div>
        <span class="badge bg-danger mb-5 fs-5">
            Discrepancias: <?= count($discrepancias); ?>
        </span>
        <?php

        if(count($discrepancias) > 0) {
            ?>
            <table class="table">
            <?php
        }

        foreach($discrepancias as $discrepancia) {
            ?>
            <tr>
                <td>
                    <?= $discrepancia['tipo'].' <b>'.$discrepancia['codigo'].'</b>'; ?>
                </td>
                <td>
                    <?= $discrepancia['error']; ?>
                </td>
            </tr>
            <?php
        }

        if(count($discrepancias) > 0) {
            ?>
            <table class="table">
            <?php
        }

        ?>
    </div>
</div>