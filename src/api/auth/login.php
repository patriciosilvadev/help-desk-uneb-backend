<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once(dirname(__file__)."/../../model/tecnico.php");



$data = json_decode(file_get_contents("php://input"));

if(empty($data->usuario) || empty($data->senha)) {
  echo json_encode(array(
    "error" => 409,
    "mensagem" => "Tem que enviar email e senha, né amigão?"
  ));
  return false;
}

$tecnico = new Tecnico();
$tecnico->setLogin($data->usuario);
$tecnico->setSenha($data->senha);
if($tecnico->auth()) {
  echo json_encode(array(
    "token" => $tecnico->toJWT(),
    "mensagem" => "Você está autenticado!",
  ));
} else {
  echo json_encode(array(
    "error" => 400,
    "mensagem" => "Senha ou login incorreto."
  ));
}


?>