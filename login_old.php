<?php

$msg = 0;

if(isset($_GET['msg'])) {
  $msg = $_GET['msg'];
}

$url = '';
if(isset($_GET['url'])) {
  $url = $_GET['url'];
}

?>
<!doctype html>
<html lang="es">
  <head>
    <link rel="shortcut icon" type="image/png" href="../img/navbar-logo.png">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="Luis Herrera">
    <title>Cerveza Cocholgue</title>
    <link href="./css/fontawesome-all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.css" rel="stylesheet">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <style>
    html {
      background-color: black;
    }
    body {
      padding-top: 50px;
      background-color: black;
      text-align: center;
    }

    .drgrass-navbar {
      position: fixed;
      z-index: 100;
      width: 100%;
      height: 100px;
    }
    @media (min-width: 768px) {
      .drgrass-navbar {
        top: 50px; } }

    .drgrass-navbar-link {
      color: white;
      transition: 0.5s;
      font-size: 0.9em;
      line-height: 50px;
      margin-right: 50px;
      text-transform: uppercase;
    }
    .drgrass-navbar-link:hover {
      color: yellow;
    }

    .drgrass-container {
      margin-left: 80px;
      margin-right: 80px;
      height: 100%;
    }

    .drgrass-navbar-inside-right {
      background-color: #f492d8;
      height: 50px;
    }

    .drgrass-navbar-inside-left {
      background-image: linear-gradient(to right,  rgba(244,146,216, 0),rgba(244,146,216, 1));
    }
    .drgrass-navbar-logo-desktop {
      position: fixed;
      left: 50px;
      top: 10px;
      z-index: 101;
      width: 120px;
    }

    .drgrass-navbar-logo-mobile {
      position: fixed;
      left: 0px;
      top: 75px;
      z-index: 101;
      width: 100px;
    }

    .drgrass-footer {
      background: linear-gradient(90deg, rgba(255,243,53,1) 0%, rgba(91,247,171,1) 46%, rgba(91,223,253,1) 100%);
      height: 200px;
    }

    .drgrass-search-bar-div {
      border: 2px solid rgba(255,255,255,0.3);
      border-radius: 5px;
    }

    .drgrass-search-bar-input {
      background-color: rgba(255,255,255,0.1);
      color: white;
      border: 0px;
      margin-top: 5px;
      margin-bottom: 5px;
      padding-left: 5px;
      width: 250px;
      height: 40px;
      font-family: Montserrat-Medium;
    }

    .drgrass-search-bar:focus {
      border:0px;
    }

    .search-results-li {

      font-family: Montserrat-Medium;
    }

    ::placeholder { /* Chrome, Firefox, Opera, Safari 10.1+ */
      color: white;
      opacity: 1; /* Firefox */
    }
    a {
      text-decoration: none;
    }
    body > * {
    }

    h1 {
      font-size: 1.5em;
    }

    div {
    }

    .drgrass-inicio-news-bar {
      background-color: #fff436;
      height: 35px;
      line-height: 35px;
      color: #f492d8;
      font-family: 'Montserrat-Black';
      font-size: 1.6em;
      padding-left: 20px;
    }

    .drgrass-inicio-store-bar {
      background-color: #f492d8;
      height: 35px;
      line-height: 35px;
      color: white;
      font-size: 1.6em;
      padding-left: 20px;
    }

    .drgrass-inicio-card {
      box-sizing: border-box;
      padding: 0px;
      border: 2px solid white;
    }


    .drgrass-producto-card-imagen {
      height: 300px;
      background-size: contain;
      background-repeat: no-repeat;
      background-position: center;
      cursor: pointer;
      transition: transform .2s;
    }

    .drgrass-producto-card-imagen:hover {
      transform: scale(1.1);
    }

    .drgrass-inicio-card-side-text {
      text-align: center;
      vertical-align: middle;
    }

    .drgrass-inicio-card-side-text > * {
      margin-bottom: 10px;
    }
    .drgrass-inicio-card-side-text-precio {
      font-size: 1.4em;
      letter-spacing: 0.02em;
    }
    .drgrass-inicio-card-side-text-precio-tachado {
      font-size: 1.4em;
      letter-spacing: 0.02em;
      text-decoration: line-through;
    }
    .drgrass-inicio-card-side-text-oferta {
      font-size: 1.8em;
      letter-spacing: 0.01em;
      color: #f492d8;
    }
    .drgrass-inicio-card-side-text-precio-oferta {
      font-size: 1.8em;
      letter-spacing: 0.02em;
      margin-bottom: 20px;
    }
    .drgrass-inicio-card-bottom-text {
      font-size: 1.3em;
      text-transform: uppercase;
    }

    .drgrass-inicio-card-bottom-text-medium {
      font-size: 0.7em;
    }

    .carrito-compra-producto-nombre {
      vertical-align: middle;
      font-size: 0.9em;
      padding-right: 10px;
    }

    .carrito-compra-div-cantidad-articulos {
      padding-left: 10px;
      padding-right: 10px;
      color: red;
    }

    .carrito-compra-div-total > * {
      padding-left: 10px;
      padding-right: 10px;
      font-size: 1.2em;
    }

    #carrito-compra-productos-total {
      font-family: 'Montserrat-Black';
    }

    .btn {
      border: 2px solid white;
      color: white;
      text-transform: uppercase;
    }

    input {
    }
    .btn-google {
      border: 1px solid #fff436 !important;
      color: white !important;
    }
    </style>
  </head>
  <body>
    <center>
    <a href="./">
      <img src="./img/cocholgue.png" style="width: 200px">
    </a>
      <input id="login-email" type="text" name="email" placeholder="Email" class="form-control" style="max-width: 300px; margin-top: 20px">
      <input id="login-password" type="password" name="password" placeholder="Password" class="form-control" style="max-width: 300px; margin-top: 10px">
      <button id="login-btn" class="btn btn-default btn-lg" style="width: 300px; margin-top: 10px;">Entrar</button>
      <br />
      <br />
      <?php
      if($msg == 1) {
      ?>
      <div class="alert alert-danger" role="alert" style="width: 300px">Contrase&ntilde;a incorrecta.</div>
      <?php
      } else
      if($msg == 2) {
      ?>
      <div class="alert alert-info" role="alert" style="width: 300px">Se ha enviado un correo para recuperar su cuenta.</div>
      <?php
      } else
      if($msg == 3) {
      ?>
      <div class="alert alert-danger" role="alert" style="width: 300px">El email no corresponde.</div>
      <?php
      } else
      if($msg == 4) {
      ?>
      <div class="alert alert-info" role="alert" style="width: 300px">Contrase&ntilde;a seteada con &eacute;xito.</div>
      <?php
      } else
      if($msg == 5) {
      ?>
      <div class="alert alert-info" role="alert" style="width: 300px">Contrase&ntilde;a seteada con &eacute;xito.</div>
      <?php
      }
      ?>

      <br />
      <div>
        <a href="#" id="olvidePasswordBtn" style="width: 200px; font-size: 0.8em; color:white">Recuperar contrase&ntilde;a</a>
      </div>
    <form action="./php/login.php" method="POST" id="loginForm">
      <input type="hidden" id="hidden_email" name="email" value="">
      <input type="hidden" id="hidden_password" name="password" value="">
      <input type="hidden" id="hidden_tipo_login" name="tipo_login" value="">
      <input type="hidden" name="redirect-url" value="<?= $url; ?>">
    </form>

    <div class="modal fade bd-example-modal" id="olvidePasswordModal" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title" id="exampleModalLabel">Recuperar cuenta</h4>
            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
              <div class="align-center"><center>
                <div class="form-group mt-20 subscribe-form">
                  <input type="text" class="form-control" id="olvidePasswordEmailTxt" name="email" placeholder="Email">
                  <small style="color: black">Se enviar&aacute;n instrucciones para recuperar tu cuenta a tu correo.</small>
                </div>
              </div>
              <div class="mt-10 mb-10"><center>
                <button class="btn btn-1 btn-lg" id="olvide-password-modal-btn" STYLE="BORDER: 2PX SOLID BLACK; color: black">Aceptar</button>
              </div>
            </div>
          </div>
        </div>
      </div>


      <div class="modal fade bd-example-modal" id="result-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title" id="exampleModalLabel">Recuperar cuenta</h4>
              <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body" id="result-modal-text">
            </div>
          </div>
        </div>
      </div>

    <script>

    $('#olvidePasswordBtn').click(()=>{
      $('#olvidePasswordModal').modal('toggle');
    });
    $('#olvidePasswordModalBtn').click(()=>{
      $('#olvidePasswordForm').submit();
    });
    $('#olvidePasswordEmailTxt').change(()=>{
      if($('#olvidePasswordEmailTxt').val()=="") {
        $('#olvidePasswordModalBtn').attr('DISABLED',true);
      } else {
        $('#olvidePasswordModalBtn').attr('DISABLED',false);
      }
    });

    $('#emailTxt').keyup(()=>{
      if($('#emailTxt').val()!=''&&$('#passwordTxt').val()!='') {
        $('#loginBtn').attr('DISABLED',false);
      } else {
        $('#loginBtn').attr('DISABLED',true);
      }
    });
    $('#passwordTxt').keyup(()=>{
      if($('#emailTxt').val()!=''&&$('#passwordTxt').val()!='') {
        $('#loginBtn').attr('DISABLED',false);
      } else {
        $('#loginBtn').attr('DISABLED',true);
      }
    });

    $('#login-btn').click(()=>{
      $('#hidden_email').val($('#login-email').val());
      $('#hidden_password').val($('#login-password').val());
      $('#hidden_tipo_login').val('');
      $('#loginForm').submit();
    });

    $(document).on('click','#olvide-password-modal-btn',function(e){

      e.preventDefault();

      var email = $('#olvidePasswordEmailTxt').val();

      if(!email.includes('@')) {
        alert("Ingrese un correo valido.");
        return false;
      }

      if(!email.includes('.')) {
        alert("Ingrese un correo valido.");
        return false;
      }

      if(email.length < 5) {
        alert("El email debe tener mas de 5 caracteres.");
        return false;
      }

      var data = {
        'email': email
      };

      var url = "./ajax/ajax_setRecuperacion.php";

      $.post(url,data,function(response){
        console.log(response);
        $('#olvidePasswordModal').modal('toggle');
        $('#result-modal').modal('toggle');
        $('#result-modal-text').html(response.mensaje)
        console.log(response);
        if(response.status!="OK") {

        }
      },'json').fail(function(){
        alert("No funciono");
      });

    });


    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
  </body>
  </html>
