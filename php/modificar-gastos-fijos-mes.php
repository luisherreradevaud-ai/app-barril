<pre><?php

	require_once("./app.php");

	$usuario = new Usuario;
	session_start();

	$usuario->checkSession($_SESSION);

    /*print_r($_POST);
    print_r($_FILES);*/
	

	if(!validaIdExists($_SESSION,'id')) {
		header("Location: ../login.php");
	}

    if(!validaIdExists($_POST,'id')) {
        $_POST['id'] = '';
    }



    $obj = new GastoFijoMes($_POST['id']);
    $obj->setProperties($_POST);
    $obj->setSpecifics($_POST);
    $obj->save();

    /*print_r($obj);
    die();*/

    $files = array();
    foreach($_FILES['images'] as $key_1 => $value_1) {
        foreach($value_1 as $index => $value_2) {
            $files[$index][$key_1] = $value_2;
        }
    }



    foreach($files as $file) {
        if(isset($file['error'])) {
            if($file['error'] == 4) {
                continue;
            }
        }
        $media = new Media;
        $media->nombre = "";
        $media->descripcion = "";
        $media->newMedia($file);
        $media->createRelation($obj);
    }


    header("Location: ../?s=gastos-fijos-vista-anual&msg=5&id_gastos_fijos_mes=".$obj->id);

?>