<?php

	require_once("./app.php");

	$usuario = new Usuario;
	session_start();

	$usuario->checkSession($_SESSION);
	

	if(!validaIdExists($_SESSION,'id')) {
		header("Location: ../login.php");
	}

    $obj = new Gasto;
    $obj->setProperties($_POST);
    $obj->setSpecifics($_POST);
    $obj->id_usuarios_ingreso = $usuario->id;
    $obj->save();

    //Historial::guardarAccion(get_class($obj)." #".$obj->id." creado.",$GLOBALS['usuario']);

    if(isset($_FILES['comprobante-img'])) {
      
      $media = new Media;
      $media->nombre = "";
      $media->descripcion = "";
      $media->newMedia($_FILES['comprobante-img']);
      $media->createRelation($obj);
      //Historial::guardarAccion("Imagen agregada a ".get_class($obj)." #".$obj->id.".",$GLOBALS['usuario']);
    }

    header("Location: ".$_POST['return_url']."&msg=5");

?>