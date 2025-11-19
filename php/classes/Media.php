<?php

const IMAGE_HANDLERS = [
  IMAGETYPE_JPEG => [
      'load' => 'imagecreatefromjpeg',
      'save' => 'imagejpeg',
      'quality' => 100
  ],
  IMAGETYPE_PNG => [
      'load' => 'imagecreatefrompng',
      'save' => 'imagepng',
      'quality' => 0
  ],
  IMAGETYPE_GIF => [
      'load' => 'imagecreatefromgif',
      'save' => 'imagegif'
  ]
];

class Media extends Base {

  public $url = "";
  public $nombre = "";
  public $descripcion = "";
  public $tipo = "";

  public function __construct($id = null) {
    $this->tableName("media");
    if($id) {
      $this->id = $id;
      $info = $this->getInfoDatabase('id');
      $this->setProperties($info);
    }
  }

  public function newMedia($file) {

    $base_dir = $GLOBALS['base_dir'];
    $this->save();

    $file_name_arr = explode(".",basename($file['name']));
    $extension = end($file_name_arr);
    $dir_subida = $base_dir."/media/images/";
    $file_name = $this->id.".".$extension;
    $fichero_subido = $dir_subida.$file_name;

    $fichero_thumbnails = $base_dir."/media/thumbnails/";

    if(file_exists($fichero_subido)) {
      unlink($fichero_subido);
    }

    if (move_uploaded_file($file['tmp_name'], $fichero_subido)) {
      chmod($fichero_subido, 0777);
      $this->url = $file_name;
      $this->tipo = $extension;
      $this->save();

      $url_thumbnail_50 = $fichero_thumbnails."50/".$this->id.".".$extension;
      $url_thumbnail_320 = $fichero_thumbnails."320/".$this->id.".".$extension;
      $url_thumbnail_640 = $fichero_thumbnails."640/".$this->id.".".$extension;
      $this->createThumbnail($fichero_subido,$url_thumbnail_50,50);
      $this->createThumbnail($fichero_subido,$url_thumbnail_320,320);
      $this->createThumbnail($fichero_subido,$url_thumbnail_640,640);
    }

  }

  public function createThumbnail($src, $dest, $targetWidth, $targetHeight = null) {



    // 1. Load the image from the given $src
    // - see if the file actually exists
    // - check if it's of a valid image type
    // - load the image resource

    // get the type of the image
    // we need the type to determine the correct loader
    $type = exif_imagetype($src);

    // if no valid type or no handler found -> exit
    if (!$type || !IMAGE_HANDLERS[$type]) {
        return null;
    }

    // load the image with the correct loader
    $image = call_user_func(IMAGE_HANDLERS[$type]['load'], $src);

    // no image found at supplied location -> exit
    if (!$image) {
        return null;
    }


    // 2. Create a thumbnail and resize the loaded $image
    // - get the image dimensions
    // - define the output size appropriately
    // - create a thumbnail based on that size
    // - set alpha transparency for GIFs and PNGs
    // - draw the final thumbnail

    // get original image width and height
    $width = imagesx($image);
    $height = imagesy($image);

    // maintain aspect ratio when no height set
    if ($targetHeight == null) {

        // get width to height ratio
        $ratio = $width / $height;

        // if is portrait
        // use ratio to scale height to fit in square
        if ($width > $height) {
            $targetHeight = floor($targetWidth / $ratio);
        }
        // if is landscape
        // use ratio to scale width to fit in square
        else {
            $targetHeight = $targetWidth;
            $targetWidth = floor($targetWidth * $ratio);
        }
    }

    // create duplicate image based on calculated target size
    $thumbnail = imagecreatetruecolor($targetWidth, $targetHeight);

    // set transparency options for GIFs and PNGs
    if ($type == IMAGETYPE_GIF || $type == IMAGETYPE_PNG) {

        // make image transparent
        imagecolortransparent(
            $thumbnail,
            imagecolorallocate($thumbnail, 0, 0, 0)
        );

        // additional settings for PNGs
        if ($type == IMAGETYPE_PNG) {
            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);
        }
    }

    // copy entire source image to duplicate image and resize
    imagecopyresampled(
        $thumbnail,
        $image,
        0, 0, 0, 0,
        $targetWidth, $targetHeight,
        $width, $height
    );


    // 3. Save the $thumbnail to disk
    // - call the correct save method
    // - set the correct quality level

    // save the duplicate version of the image to disk
    return call_user_func(
        IMAGE_HANDLERS[$type]['save'],
        $thumbnail,
        $dest,
        IMAGE_HANDLERS[$type]['quality']
    );

  }

  public function deleteMedia() {

    $base_dir = $GLOBALS['base_dir'];

    $dir_subida = $base_dir."/media/images/";
    $fichero_thumbnails = $base_dir."/media/thumbnails/";

    $url_image = $dir_subida = $base_dir."/media/images/".$this->url;
    $url_thumbnail_50 = $fichero_thumbnails."50/".$this->url;
    $url_thumbnail_320 = $fichero_thumbnails."320/".$this->url;
    $url_thumbnail_640 = $fichero_thumbnails."640/".$this->url;

    $files = array(
      $url_image,
      $url_thumbnail_50,
      $url_thumbnail_320,
      $url_thumbnail_640
    );

    foreach($files as $file) {
      if(file_exists($file) && !is_dir($file)) {
        unlink($file);
      }
    }

    $this->delete();
  }


}

 ?>
