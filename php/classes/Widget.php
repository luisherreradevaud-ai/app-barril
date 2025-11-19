<?php

  class Widget {

    public static function printWidget($nombre) {
        
        $path = $GLOBALS['base_dir']."/widgets/".$nombre.".php";

        if(!file_exists($path)) {
            print $path;
            return false;
        }
        include($path);

    }
  }

 ?>
