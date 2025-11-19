<?php

  require_once "./app.php";

  $usuario = new Usuario;
  session_start();
  $usuario->checkSession($_SESSION);
  $_SESSION['login'] = "cocholgue";

  if(!validaIdExists($_POST,'id')) {
    die();
  }

  $precios = ClienteProductoPrecio::getAll("WHERE id_clientes='".$_POST['id']."'");
  foreach($precios as $precio) {
    $precio->delete();
  }

  foreach($_POST['precio'] as $key => $value) {
    $precio = new ClienteProductoPrecio;
    $precio->id_clientes = $_POST['id'];
    $precio->id_productos = $key;
    $precio->precio = $value;
    $precio->save();
  }

  header("Location: .".$_POST['return_url']."&msg=3");

?>
