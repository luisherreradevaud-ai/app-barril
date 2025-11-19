<?php

    class ReporteDiario extends Base {
        
        public $creada;
        public $json_reporte;
        public $json_discrepancias;
        public $id_usuarios;
        public $date;
        public $estado;

        public function __construct($id = null) {
            $this->tableName("reportes_diarios");
            if ($id) {
                $this->id = $id;
                $info = $this->getInfoDatabase('id');
                $this->setProperties($info);
            } else {
                $this->creada = date('Y-m-d H:i:s');
            }
        }
    }

?>