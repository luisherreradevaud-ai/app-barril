<?php

  class Msg {

    public static function show($number,$msg,$type) {
        if( $number != '' ) {
          if(!isset($_GET['msg'])) {
            return false;
          }
          if($_GET['msg']!=$number) {
              return false;
          }
        }
        ?>
        <div class="alert alert-<?= $type; ?> alert-dismissible" role="alert">
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          <div class="alert-message">
            <?= $msg; ?>
          </div>
        </div>
        <?php
    }
  }
  
?>
