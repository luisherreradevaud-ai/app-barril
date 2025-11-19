<?php

require_once '../php/app.php';

$usuario = new Usuario;
session_start();
$usuario->checkSession($_SESSION);

if(isset($_POST['barriles'])) {
    $barriles_post = $_POST['barriles'];
} else {
    $barriles_post = [];
}

if(isset($_POST['fermentadores'])) {
    $fermentadores_post = $_POST['fermentadores'];
} else {
    $fermentadores_post = [];
}

$sent_barril_ids  = array_column($barriles_post,      'id');
$sent_fermact_ids = array_column($fermentadores_post, 'id');

$plant_barriles = Barril::getAll("WHERE estado='En planta'");
$plant_barril_ids = array_column($plant_barriles,'id');

$batch_activos = BatchActivo::getAll("WHERE litraje > 0");
$batch_act_map = [];
foreach ($batch_activos as $ba) {
    $batch_act_map[$ba->id_activos] = $ba;
}
$plant_fermact_ids = array_keys($batch_act_map);

$discrepancias = [];

if(!isset($barriles_post)) {

}


foreach ($plant_barriles as $b) {
    if (!in_array($b->id, $sent_barril_ids)) {
        $discrepancias[] = [
            'tipo'  => 'Barril',
            'id'    => $b->id,
            'codigo'=> $b->codigo,
            'error' => 'Está en planta pero falta en el reporte.'
        ];
    }
}

foreach ($barriles_post as $b_post) {
    if (!in_array($b_post['id'], $plant_barril_ids)) {
        $b = new Barril($b_post['id']);
        $discrepancias[] = [
            'tipo'   => 'Barril',
            'id'     => $b_post['id'],
            'codigo' => $b->codigo,
            'error'  => 'No está en planta.'
        ];
    }
}

// fermentadores con litraje>0 pero no enviados
foreach ($batch_activos as $ba) {
    if (!in_array($ba->id_activos, $sent_fermact_ids)) {
        $activo = new Activo($ba->id_activos);
        $discrepancias[] = [
            'tipo'   => 'Fermentador',
            'codigo' => $activo->codigo,
            'id'     => $ba->id_activos,
            'error'  => 'Cargado pero no en el reporte.'
        ];
    }
}
// fermentadores enviados que no están en batch activos
foreach ($fermentadores_post as $f_post) {
    $id = $f_post['id'];
    if (!in_array($id, $plant_fermact_ids)) {
        $activo = new Activo($ba->id_activos);
        $discrepancias[] = [
            'tipo'   => 'Fermentador',
            'codigo' => $activo->codigo,
            'id'     => $id,
            'error'  => 'No está cargado.'
        ];
    } else {
        // comparar estado
        $ba = $batch_act_map[$id];
        if ($ba->estado !== $f_post['status']) {
            $activo = new Activo($ba->id_activos);
            $discrepancias[] = [
                'tipo'       => 'Fermentador',
                'id'         => $id,
                'codigo' => $activo->codigo,
                'campo'      => 'Estado',
                'esperado'   => $ba->estado,
                'enviado'    => $f_post['status'],
                'error'      => 'Estado esperado: '.$ba->estado.'<br>Estado recibido: '.$f_post['status']
            ];
        }
    }
}

$repo = new ReporteDiario();
$repo->json_reporte       = json_encode([
    'barriles'      => $barriles_post,
    'fermentadores' => $fermentadores_post
], JSON_UNESCAPED_UNICODE);
$repo->json_discrepancias = json_encode($discrepancias, JSON_UNESCAPED_UNICODE);
$repo->id_usuarios        = $usuario->id;
$repo->date               = date('Y-m-d');
$repo->estado             = 'Cerrado';
$repo->save();


print json_encode([
    'success'       => true,
    'id_reporte'    => $repo->id,
    'discrepancias' => $discrepancias
]);



?>