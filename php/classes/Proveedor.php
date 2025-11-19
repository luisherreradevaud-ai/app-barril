<?php

    class Proveedor extends Base {

    public $id_usuarios = 0;
    public $nombre = "";
    public $email = "";
    public $telefono = "";
    public $creada;
    public $comentarios = "";
    public $numero_cuenta = "";
    public $rut_empresa = "";
    public $ids_tipos_de_insumos = "";
    public $tipos_de_insumos = array();

    public function __construct($id = null) {
      $this->tableName("proveedores");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
        $ids_ti = explode("%$*",$this->ids_tipos_de_insumos);
        foreach($ids_ti as $id_ti) {
          if($id_ti == "") {
            continue;
          }
          $this->tipos_de_insumos[] = new TipoDeInsumo($id_ti);
        }
      } else {
        $this->creada = date('Y-m-d H:i:s');
      }
    }

    public function setSpecifics($values) {

      if(!is_array($values)) {
        return false;
      }
      if(!isset($values['ids_tipos_de_insumos'])) {
        return false;
      }
      if(!is_array($values['ids_tipos_de_insumos'])) {
        return false;
      }

      $this->ids_tipos_de_insumos = implode('%$*',$values['ids_tipos_de_insumos']);
    }
  }

 ?>
