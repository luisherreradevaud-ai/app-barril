<?php

if($_POST == array()) {
  die();
}

require_once("../php/app.php");

$usuario = new Usuario;
session_start();
$usuario->checkSession($_SESSION);

$obj = createObjFromTableName($_POST['entidad'],$_POST['id']);

$media = new Media($_POST['id_media']);
$obj->deleteRelation($media);
if(property_exists($obj,'id_media_header')){
  if($obj->id_media_header == $_POST['id_media']) {
    $obj->id_media_header = 0;
    $obj->save();
  }
}

$media->deleteMedia();

Historial::guardarAccion("Media #".$media->id." eliminada de ".get_class($obj)." #".$obj->id.".",$GLOBALS['usuario']);

$response["status"] = "OK";
$response["mensaje"] = "DELETE OK";

print json_encode($response,JSON_PRETTY_PRINT);


?>
