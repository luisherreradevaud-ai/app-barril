<?php

include("./php/app.php");

$usuario = new Usuario;

$_SESSION = $usuario->checkSession($_SESSION);
$_SESSION['login'] = "cocholgue";

if(!validaIdExists($_SESSION,'id')) {
  header("Location: ./login.php");
}

?>
<!DOCTYPE html>
<html lang="en">
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.min.js"></script>
    <script>
    function getDataForm(entity) {
      var data = {};
      $("form#" + entity + "-form :input").each(function(){
       var input = $(this);
       if(input.attr("name")!=undefined) {
         data[input.attr("name")] = input.val();
       };
      });
      return data;
    }
    </script>
</head>
<body id="page-top">
    <div id="wrapper">
        <ul class="navbar-nav bg-gradient-drgrass-black sidebar sidebar-dark accordion" id="accordionSidebar">

            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="./" target="_BLANK">
                <div class="sidebar-brand-icon rotate-n-15 d-none">
                  <img src="../img/navbar-logo.png" width="60">
                </div>
                <div class="sidebar-brand-text mx-3">Cerveza Cocholgue</div>
            </a>

            <hr class="sidebar-divider">

            <div class="sidebar-heading">
                Cervecer&iacute;a
            </div>
            <?php
            if($usuario->nivel == "Cliente") {
              ?>
            <li class="nav-item">
                <a class="nav-link" href="./?s=central-clientes">
                    <i class="fas fa-fw fa-handshake"></i>
                    <span>Facturacion</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="./?s=central-clientes-pedidos">
                    <i class="fas fa-fw fa-handshake"></i>
                    <span>Pedidos</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="./?s=sugerencias">
                    <i class="fas fa-fw fa-handshake"></i>
                    <span>Sugerencias y Reclamos</span>
                </a>
            </li>
              <?php
            }
            if($usuario->nivel == "Jefe de Planta" || $usuario->nivel == "Administrador") {
            ?>
            <li class="nav-item">
                <a class="nav-link" href="./?s=central-despacho">
                    <i class="fas fa-fw fa-truck"></i>
                    <span>Central Despacho</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="./?s=pedidos">
                    <i class="fas fa-fw fa-truck"></i>
                    <span>Pedidos de Clientes</span>
                </a>
            </li>
            <?php
            }
            if($usuario->nivel == "Repartidor") {
            ?>
            <li class="nav-item">
                <a class="nav-link" href="./?s=repartidor">
                    <i class="fas fa-fw fa-truck"></i>
                    <span>Entrega de Productos</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="./?s=devolucion-barriles">
                    <i class="fas fa-fw fa-truck"></i>
                    <span>Devolucion de Barriles</span>
                </a>
            </li>
            <?php
            }
            if($usuario->nivel == "Administrador") {
            ?>
            <li class="nav-item">
                <a class="nav-link" href="./?s=pagos">
                    <i class="fas fa-fw fa-coins"></i>
                    <span>Pagos</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="./?s=barriles">
                    <i class="fas fa-fw fa-coins"></i>
                    <span>Barriles</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="./?s=clientes">
                    <i class="fas fa-fw fa-handshake"></i>
                    <span>Clientes</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="./?s=usuarios">
                    <i class="fas fa-fw fa-user"></i>
                    <span>Usuarios</span>
                </a>
            </li>
            <?php
            }
            ?>
<!--
            <div class="sidebar-heading">
                Interno
            </div>

            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapse-tienda"
                    aria-expanded="true" aria-controls="collapse-tienda">
                    <i class="fas fa-fw fa-store"></i>
                    <span>Tienda</span>
                </a>
                <div id="collapse-tienda" class="collapse" aria-labelledby="headingUtilities"
                    data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                      <a class="collapse-item" href="./?s=nuevo-ventas">Nueva Venta</a>
                      <a class="collapse-item" href="./?s=caja">Caja</a>
                      <a class="collapse-item" href="./?s=estadisticas">Estadisticas</a>
                    </div>
                </div>
            </li>
-->


            <div class="text-center d-none d-md-inline mt-5">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>



        </ul>

        <div id="content-wrapper" class="d-flex flex-column">

            <div id="content">

                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <ul class="navbar-nav ml-auto">

                        <li class="nav-item dropdown no-arrow d-sm-none">
                            <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-search fa-fw"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in"
                                aria-labelledby="searchDropdown">
                                <form class="form-inline mr-auto w-100 navbar-search">
                                    <div class="input-group">
                                        <input type="text" class="form-control bg-light border-0 small"
                                            placeholder="Search for..." aria-label="Search"
                                            aria-describedby="basic-addon2">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="button">
                                                <i class="fas fa-search fa-sm"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </li>

                        <div class="topbar-divider d-none d-sm-block"></div>

                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                                <?php
                                  print $_SESSION['nombre']." ".$_SESSION['apellido'];
                                ?></span>
                                <img class="img-profile rounded-circle"
                                    src="img/undraw_profile.svg">
                            </a>

                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="?s=perfil">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Perfil
                                </a>

                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>

                    </ul>

                </nav>

                <div class="container-fluid">

                  <?php
                    switch_templates($section);
                  ?>


                </div>

            </div>
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; Estudio Pl&aacute;tano 2023</span>
                    </div>
                </div>
            </footer>

        </div>

    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Listo para salir?</h5>
                    <button class="close" type="button" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Selecciona "Logout" si deseas cerrar tu sesi&oacute;n.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
                    <a class="btn btn-primary" href="php/logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>-->

    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

    <!-- Core plugin JavaScript-->
    <!--<script src="vendor/jquery-easing/jquery.easing.min.js"></script>-->


    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Page level plugins
    <script src="vendor/chart.js/Chart.min.js"></script>
    <script stc="vendor/lightgallery/lightgallery.js"></script>

    Page level custom scripts
    <script src="js/demo/chart-area-demo.js"></script>
    <script src="js/demo/chart-pie-demo.js"></script>
    <script>
        $(document).ready(function(){
            $('#lightgallery').lightGallery();
        });
    </script> -->
</body>

</html>
