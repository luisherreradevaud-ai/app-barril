<?php

if($_POST == array()) {
  die();
}

require_once("../php/app.php");

$obj = createObjFromTableName($_POST['entidad'],$_POST['id']);
$obj->id_media_header = $_POST['id_media'];
$obj->save();

$response["status"] = "OK";
$response["mensaje"] = "UPDATE OK";

print json_encode($response,JSON_PRETTY_PRINT);


?>
