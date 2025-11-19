<?php

function diaDeLaSemana($date) {
      return date('w', strtotime($date));
    }

    function cantidadDiasMes($mes,$ano) {
      $mes = intval($mes);
      if($mes<1||$mes>12) {
        return 0;
      }
      $cantidad_dias_mes[1] = 31;
      $cantidad_dias_mes[2] = 28;
      $cantidad_dias_mes[3] = 31;
      $cantidad_dias_mes[4] = 30;
      $cantidad_dias_mes[5] = 31;
      $cantidad_dias_mes[6] = 30;
      $cantidad_dias_mes[7] = 31;
      $cantidad_dias_mes[8] = 31;
      $cantidad_dias_mes[9] = 30;
      $cantidad_dias_mes[10] = 31;
      $cantidad_dias_mes[11] = 30;
      $cantidad_dias_mes[12] = 31;
      if($ano%4==0) {
        $cantidad_dias_mes[2] = 29;
      }
      return $cantidad_dias_mes[$mes];
    }

    function intmes2string($intmes) {
      $intmes = $intmes + 0;
      if($intmes<1||$intmes>12) {
        return false;
      }
      $mes[1] = "Enero";
      $mes[2] = "Febrero";
      $mes[3] = "Marzo";
      $mes[4] = "Abril";
      $mes[5] = "Mayo";
      $mes[6] = "Junio";
      $mes[7] = "Julio";
      $mes[8] = "Agosto";
      $mes[9] = "Septiembre";
      $mes[10] = "Octubre";
      $mes[11] = "Noviembre";
      $mes[12] = "Diciembre";

      return $mes[$intmes];
    }

    function w2dia($date) {
      $semana[1] = "Lunes";
      $semana[2] = "Martes";
      $semana[3] = "Miercoles";
      $semana[4] = "Jueves";
      $semana[5] = "Viernes";
      $semana[6] = "Sabado";
      $semana[0] = "Domingo";
      return $semana[diaDeLaSemana($date)];
    }

  $semana = 1;
  if(isset($_GET['semana'])) {
    $semana = $_GET['semana'];
  }

  $mes = date('m');
  if(validaIdExists($_GET,'mes')) {
    $mes = intval($_GET['mes']);
    if($mes < 10) {
        $mes = "0".$mes;
    }
  }

  $ano = date('Y');
  if(isset($_GET['ano'])) {
    $ano = $_GET['ano'];
  }

  $cdem = cantidadDiasMes($mes,$ano);
  $pdm = diaDeLaSemana("1-".$mes."-".$ano);

  if($pdm == 0) {
    $pdm = 7;
  }

  $array_mes[$pdm] = array(
    "dia"=>"01",
    "mes"=>$mes,
    "ano"=>$ano
  );

  for( $i = 1; $i < $pdm; $i++ ) {
    if($mes==1) {
      $dia_p = cantidadDiasMes(12,$ano-1) - ($i-1);
      if($dia_p < 10) {
        $dia_p = "0".$dia_p;
      }
      $array_mes[$pdm - $i] = array(
        "dia"=>$dia_p,
        "mes"=>12,
        "ano"=>$ano-1
      );
    } else {
      $dia_p = cantidadDiasMes($mes-1,$ano) - ($i-1);
      if($dia_p < 10) {
        $dia_p = "0".$dia_p;
      }
      $mes_p = $mes - 1;
      if($mes_p<10) {
        $mes_p = "0".$mes_p;
      }
      $array_mes[$pdm - $i] = array(
        "dia"=>$dia_p,
        "mes"=>$mes_p,
        "ano"=>$ano
      );
    }
  }

  $rango = ceil(($pdm + $cdem)/7) * 7;

  for( $i = $pdm; $i <= $rango; $i++ ) {



    if($i < ($cdem + $pdm)) {
        $dia_p = $i - $pdm + 1;
        if($dia_p < 10) {
            $dia_p = "0".$dia_p;
        }
        $array_mes[$i] = array(
        "dia"=>$dia_p,
        "mes"=>$mes,
        "ano"=>$ano
        );
    } else {
        $dia_p = $i - ($cdem + $pdm) + 1;
        if($dia_p < 10) {
            $dia_p = "0".$dia_p;
        }
        if($mes==12) {
            $array_mes[$i] = array(
            "dia"=> $dia_p,
            "mes"=>"01",
            "ano"=>$ano+1
            );
        } else {
            $mes_p = $mes + 1;
            if($mes_p < 10) {
                $mes_p = "0".$mes_p;
            }
            $array_mes[$i] = array(
            "dia"=>$dia_p,
            "mes"=>$mes_p,
            "ano"=>$ano
            );
        }

    }

  }


  for($i=1;$i<7;$i++) {
    $semana_arr[$i] = $array_mes[$i+(($semana-1)*7)];
  }

  ksort($array_mes);

  $usuario = $GLOBALS['usuario'];

  $primer_dia = $array_mes[1]['ano']."-".$array_mes[1]['mes']."-".$array_mes[1]['dia'];
  $ultimo_dia = end($array_mes)['ano']."-".end($array_mes)['mes']."-".end($array_mes)['dia'];

  $tareas = Tarea::getAll("WHERE destinatario='".$usuario->id."' AND estado!='Realizada' AND plazo_maximo BETWEEN '".$primer_dia."' AND '".$ultimo_dia."'");
  if($usuario->nivel == "Administrador" || $usuario->nivel == "Jefe de Planta") {
    $gastos = Gasto::getAll("WHERE estado!='Pagado' AND date_vencimiento BETWEEN '".$primer_dia."' AND '".$ultimo_dia."'");
    $proyectos = Proyecto::getAll("WHERE date_inicio BETWEEN '".$primer_dia."' AND '".$ultimo_dia."' OR date_finalizacion BETWEEN '".$primer_dia."' AND '".$ultimo_dia."'");
  } else {
    $gastos = array();
    $proyectos = array();
  }
  
  $usuarios = Usuario::getAll("WHERE estado='Activo' AND nivel!='Cliente'");

  if($usuario->nivel == "Jefe de Cocina" || $usuario->nivel == "Administrador") {
    $batches = Batch::getAll();
  } else {
    $batches = [];
  }

  $usuario = $GLOBALS['usuario']; 
  if($usuario->nivel == "Administrador")  {
    $tipos_de_gasto = TipoDeGasto::getAll("ORDER BY nombre asc");
  } {
    $tipos_de_gasto = TipoDeGasto::getAll("ORDER BY nombre asc");

  }

