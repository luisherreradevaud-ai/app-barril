<?php

    class CentralDeDespacho {

        public function getDataBarriles() {
            $barriles_enplanta_cerveza = Barril::getAll("WHERE clasificacion='Cerveza' AND estado='En planta' AND id_batches!=0 AND litros_cargados>0 ORDER BY codigo asc");
            foreach($barriles_enplanta_cerveza as $bepc) {
                $bepc->batch_info = new Batch($bepc->id_batches);
                $bepc->id_recetas = $bepc->batch_info->id_recetas;
            }
            return $barriles_enplanta_cerveza;
        }

        public function getDataPedidos() {
            $pedidos = Pedido::getAll("ORDER BY id asc");
            $data = [];
            foreach($pedidos as $pedido) {
                $pedidos_productos = PedidoProducto::getAll("WHERE id_pedidos='".$pedido->id."'");
                $data[] = array(
                    'pedido' => $pedido,
                    'pedidos_productos' => $pedidos_productos
                );
            }
        }

        public function getDataDespachos() {
            $repartidores = Usuario::getAll("WHERE nivel='Repartidor'");
            foreach($repartidores as $repatidor) {
                $despachos = Despacho::getAll("WHERE id_usuarios_repartidor='".$repartidor->id."'");
            }
        }

        public function getDataFermentadores() {
            $batches_activos_maduracion = BatchActivo::getAll("JOIN activos 
                ON activos.id = batches_activos.id_activos
                WHERE activos.clase = 'Fermentador'
                AND batches_activos.litraje > 0
                AND (
                    (activos.codigo LIKE 'BD%' 
                    AND batches_activos.estado = 'Maduración')
                    OR activos.codigo NOT LIKE 'BD%'
                )
                ORDER BY batches_activos.id_batches ASC");
            $bams = [];
            foreach($batches_activos_maduracion as $bam) {
                $activo = new Activo($bam->id_activos);
                $batch = new Batch($bam->id_batches);
                $receta = new Receta($batch->id_recetas);
                $bam->activo_codigo = $activo->codigo;
                $bam->activo_litraje = $activo->litraje;
                $bam->receta_nombre = $receta->nombre;
                $bams[] = $bam;
            }
            return $bams;
        }

        public function getBarrilesDisponiblesParaCarga() {
            $barriles = Barril::getAll("WHERE litros_cargados<litraje AND estado='En planta' AND clasificacion='Cerveza' ORDER BY codigo asc");
            return $barriles;
        }

        public function getDataPage() {
            $data['barriles_en_planta'] = $this->getDataBarriles();
            $data['pedidos'] = $this->getDataPedidos();
            $data['batches_activos_maduracion'] = $this->getDataFermentadores();
            $data['barriles_disponibles_para_carga'] = $this->getBarrilesDisponiblesParaCarga();
            return $data;
        }

    }

?>