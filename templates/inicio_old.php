<?php

    $usuario = $GLOBALS['usuario'];

  if($usuario->nivel == "Cliente") {
    include("./templates/central-clientes-completa.php");
  } else
  {
    include("./templates/planificacion.php");
  }

?>