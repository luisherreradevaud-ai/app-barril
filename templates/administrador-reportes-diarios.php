<?php

    function mesDesdeEntero($n) {
        $meses = [
            1  => 'Enero',
            2  => 'Febrero',
            3  => 'Marzo',
            4  => 'Abril',
            5  => 'Mayo',
            6  => 'Junio',
            7  => 'Julio',
            8  => 'Agosto',
            9  => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];

        return $meses[$n];
    }

    if( isset($_GET['mesSelect']) && count(explode('-',$_GET['mesSelect'])) == 2 ) {
        $mesSelect = $_GET['mesSelect'];
    } else {
        $mesSelect = date('Y').'-'.intval(date('m'));
    }

    $primer_dia = $mesSelect.'-01';
    $ultimo_dia = $mesSelect.'-'.date('t',strtotime($primer_dia));

    $reportes_diarios = ReporteDiario::getAll('WHERE date BETWEEN "'.$primer_dia.'" AND "'.$ultimo_dia.'" ORDER BY date desc');

    $select_html = '';
    for($ano = 0; $ano <= (date('Y')-2025); $ano++) {
        for($mes = 1; $mes <= 12; $mes++) {
            $select_html .= '<option value="'.(2025-$ano).'-'.(13-$mes).'">'.mesDesdeEntero(13-$mes).' '.(2025-$ano).'</option>';
        }
    }

?>


<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-3">
            <h2>Reportes Diarios</h2>
            <select class="form-control font-bold" id="mesSelect" style="max-width: 250px">
                <?= $select_html; ?>
            </select>
        </div>
        <table class="table table-striped table-sm table-hover">
            <thead>
                <tr>
                    <td>
                        Fecha
                    </td>
                    <td>
                        Discrepancias
                    </td>
                    <td>
                        Estado
                    </td>
                </tr>
            </thead>
        <?php
            foreach($reportes_diarios as $reporte_diario) {
                ?>
                <tr class="tr-objs" style="cursor: pointer" data-id="<?= $reporte_diario->id; ?>">
                    <td>
                        <?= date2fechaEscrita($reporte_diario->date); ?>
                    </td>
                    <td>
                        <?= ($reporte_diario->json_discrepancias == '') ? 'No' : '<b>Si</b>'; ?>
                    </td>
                    <td>
                        <?= $reporte_diario->estado; ?>
                    </td>
                </tr>
                <?php
            }
        ?>
        </table>
    </div>
</div>


<script>

    var mesSelect = '<?= $mesSelect; ?>';

    $(document).ready(function() {
        $('#mesSelect').val(mesSelect);
    });

    $(document).on('change','#mesSelect', function(e) {
        window.location.href = './?s=administrador-reportes-diarios&mesSelect=' + $(e.currentTarget).val();
    });

    $(document).on('click','.tr-objs',function(e){
        window.location.href = './?s=detalle-reportes-diarios&id=' + $(e.currentTarget).data('id');
    })

</script>



