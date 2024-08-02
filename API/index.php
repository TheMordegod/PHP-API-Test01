<?php 
require __DIR__ . "/../vendor/autoload.php";

use api\database\DataBaseAcess;
use api\controller\UserController;

//Headers para não deixar o CORS reclamar
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PATCH,DELETE");

//Criar uma conexão unica com o banco e conectar
$database = new DatabaseAcess();
$conn = $database->getConnection();

//Variaveis para armazenar os dados da URL
$method = $_SERVER['REQUEST_METHOD'];
$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$url = urldecode($url);

$url = explode( '/', $url );
$queryParam = null;

//Verifica se existe um parametro na URL
if(isset($url[3]) && $url[3] != "") {
    $queryParam = $url[3];
}

// Todos os endpoints começarão com API, qualquer outra coisa será not found!
if ($url[1] !== 'api') {
    header("HTTP/1.1 404 Not Found");
    exit();
}

//Rota Users
if ($url[2] === 'users') {
   $userController = new UserController($conn,$method,$queryParam);
   echo ($userController->processRequest());
   $database->closeConnection();
   exit();
}

header("HTTP/1.1 400 Bad request",true,400);
exit();
?>