<?php

  class RegistroAsistencia extends Base {

    public $id_usuarios = 0;
    public $date;
    public $entrada;
    public $salida;
    public $creada;

    public function __construct($id = null) {
      $this->tableName("registro_asistencia");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      } else {
        $this->creada = date('Y-m-d H:i:s');
      }
    }

    public static function getAsistencia($id_usuarios,$date) {
        $fetch = RegistroAsistencia::getAll("WHERE id_usuarios='".$id_usuarios."' AND date='".$date."'");
        if( count($fetch) == 0 ) {
            $registro_asistencia = new RegistroAsistencia;
            $registro_asistencia->id_usuarios = $id_usuarios;
            $registro_asistencia->date = $date;
            $usuario_ra = new Usuario($id_usuarios);
            if( $usuario_ra->registro_asistencia == 0 ) {
                $registro_asistencia->entrada = date('H:i:s');
            }
            $registro_asistencia->save();
            return $registro_asistencia;
        } else {
            return $fetch[0];
        }
    }

  }

 ?>