?>
<style>
.calendar-table {
}
.calendar-td {
    height: 90px;
    background-color: white;
    color: black;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-calendar"></i> <b>Planificaci&oacute;n</b></h1>
  </div>
  <div>
  <table>
  <tr>
  <td>
    <select class="form-control date-select me-1" id="mes-select">
      <?php
      for($i = 1; $i<=12; $i++) {
        $mes_int = $i;
        if($mes_int<10) {
            $mes_int = "0".$mes_int;
        }
        $mes_txt = int2mes($i);
        print "<option value='".$mes_int."'>".$mes_txt."</option>";
      }
      ?>
    </select>
  </td>
  <td>
    <select class="form-control date-select" id="ano-select">
      <?php
      for($i = 2023; $i<=date('Y'); $i++) {
        print "<option>".$i."</option>";
      }
      ?>
    </select>
  </td>
    </table>
  </div>
</div>
<hr />
<?php 
Msg::show(7,'Tarea enviada con &eacute;xito','info');
Msg::show(5,'Tareas eliminadas con &eacute;xito','danger');
Msg::show(6,'Tareas enviadas con &eacute;xito','info');
?>
  <table class="table table-bordered calendar-table">
  <thead class="bg-light">
    <tr>
        <th width="14.3%">
          <div class="d-none d-md-inline">
            Lunes
          </div>
          <div class="d-inline d-md-none">
            L
          </div>
        </th>
        <th width="14.3%">
          <div class="d-none d-md-inline">
            Martes
          </div>
          <div class="d-inline d-md-none">
            M
          </div>
        </th>
        <th width="14.3%">
          <div class="d-none d-md-inline">
            Miercoles
          </div>
          <div class="d-inline d-md-none">
            M
          </div>
        </th>
        <th width="14.3%">
          <div class="d-none d-md-inline">
            Jueves
          </div>
          <div class="d-inline d-md-none">
            J
          </div>
        </th>
        <th width="14.3%">
          <div class="d-none d-md-inline">
            Viernes
          </div>
          <div class="d-inline d-md-none">
            V
          </div>
        </th>
        <th width="14.3%">
          <div class="d-none d-md-inline">
            S&aacute;bado
          </div>
          <div class="d-inline d-md-none">
            S
          </div>
        </th>
        <th width="14.3%">
          <div class="d-none d-md-inline">
            Domingo
          </div>
          <div class="d-inline d-md-none">
            D
          </div>
        </th>

  </thead>
  <?php

    for($j=0;$j<floor($rango/7);$j++) {
        print "<tr class='trSemana' data-semana='".($j+1)."' data-mes='".$mes."'>";
        for( $i = 1; $i <= 7; $i++ ) {
          $dia_obj = $array_mes[$i+($j*7)];
            ?>
            <td class='calendar-td p-0'>
              <div class="d-flex justify-content-between">
                  <?php
                  print "<div class='calendar-dia-div px-1 mt-1' data-date='".$dia_obj["ano"]."-".$dia_obj["mes"]."-".$dia_obj["dia"]."'></div>";
                  ?>
                <div class="dropdown">
                  <button class="btn btn-sm dropdown-toggle" type="button" id="dropdown-<?= $dia_obj["ano"]."-".$dia_obj["mes"]."-".$dia_obj["dia"]; ?>" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <?= $dia_obj["dia"]; ?>
                  </button>
                  <div class="dropdown-menu" aria-labelledby="dropdown-<?= $dia_obj["ano"]."-".$dia_obj["mes"]."-".$dia_obj["dia"]; ?>">
                    <a class="dropdown-item calendario-nuevo-tareas-btn" href="#" data-date='<?= $dia_obj["ano"]."-".$dia_obj["mes"]."-".$dia_obj["dia"]; ?>'>
                      <i class="fas fa-fw fa-plus"></i> Nueva Tarea
                    </a>
                    <a class="dropdown-item calendario-nuevo-gastos-btn" href="#" data-date='<?= $dia_obj["ano"]."-".$dia_obj["mes"]."-".$dia_obj["dia"]; ?>'>
                      <i class="fas fa-fw fa-plus"></i> Nuevo Gasto
                    </a>
                  </div>
                </div>
              </div>
            <?php
            print "</td>";
        }
        print "</tr>";
    }

    ?>
</table>

<div class="modal modal-fade" tabindex="-1" role="dialog" id="calendario-nuevo-tareas-modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Nueva Tarea</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="tareas-form">
          <input type="hidden" name="entidad" value="tareas">
          <div class="row">
            <div class="col-6 mb-1">
              Destinatario
            </div>
            <div class="col-6 mb-1">
              <select name="destinatario" class="form-control calendario-nuevo-tareas-inputrequired">
                  <option></option>
                  <?php
                  foreach($usuarios as $usuario) {
                      print "<option value='".$usuario->id."'>".$usuario->nombre."</option>";
                  }
                  $usuario = $GLOBALS['usuario'];
                  ?>
              </select>
            </div>
            <div class="col-6 mb-1">
              Importancia:
            </div>
            <div class="col-6 mb-1">
              <select name="importancia" class="form-control">
                  <option>Normal</option>
                  <option>URGENTE</option>
              </select>
            </div>
            <div class="col-6 mb-1">
              Plazo m&aacute;ximo:
            </div>
            <div class="col-6 mb-1">
              <input type="date" name="plazo_maximo" class="form-control" value="<?= date('Y-m-d'); ?>">
            </div>
            <div class="col-6 mb-1">
                Repetir:
              </div>
              <div class="col-6 mb-1">
                <select name="tareas-repetir" class="form-control">
                  <option>No</option>
                  <option>Cada semana</option>
                  <option>Cada mes</option>
                </select>
              </div>
                <div class="col-6 tareas-repetir">
                  <div class=" mb-1">
                    Hasta:
                  </div>
                </div>
                <div class="col-6 tareas-repetir">
                  <div class="mb-1">
                    <input type="date" name="tareas-hasta" class="form-control">
                  </div>
                </div>
            <div class="col-12 mb-1">
              Tarea:
            </div>
            <div class="col-12 mb-1">
              <textarea name="tarea" class="form-control calendario-nuevo-tareas-inputrequired"></textarea>
            </div>
            <div class="col-12 mt-3 mb-1 text-right">
              <input type="checkbox" name="enviar_email" CHECKED> <b>Enviar Email</b>
            </div>
          </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary btn-sm" id="nuevo-tareas-aceptar" data-bs-dismiss="modal">Agregar</button>
        </div>
      </div>
    </div>
  </div>

<div class="modal modal-fade" tabindex="-1" role="dialog" id="calendario-nuevo-gastos-modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Nuevo Gasto</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="gastos-form" action="./php/procesar.php" method="post" enctype="multipart/form-data">
          <input type="hidden" name="id" value="">
          <input type="hidden" name="entidad" value="gastos">
          <input type="hidden" name="modo" value="nuevo-entidad-con-media">
          <input type="hidden" name="id_usuarios" value="<?= $GLOBALS['usuario']->id; ?>">
          <input type="hidden" name="redirect" value="planificacion&mes=<?= $mes; ?>&ano=<?= $ano; ?>">
          <div class="row">
            <div class="col-6 mb-1">
                Fecha:
              </div>
              <div class="col-6 mb-1">
                <input type="date" value="<?= date('Y-m-d'); ?>" class="form-control" name="creada">
              </div>
              <div class="col-6 mb-1">
                Tipo de Gasto:
              </div>
              <div class="col-6 mb-1">
                <select name="tipo_de_gasto" class="form-control calendario-nuevo-gastos-inputrequired">
                <option></option>
                <?php
                        foreach($tipos_de_gasto as $tipo) {
                            print "<option>".$tipo->nombre."</option>";
                        }
                    ?>
                </select>
              </div>
              <div class="col-6 mb-1">
                &Iacute;tem:
              </div>
              <div class="col-6 mb-1">
                <input type="text" name="item" class="form-control calendario-nuevo-gastos-inputrequired">
              </div>
              <div class="col-6 mb-1">
                Monto:
              </div>
              <div class="col-6 mb-1">
                <div class="input-group">
                  <span class="input-group-text" id="basic-addon1" style="border-radius: 10px 0px 0px 10px">$</span>
                  <input type="text" class="form-control acero" name="monto" value="0">
                </div>
              </div>
              <?php
              if($usuario->nivel=="Administrador") {
                ?>
              <div class="col-6 mb-1">
                Estado:
              </div>
              <div class="col-6 mb-1">
                <select name="estado" class="form-control">
                    <option>Pagado</option>
                    <option>Por Pagar</option>
                </select>
              </div>
              <div class="col-6 mb-1 date_vencimiento">
                Vencimiento:
              </div>
              <div class="col-6 mb-1 date_vencimiento">
                <input type="date" name="date_vencimiento" class="form-control" value="<?= date('Y-m-d'); ?>">
              </div>
              <div class="col-6 mb-1 date_vencimiento">
                Repetir:
              </div>
              <div class="col-6 mb-1 date_vencimiento">
                <select name="repetir" class="form-control">
                  <option>No</option>
                  <option>Cada semana</option>
                  <option>Cada mes</option>
                </select>
              </div>
                <div class="col-6 repetir">
                  <div class="date_vencimiento mb-1">
                    Hasta:
                  </div>
                </div>
                <div class="col-6 repetir">
                  <div class="date_vencimiento mb-1">
                    <input type="date" name="hasta" class="form-control">
                  </div>
                </div>
              <?php
              } else 
              {
              ?>
              <input type="hidden" name="estado" value="Por Pagar">
              <div class="col-6 mb-1">
                Vencimiento:
              </div>
              <div class="col-6 mb-1">
                <input type="date" name="date_vencimiento" class="form-control" value="<?= date('Y-m-d'); ?>">
              </div>
              <?php
              }
              ?>
              <div class="col-12 mb-1">
                Imagen:
              </div>
              <div class="col-12 mb-1">
                <input type="file" name="file" class="form-control">
              </div>
              <div class="col-12 mb-1">
                Comentarios:
              </div>
              <div class="col-12 mb-1">
                <textarea name="comentarios" class="form-control"></textarea>
              </div>
          </div>
        </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary btn-sm" id="nuevo-gastos-aceptar" data-bs-dismiss="modal">Agregar</button>
        </div>
      </div>
    </div>
  </div>

<script>


$(document).on('click','#nuevo-gastos-aceptar',function(){
  $('#gastos-form').submit();
});

    var mes = '<?= $mes; ?>';
    var ano = '<?= $ano; ?>';
    var gastos = <?= json_encode($gastos,JSON_PRETTY_PRINT); ?>;
    var tareas = <?= json_encode($tareas,JSON_PRETTY_PRINT); ?>;
    var proyectos = <?= json_encode($proyectos,JSON_PRETTY_PRINT); ?>;
    var batches = <?= json_encode($batches,JSON_PRETTY_PRINT); ?>;

    $(document).ready(function(){

        $('.date_vencimiento').hide();
        $('.repetir').hide();
        $('.tareas-repetir').hide();
        $('#mes-select').val(mes);
        $('#ano-select').val(ano);
        console.log(gastos);
        
        gastos.forEach(function(gasto) {
            var html = $('.calendar-dia-div[data-date="' + gasto.date_vencimiento + '"]').html();
            var txt = gasto.item;
            if(txt.length > 20) {
              txt = txt.substring(0, 17) + '...';
            }
            html += "<a class='d-none d-md-block badge bg-danger rounded-pill' href='./?s=detalle-gastos&id=" + gasto.id + "'>" + txt + "</a>";
            $('.calendar-dia-div[data-date="' + gasto.date_vencimiento + '"]').html(html);
        });

        tareas.forEach(function(tarea) {
            var html = $('.calendar-dia-div[data-date="' + tarea.plazo_maximo + '"]').html();
            var txt = tarea.tarea;
            if(txt.length > 20) {
              txt = txt.substring(0, 17) + '...';
            }
            html += "<a class='d-none d-md-block badge bg-danger rounded-pill' href='./?s=detalle-tareas&id=" + tarea.id + "'>" + txt + "</a>";
            $('.calendar-dia-div[data-date="' + tarea.plazo_maximo + '"]').html(html);
        });

        proyectos.forEach(function(proyecto) {
          var currentDate = new Date(proyecto.date_inicio);
          var endDate = new Date(proyecto.date_finalizacion);
          var dates = [];
          while (currentDate <= endDate) {
            dates.push(new Date(currentDate));
            currentDate.setDate(currentDate.getDate() + 1);
          }
          dates.forEach(function(date) {
            date = date.toISOString().split('T')[0];
            var html = $('.calendar-dia-div[data-date="' + date + '"]').html();
            html += "<div class='d-none d-md-block badge bg-info rounded-pill'>" + proyecto.nombre + "</div>";
            $('.calendar-dia-div[data-date="' + date + '"]').html(html);
          })
          console.log();
            
        });

        batches.forEach(function(batch) {

          var html = '';

          if(batch.fecha_inicio != '0000-00-00') {
            html = $('.calendar-dia-div[data-date="' + batch.fecha_inicio + '"]').html();
            html += "<div class='d-none d-md-block badge'>Inicio Batch #" + batch.id + "</div>";
            $('.calendar-dia-div[data-date="' + batch.fecha_inicio + '"]').html(html);
          }

          if(batch.fecha_termino != '0000-00-00') {
            html = $('.calendar-dia-div[data-date="' + batch.fecha_termino + '"]').html();
            html += "<div class='d-none d-md-block badge'>Termino Batch #" + batch.id + "</div>";
            $('.calendar-dia-div[data-date="' + batch.fecha_termino + '"]').html(html);
          }

        });

    });

    $(document).on('change','.date-select', function(e) {

        e.preventDefault();

        window.location.href = "./?s=planificacion&mes=" + $('#mes-select').val() + "&ano=" + $('#ano-select').val();

    });

    $(document).on('click','.calendario-nuevo-tareas-btn',function(e) {

      $('#calendario-nuevo-tareas-modal').modal('toggle');
      $('select[name="destinatario_id_usuarios"]').val("");
      $('select[name="importancia"]').val("Normal");
      $('input[name="plazo_maximo"]').val($(e.currentTarget).data('date'));
      $('#nuevo-tareas-aceptar').attr('disabled',true);
        
    });

    $(document).on('change','.calendario-nuevo-tareas-inputrequired',function(e) {
      e.preventDefault();
      var disabled = false;
      if($('select[name="destinatario"]').val() == '' || $('textarea[name="tarea"]').val() == '') {
        disabled = true;
      }
      $('#nuevo-tareas-aceptar').attr('disabled',disabled);
    });

    $(document).on('change','.calendario-nuevo-gastos-inputrequired',function(e) {
      e.preventDefault();
      var disabled = false;
      if($('select[name="tipo_de_gastos"]').val() == '' || $('input[name="item"]').val() == '') {
        disabled = true;
      }
      $('#nuevo-gastos-aceptar').attr('disabled',disabled);
    });

    $(document).on('click','.calendario-nuevo-gastos-btn',function(e) {

      $('#calendario-nuevo-gastos-modal').modal('toggle');
      $('select[name="tipo_de_gastos"]').val('');
      $('input[name="item"]').val('');
      $('#nuevo-gastos-aceptar').attr('disabled',true);
        
    });

    $(document).on('change','select[name="estado"]',function(e) {
      if($(e.currentTarget).val() == 'Por Pagar') {
        $('.date_vencimiento').show(200);
      } else {
        $('.date_vencimiento').hide(200);
      }
    });

    $(document).on('change','select[name="repetir"]',function(e) {
      if($(e.currentTarget).val() != "No") {
        $('.repetir').show(200);
      } else {
        $('.repetir').hide(200);
      }
    });

    $(document).on('change','select[name="tareas-repetir"]',function(e) {
      if($(e.currentTarget).val() != "No") {
        $('.tareas-repetir').show(200);
      } else {
        $('.tareas-repetir').hide(200);
      }
    });

    $(document).on('click','#nuevo-tareas-aceptar',function(e){

      e.preventDefault();

      var url = "./ajax/ajax_guardarTareas.php";
      var data = getDataForm("tareas");

      $.post(url,data,function(raw){
        console.log(raw);
        var response = JSON.parse(raw);
        if(response.status!="OK") {
          alert("Algo fallo");
          return false;
        } else {
          if(response.mensaje == 'Tarea') {
            window.location.href = "./?s=planificacion&mes=" + mes + "&ano=" + ano + "&msg=7";
          } else {
            window.location.href = "./?s=planificacion&mes=" + mes + "&ano=" + ano + "&msg=6";
          }
          
        }
      }).fail(function(){
        alert("No funciono");
      });
    });

</script>
<?php
    $usuario = $GLOBALS['usuario'];

    $order_by = "por defecto";
    $order = "desc";

    if(isset($_GET['order_by'])) {
      if($_GET['order_by'] == "creada") {
        $order_by = "creada";
      } else
      if($_GET['order_by'] == "emisor") {
        $order_by = "emisor";
      } else
      if($_GET['order_by'] == "plazo_maximo") {
        $order_by = "plazo_maximo";
      }
      if($_GET['order_by'] == "estado") {
        $order_by = "estado";
      }
    }

    if(isset($_GET['order'])) {
      if($_GET['order'] == "asc") {
        $order = "asc";
      }
    }

    if($order_by == "por defecto") {
      $tareas_pendientes = Tarea::getAll("WHERE ((tipo_envio='Usuario' AND destinatario='".$usuario->id."') OR (tipo_envio='Nivel' AND destinatario='".$usuario->nivel."')) AND (estado='Pendiente' OR estado='Recibida') ORDER BY importancia desc, id desc");
    } else 
    if($order_by == "emisor") {
      $tareas_pendientes = Tarea::getAll("INNER JOIN usuarios ON tareas.id_usuarios_emisor = usuarios.id WHERE ((tareas.tipo_envio='Usuario' AND tareas.destinatario='".$usuario->id."') OR (tareas.tipo_envio='Nivel' AND tareas.destinatario='".$usuario->nivel."')) AND (tareas.estado='Pendiente' OR tareas.estado='Recibida') ORDER BY usuarios.nombre ".$order);
    } else {
      $tareas_pendientes = Tarea::getAll("WHERE ((tipo_envio='Usuario' AND destinatario='".$usuario->id."') OR (tipo_envio='Nivel' AND destinatario='".$usuario->nivel."')) AND (estado='Pendiente' OR estado='Recibida') ORDER BY ".$order_by." ".$order);
    }

    if($order_by == "por defecto") {
      $tareas_enviadas = Tarea::getAll("WHERE id_usuarios_emisor='".$usuario->id."' AND estado!='Realizada' ORDER BY importancia desc, id desc");
    } else {
      $tareas_enviadas = Tarea::getAll("WHERE id_usuarios_emisor='".$usuario->id."' AND estado!='Realizada' ORDER BY ".$order_by." ".$order);
    }

?>
<style>
.tr-tareas {
  cursor: pointer;
}
</style>
<hr />
<?php 
Msg::show(1,'Tarea enviada con &eacute;xito','info');
Msg::show(2,'Tarea eliminada con &eacute;xito','danger');
Msg::show(3,'Tarea marcada como Recibida','info');
Msg::show(4,'Tarea marcada como Realizada','info');
?>
<h3 class="h5 mb-0 text-gray-800 mb-3">
  Recibidas pendientes (<?= (count($tareas_pendientes)); ?>)
</h3>
<table class="table table-hover table-striped table-sm" id="tareas-table">
  <thead class="thead-dark">
    <tr>
      <th style="width: 30px">
      </th>
      <th>
        <a href="#" class="sort" data-sortorderby="creada">Creada</a>
      </th>
      <th>
        <a href="#" class="sort" data-sortorderby="creada">Transcurrido</a>
      </th>
      <th>
        <a href="#" class="sort" data-sortorderby="plazo_maximo">Plazo m&aacute;ximo</a>
      </th>
      <th style="width: 40%">
        Tarea
      </th>
      <th>
        <a href="#" class="sort" data-sortorderby="emisor">Emisor</a>
      </th>
      <th>
        <a href="#" class="sort" data-sortorderby="estado">Estado</a>
      </th>
      <th>
        <a href="#" class="sort" data-sortorderby="comentarios">Comentarios</a>
      </th>
      <th>
      </th>
    </tr>
  </thead>
  <tbody>
    <?php
      foreach($tareas_pendientes as $tarea) {
        $usuario_emisor = new Usuario($tarea->id_usuarios_emisor);
        $tr_class = "";
        $badge = "";
        if($tarea->importancia == "URGENTE") {
          $tr_class = " table-danger";
          $badge = "<span class='badge bg-danger'>URGENTE</span>";
        }
        $date1 = strtotime(date('Y-m-d'));
        $date2 = strtotime(explode(' ',$tarea->creada)[0]);
        $transcurrido = floor(($date1 - $date2) / (60 * 60 * 24));

        $estado = $tarea->estado;

        if(floor((strtotime(date('Y-m-d')) - strtotime($tarea->plazo_maximo)) / (60 * 60 * 24)) > 0 ) {
          $estado = "Atrasada";
        }
    ?>
    <tr class="tr-tareas<?= $tr_class; ?>" data-idtareas="<?= $tarea->id; ?>">
      <td>
        <input class="tareas-pendientes-checkbox" type="checkbox" data-idtareas="<?= $tarea->id; ?>">
      <td>
        <?= datetime2fecha($tarea->creada); ?>
      </td>
      <td>
      <?= $transcurrido; ?> d&iacute;as
      </td>
      <td>
        <?= datetime2fecha($tarea->plazo_maximo); ?>
      </td>
      <td>
        <?= $badge; ?> <b style="color: black"><?= $tarea->tarea; ?></b>
      </td>
      <td>
        <?= $usuario_emisor->nombre; ?>
      </td>
      <td>
        <b>
          <?= $estado; ?>
        </b>
      </td>
      <td class="text-center">
        <?= count($tarea->tareas_comentarios); ?>
      </td>
      <td>
        <?php
          if($tarea->estado == "Pendiente") {
            ?>
            <a href="#" class="cambiar-estado-btn" data-idtareas="<?= $tarea->id; ?>" data-estado="Recibida">Marcar como Recibida</a>
            <?php
          } else 
          if($tarea->estado == "Recibida") {
            ?>
            <a href="#" class="cambiar-estado-btn" data-idtareas="<?= $tarea->id; ?>" data-estado="Realizada">Marcar como Realizada</a>
            <?php
          }
        ?>
      </td>
    </tr>
    <?php
      }
    ?>
  </tbody>
</table>

<div class="mt-3 mb-3" style="font-size: 0.8em">
  Selecci&oacute;n (<span id="tareas_checkbox_total">0</span>): <a class="btn btn-sm btn-success accion-masiva" href="#tareas_checkbox_total" data-estado="Recibida" data-accion="tareas-marcar-como">Marcar como Recibida</a> <a class="btn btn-sm btn-success accion-masiva" href="#tareas_checkbox_total" data-estado="Realizada" data-accion="tareas-marcar-como">Marcar como Realizada</a>
</div>

<div class="d-sm-flex align-items-center justify-content-between mt-5 mb-3">
  <h3 class="h5 mb-0 text-gray-800">
    Enviadas pendientes (<?= (count($tareas_enviadas)); ?>)
  </h3>
</div>

<table class="table table-hover table-striped table-sm" id="tareas-table">
  <thead class="thead-dark">
    <tr>
      <th style="width: 30px">
      </th>
      <th>
        <a href="#" class="sort" data-sortorderby="creada">Creada</a>
      </th>
      <th>
        <a href="#" class="sort" data-sortorderby="creada">Transcurrido</a>
      </th>
      <th>
        <a href="#" class="sort" data-sortorderby="plazo_maximo">Plazo m&aacute;ximo</a>
      </th>
      <th style="width: 40%">
        Tarea
      </th>
      <th>
        <a href="#" class="sort" data-sortorderby="destinatario">Destinatario</a>
      </th>
      <th>
        <a href="#" class="sort" data-sortorderby="estado">Estado</a>
      </th>
      <th>
        <a href="#" class="sort" data-sortorderby="comentarios">Comentarios</a>
      </th>
    </tr>
  </thead>
  <tbody>
    <?php
      foreach($tareas_enviadas as $tarea) {
        if($tarea->tipo_envio == "Usuario") {
            $usuario_destinatario = new Usuario($tarea->destinatario);
            $destinatario = $usuario_destinatario->nombre;
        } else
        if($tarea->tipo_envio == "Nivel") {
            $destinatario = "Nivel: <b>".$tarea->destinatario."</b>";
        }
        $tr_class = "";
        $badge = "";
        if($tarea->importancia == "URGENTE") {
          $tr_class = " table-danger";
          $badge = "<span class='badge bg-danger'>URGENTE</span>";
        }
        $date1 = strtotime(date('Y-m-d'));
        $date2 = strtotime(explode(' ',$tarea->creada)[0]);
        $transcurrido = floor(($date1 - $date2) / (60 * 60 * 24));

        $estado = $tarea->estado;

        if((floor((strtotime(date('Y-m-d')) - strtotime($tarea->plazo_maximo)) / (60 * 60 * 24)) > 0 ) && $estado!= "Realizada") {
          $estado = "Atrasada";
        }
    ?>
    <tr class="tr-tareas<?= $tr_class; ?>" data-idtareas="<?= $tarea->id; ?>">
      <td>
        <input class="tareas-enviadas-checkbox" type="checkbox" data-idtareas="<?= $tarea->id; ?>">
      </td>
      <td>
        <?= datetime2fecha($tarea->creada); ?>
      </td>
      <td>
        <?= $transcurrido; ?> d&iacute;as
      </td>
      <td>
        <?= datetime2fecha($tarea->plazo_maximo); ?>
      </td>
      <td>
        <?= $badge; ?> <b style="color: black"><?= $tarea->tarea; ?></b>
      </td>
      <td>
        <?= $destinatario; ?>
      </td>
      <td>
        <b>
          <?= $estado; ?>
        </b>
      </td>
      <td class="text-center">
        <?= count($tarea->tareas_comentarios); ?>
      </td>
    </tr>
    <?php
      }
    ?>
  </tbody>
</table>
<div class="mt-3 mb-3" style="font-size: 0.8em">
  Selecci&oacute;n (<span id="tareas_checkbox_enviadas_total">0</span>): <a class="btn btn-sm btn-danger" href="#tareas_checkbox_total_enviadas" id="eliminar-btn">Eliminar</a>
</div>
<div class="d-flex justify-content-between">
  &nbsp;
  <div class="mt-3">
    <a href="./?s=tareas-realizadas" class="d-sm-inline-block btn btn-sm btn-secondary shadow-sm mb-2"><i class="fas fa-fw fa-folder"></i> Realizadas</a>
    <a href="./?s=tareas-enviadas-y-archivadas" class="d-sm-inline-block btn btn-sm btn-secondary shadow-sm mb-2"><i class="fas fa-fw fa-save"></i> Enviadas y Archivadas</a>
  </div>
</div>
<script>

var order_by = "<?= $order_by; ?>";
var order = "<?= $order; ?>";
var change_checkbox_pendientes = [];
var change_checkbox_enviadas = [];

$(document).on('click','.tr-tareas',function(e) {
    window.location.href = "./?s=detalle-tareas&id=" + $(e.currentTarget).data('idtareas');
});

$(document).on('click','.cambiar-estado-btn',function(e) {

  e.preventDefault();
  e.stopPropagation();


  var url = "./ajax/ajax_cambiarEstadoTareas.php";
  var data = {
    'id_tareas': $(e.currentTarget).data('idtareas'),
    'estado': $(e.currentTarget).data('estado')
  };

  $.post(url,data,function(response_raw){
    console.log(response_raw);
    var response = JSON.parse(response_raw);
    if(response.status!="OK") {
      alert(response.mensaje);
      return false;
    } else {
      var msg = 3;
      if(response.obj.estado == "Realizada") {
        msg = 4;
      }
      window.location.href = "./?s=tareas&msg=" + msg + "&id_tareas=" + response.obj.id;
    }
  }).fail(function(){
    alert("No funciono");
  });

});

$(document).on('click','.sort',function(e) {

  if(order == "asc") {
    order = "desc";
  } else {
    order = "asc";
  }

  window.location.href = "./?s=tareas&order_by=" + $(e.currentTarget).data('sortorderby') + "&order=" + order;

});

$(document).on('click','.tareas-pendientes-checkbox',function(e){

  e.stopPropagation();

  change_checkbox_pendientes = [];
  total = 0;

  $('.tareas-pendientes-checkbox').each(function(){
    if($(this).is(':checked')){
      total += 1;
      change_checkbox_pendientes.push($(this).data('idtareas'));
    }
  })
  $('#tareas_checkbox_total').html(total);
  $('#accion-masiva-eliminar-modal-total').html(total);

});

$(document).on('click','.tareas-enviadas-checkbox',function(e){

  e.stopPropagation();

  change_checkbox_enviadas = [];
  total = 0;

  $('.tareas-enviadas-checkbox').each(function(){
    if($(this).is(':checked')){
      total += 1;
      change_checkbox_enviadas.push($(this).data('idtareas'));
    }
  })
  $('#tareas_checkbox_enviadas_total').html(total);
  //$('#accion-masiva-eliminar-modal-total').html(total);

});

$(document).on('click','#eliminar-btn',function(e){

  if(change_checkbox_enviadas.length == 0) {
    return 0;
  }

  var url = "./ajax/ajax_accionMasiva.php";
  var data = {
    'table_name': 'tareas',
    'ids': change_checkbox_enviadas,
    'accion': 'eliminar'
  };

  console.log(data);

  $.post(url,data,function(response_raw){
    console.log(response_raw);
    var response = JSON.parse(response_raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=tareas&msg=5";
    }
  }).fail(function(){
    alert("No funciono");
  });


});

$(document).on('click','.accion-masiva',function(e){

  if(change_checkbox_pendientes.length == 0) {
    return 0;
  }

  var url = "./ajax/ajax_accionMasiva.php";
  var data = {
    'table_name': 'tareas',
    'accion': 'tareas-marcar-como',
    'ids': change_checkbox_pendientes,
    'estado': $(e.currentTarget).data('estado')
  };

  console.log(data);
  

  $.post(url,data,function(response_raw){
    console.log(response_raw);
    var response = JSON.parse(response_raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=tareas&msg=3";
    }
  }).fail(function(){
    alert("No funciono");
  });


});


</script>
