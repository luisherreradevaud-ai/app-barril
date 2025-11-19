<?php

  require_once "app.php";



  if(isset($_POST['email'])) {
    $email = sanitize_input($_POST['email']);
  } else {
    goto404();
  }

  if(isset($_POST['password'])) {
    $password = sanitize_input($_POST['password']);
  } else {
    goto404();
  }

  if(isset($_POST['repetir-password'])) {
    $repetirpassword = sanitize_input($_POST['repetir-password']);
  } else {
    goto404();
  }

  if( $password!=$repetirpassword ) {
    gotoError('passwords-no-coinciden');
  }

  $usuario = new Usuario;
  $usuario->getFromDatabase('email',$email);

  if($usuario->id!=""&&$usuario->id!=0) {
    gotoError('usuario-ya-existe');
  }

  $usuario->nombre = $email;
  $usuario->email = $email;
  $usuario->nivel = 3;
  $usuario->password = $usuario->passwordHash($password);
  $usuario->verificacion = $usuario->passwordHash($email);
  $usuario->fecha_creacion = date('Y-m-d');
  $usuario->save();

  $email_1 = new Email;
  $email_1->destinatario = $email;
  $email_1->mailVerificacion($usuario->verificacion);
  $email_1->enviarMail();

  header("Location: ../solofaltaverificacion.html");

 ?>
