<?php

  $slides = CarouselSlide::getAll("WHERE id_carousel='0' ORDER BY seq_index asc");

?>

<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-coins"></i> <b>Carousel Inicio</b></h1>
  </div>
  <div>
    <div>
      <button class="d-sm-inline-block btn btn-sm btn-primary shadow-sm mb-2" id="nueva-slide-btn" data-toggle="modal" data-target="#agregar-slide-modal"><i class="fas fa-fw fa-plus"></i> Nuevo Slide</button>
    </div>
  </div>
</div>
<hr />
<table class="table table-stripped">
<?php
  foreach($slides as $slide) {
    $media = new Media($slide->id_media);
  ?>
  <tr>
    <td>
      <img src="../media/thumbnails/50/<?= $media->url; ?>">
    </td>
    <td>
      Mover a posicion
      <select class="mover-index-select" data-idcarouselslide="<?= $slide->id; ?>">
        <?php
        for($i=0;$i<count($slides);$i++) {
          print "<option";
          if($slide->seq_index==($i+1)) {
            print " SELECTED";
          }
          print ">".($i+1)."</option>";
        }
        ?>
      </select>
    </td>
    <td>
      <button class="d-sm-inline-block btn btn-sm btn-danger shadow-sm mb-2 eliminar-slide-btn" id="eliminar-slide-btn" data-idcarouselslide="<?= $slide->id; ?>">Eliminar</button>
    </td>
    </td>
  </tr>
  <?php
  }
?>
</table>


<div class="modal fade" id="agregar-slide-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-fw fa-images"></i> Agregar Slide</h5>
                <button class="close" type="button" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body" style="font-weight: bold">
            <form id="agregar-media-form" action="php/carousel.php" method="POST" enctype="multipart/form-data">
              <input type="hidden" name="modo" value="agregar-slide">
            <div class="form-group">
              <label for="nombre-name" class="control-label">Nombre:</label>
              <input type="text" class="form-control" name="media_nombre" value="Slide">
            </div>
            <div class="form-group">
              <label for="descripcion-text" class="control-label">Descripci&oacute;n:</label>
              <textarea type="text" class="form-control" name="media_descripcion">Imagen de Slide de Carousel</textarea>
            </div>
            <div class="form-group">
              <label for="archivo-text" class="control-label">Archivo:</label>
              <input type="file" class="form-control" name="file" accept="image/jpeg image/jpg">
            </div>
          </form></div>
            <div class="modal-footer">
                <button class="btn btn-default" type="button" data-bs-dismiss="modal">Cancelar</button>
                <a class="btn btn-primary btn-sm shadow-sm" href="#" onclick="document.getElementById('agregar-media-form').submit()">Subir</a>
            </div>
        </div>
    </div>
</div>

<script>

$(document).on('change','.mover-index-select',function(e) {

  var url = "./ajax/ajax_moverCarouselSlide.php";
  var data = {
    'id_carousel_slide': $(e.currentTarget).data('idcarouselslide'),
    'seq_index': $(e.currentTarget).val()
  };

  console.log(data);

  $.post(url,data,function(response){
    console.log(response);
    location.reload();
  });

});

$(document).on('click','.eliminar-slide-btn',function(e){
  var url = "./ajax/ajax_eliminarCarouselSlide.php";
  var data = {
    'id_carousel_slide': $(e.currentTarget).data('idcarouselslide')
  };
  $.post(url,data,function(response){
    location.reload();
  })
});

</script>
