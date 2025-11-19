<?php

if($_POST == array()) {
  die();
}

require_once "../../php/app.php";

if(!validaIdExistsVarios($_POST,['id_carousel_slide'])) {
  die();
}

$slide = new CarouselSlide($_POST['id_carousel_slide']);
$media = new Media($slide->id_media);

$slide->delete();
$media->deleteMedia();

$slides = CarouselSlide::getAll("WHERE id_carousel='".$slide->id_carousel."' ORDER BY seq_index asc");

foreach($slides as $key=>$n_slide) {
  $n_slide->seq_index = $key + 1;
  $n_slide->save();
}

?>
