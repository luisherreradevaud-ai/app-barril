<?php

    class UsuarioCliente extends Base {

        public $id_usuarios;
        public $id_clientes;

        public function __construct($id = null) {
            $this->tableName("usuarios_clientes");
            if($id) {
                $this->id = $id;
                $info = $this->getInfoDatabase('id');
                $this->setProperties($info);
            }
        }
    }

    
?>