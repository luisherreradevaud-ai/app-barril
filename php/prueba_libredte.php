<pre><?php


  require_once "app.php";
  require_once "libredte2.php";


  $entrega = new Entrega(490);
  $entrega_productos = EntregaProducto::getAll("WHERE id_entregas='".$entrega->id."'");
  $cliente = new Cliente($entrega->id_clientes);

  print_r($cliente);
  print_r($entrega);
  print_r($entrega_productos);

  $data = dataEntrada2json($cliente,$entrega,$entrega_productos);
  print_r($data);
  

  //$body = LIBREDTE_emison($data);
  //print_r($body);




  //$xml = LIBREDTE_generar($body);
  //print_r($xml);

  //print LIBREDTE_getDatos();



?>
