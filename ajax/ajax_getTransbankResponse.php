<?php

  if($_POST == array()) {
    die();
  }

  require_once "../php/app.php";

  $transbank = $GLOBALS['transbank'];
  $total = $_POST['total'];

  if($total == 0) {
    $response['mensaje'] = "ERROR";
    print json_encode($response,JSON_PRETTY_PRINT);
    die();
  }

  $tb = new Transaccion;
  $tb->setProperties($_POST);
  $tb->total = $total;
  $tb->session_id = rand();
  $tb->amount = $total;
  $tb->save();

  require_once('../vendor_php/autoload.php');

  use Transbank\Webpay\WebpayPlus\Transaction;
  Transbank\Webpay\WebpayPlus::configureForProduction($transbank['codigo_comercio'], $transbank['api_secret_key']);

  $return_url = "https://".$_SERVER['HTTP_HOST']."/?s=resultado-transaccion";

  $transaction = new Transaction();

  $response_tb = $transaction->create($tb->id, $tb->session_id, $tb->amount, $return_url);

  $url = $response_tb->url;
  $token_ws = $response_tb->token;

  $tb->token = $token_ws;
  $tb->save();

  $response['mensaje'] = "OK";
  $response['tokens'] = array(
    "url" => $url,
    "token_ws" => $token_ws
  );

  print json_encode($response,JSON_PRETTY_PRINT);

?>
