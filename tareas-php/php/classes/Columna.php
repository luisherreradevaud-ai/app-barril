<?php

class Columna extends Base {

  public $nombre;
  public $id_tableros;
  public $orden = 0;
  public $color = '#6A1693';
  public $creada;
  public $actualizada;

  public function __construct($id = null) {
    $this->tableName("columnas");
    if($id) {
      $this->id = $id;
      $info = $this->getInfoDatabase('id');
      $this->setProperties($info);
    } else {
      $this->creada = date('Y-m-d H:i:s');
      $this->actualizada = date('Y-m-d H:i:s');
    }
  }

  public function save() {
    $this->actualizada = date('Y-m-d H:i:s');
    return parent::save();
  }

  public function getTareas() {
    $tareas_arr = Tarea::getAll("WHERE id_columnas='".$this->id."' ORDER BY orden ASC, id ASC");
    $tareas = array();

    foreach($tareas_arr as $tarea) {
      $tarea_data = array(
        'id' => $tarea->id,
        'nombre' => $tarea->nombre,
        'descripcion' => $tarea->descripcion,
        'id_columnas' => $tarea->id_columnas,
        'orden' => $tarea->orden,
        'fecha_vencimiento' => $tarea->fecha_vencimiento,
        'estado' => $tarea->estado,
        'usuarios' => $tarea->getUsuarios(),
        'etiquetas' => $tarea->getEtiquetas(),
        'cantidad_archivos' => $tarea->getCantidadArchivos(),
        'vencida' => $tarea->isVencida(),
        'checklist' => $tarea->checklist,
        'progreso_checklist' => $tarea->getProgresoChecklist()
      );
      $tareas[] = $tarea_data;
    }

    return $tareas;
  }

  public function toArray() {
    return array(
      'id' => $this->id,
      'nombre' => $this->nombre,
      'id_tableros' => $this->id_tableros,
      'orden' => $this->orden,
      'color' => $this->color,
      'tareas' => $this->getTareas(),
      'creada' => $this->creada,
      'actualizada' => $this->actualizada
    );
  }

  public function delete() {
    // Delete all tasks in this column first
    $mysqli = $GLOBALS['mysqli'];
    $query = "DELETE FROM tareas WHERE id_columnas='".$this->id."'";
    $mysqli->query($query);

    // Delete the column
    parent::delete();
  }
}

?>
