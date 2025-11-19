<?php



  include("./app.php");

  $usuario = new Usuario;
  session_start();
  $usuario->checkSession($_SESSION);

  if($usuario->nombre=="Invitado"){
    header("Location: ./..");
  }

  if(!isset($_POST['modo'])){
    header("Location: ./..");
  }
  $modo = $_POST['modo'];

  /////////////////////////////////////////////////////////////////////////

  if($modo=="subir-media") {

    if(!isset($_POST['entidad'])){
      //header("Location: ./..");
    }
    $entidad = $_POST['entidad'];

    

    $media = new Media;
    $media->nombre = $_POST['media_nombre'];
    $media->descripcion = $_POST['media_descripcion'];
    $media->newMedia($_FILES['file']);

    $id = $_POST['id_'.$entidad];
    $obj = createObjFromTableName($entidad,$id);
    $media->createRelation($obj);

    Historial::guardarAccion("Imagen agregada a ".get_class($obj)." #".$obj->id.".",$GLOBALS['usuario']);

    $url = "Location: ../?s=detalle-".$entidad."&id=".$id."#media";

    if($_POST['entidad'] == "configuraciones") {
      $url = "Location: ../?s=configuracion-general&msg=2";
    }

    header($url);

  }

  if($modo=="nuevo-entidad-con-media") {

    $obj = createObjFromTableName($_POST['entidad'],'');
    $obj->setProperties($_POST);
    $obj->setSpecifics($_POST);
    $obj->save();

    Historial::guardarAccion(get_class($obj)." #".$obj->id." creado.",$GLOBALS['usuario']);


    if(isset($_FILES['file'])) {
      $media = new Media;
      $media->nombre = "";
      $media->descripcion = "";
      $media->newMedia($_FILES['file']);
      $media->createRelation($obj);
      Historial::guardarAccion("Imagen agregada a ".get_class($obj)." #".$obj->id.".",$GLOBALS['usuario']);
    }

    if($_POST['entidad'] == "gastos") {

      if($_POST['repetir'] != "No") {

        $ts_date = strtotime($_POST['date_vencimiento']);
        $ts_hasta = strtotime($_POST['hasta']);

        if($_POST['repetir'] == "Cada semana") {

          while($ts_date <= $ts_hasta) {

            $ts_date = strtotime("+1 week", $ts_date);

            $obj = createObjFromTableName($_POST['entidad'],'');
            $obj->setProperties($_POST);
            $obj->date_vencimiento = date('Y-m-d',$ts_date);
            $obj->setSpecifics($_POST);
            $obj->save();

          }
        }
        if($_POST['repetir'] == "Cada mes") {

          while($ts_date <= $ts_hasta) {

            $ts_date = strtotime("+1 month", $ts_date);

            $obj = createObjFromTableName($_POST['entidad'],'');
            $obj->setProperties($_POST);
            $obj->date_vencimiento = date('Y-m-d',$ts_date);
            $obj->setSpecifics($_POST);
            $obj->save();
            
          }
        }
      }

      
      
    }

    if($_POST['entidad'] == "documentos") {
      $url = "Location: ../?s=".$_POST['redirect']."&id=".$obj->id."&msg=1";
    } else 
    if($_POST['entidad'] == "gastos") {
      $url = "Location: ../?s=".$_POST['redirect']."&id=".$obj->id."&msg=1";
    } else {
      $url = "Location: ../?s=detalle-".$_POST['entidad']."&id=".$obj->id;
    }
    
    header($url);

  } else

    if($modo=="nuevo-gastos-en-proyectos") {

      
    if(!validaIdExists($_POST,'id_proyectos')) {
        header("./?s=404");
        die();
      }


      $proyecto = new Proyecto($_POST['id_proyectos']);

      $obj = new Gasto;
      $obj->setProperties($_POST);
      $obj->item = $proyecto->nombre.": ".$obj->item;
      $obj->setSpecifics($_POST);
      $obj->save();

      $proyecto->createRelation($obj);

      Historial::guardarAccion(get_class($obj)." #".$obj->id." creado.",$GLOBALS['usuario']);


      if(isset($_FILES['file'])) {
        $media = new Media;
        $media->nombre = "";
        $media->descripcion = "";
        $media->newMedia($_FILES['file']);
        $media->createRelation($obj);
        Historial::guardarAccion("Imagen agregada a ".get_class($obj)." #".$obj->id.".",$GLOBALS['usuario']);
      }

      header("Location: ../?s=detalle-proyectos&id=".$proyecto->id."&msg=3#agregar-gastos-btn");
      
    }
    


?>
