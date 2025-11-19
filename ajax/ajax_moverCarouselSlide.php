<?php

if($_POST == array()) {
  die();
}

require_once "../../php/app.php";

if(!validaIdExistsVarios($_POST,['id_carousel_slide','seq_index'])) {
  die();
}

$slide_a_mover = new CarouselSlide($_POST['id_carousel_slide']);

if($slide_a_mover->seq_index < $_POST['seq_index']) {
  $query = "WHERE seq_index>=".$slide_a_mover->seq_index." AND id!=".$slide_a_mover->id." ORDER BY seq_index asc";
  $direccion = -1;
} else
if($slide_a_mover->seq_index > $_POST['seq_index']) {
  $query = "WHERE seq_index>=".$_POST['seq_index']." AND id!=".$slide_a_mover->id." ORDER BY seq_index asc";
  $direccion = 1;
} else {
  die();
}


$slides = CarouselSlide::getAll($query);

foreach($slides as $slide) {
  print $slide->id."\n";
  $slide->seq_index = $slide->seq_index + $direccion;
  $slide->save();
}

$slide_a_mover->seq_index = $_POST['seq_index'];
$slide_a_mover->save();



?>
