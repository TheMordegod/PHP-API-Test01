<?php 
namespace api\controller;

use api\model\userModel;
use mysqli;
use Exception;

class UserController {
    private $userModel;
    private $requestMethod;
    private $queryParam;

    public function __construct(mysqli $db, $requestMethod, $queryParam){
        $this->userModel = new UserModel($db);
        $this->requestMethod = strtoupper($requestMethod);
        $this->queryParam = $queryParam;
    }
  
    public function processRequest() {
        $routes = [
            'GET' => $this->getRoute(),
            'POST' => $this->postRoute(),
            'DELETE' => $this->deleteRoute(),
            'PATCH' => $this->patchRoute(),
        ];

        //verifica se a rota existe e executa a função apropriada
        if(isset($routes[$this->requestMethod])) {
            $processedRequest = $routes[$this->requestMethod]();
            return $processedRequest;
        }
        
       return $this->exceptionHandler(400, "Bad Request");
    }
    // api/users/{?} 
    private function getRoute():callable { 
        return function() {
            //retorna todos os usuarios se não houver parametros
            if($this->queryParam === null) {
                $result = json_encode($this->userModel->selectAllUsers());
                return $result;
            } 
            
            //seleciona por id se ouver query
            $result = json_encode($this->userModel->selectUserById((int) $this->queryParam));
            return $result;
        };
    }

    // api/users/{ID}
    private function deleteRoute():callable {
        return function() {                        
                try {
                    //Sanitiza a query
                    $sanitizedInput = $this->sanitizeString($this->queryParam);
                    $id = (int) $sanitizedInput;  
    
                    //executa a exclusão no banco  
                    $dataBaseResponse = $this->userModel->deleteUser($id);
    
                    //prepara a resposta baseado nas linhas afetadas
                    $result = $this->responseHandler($dataBaseResponse['affectedRows']);
                    http_response_code($result['status']);
                    return json_encode([$result], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
                } catch (Exception $e) {
                  return $this->exceptionHandler($e->getCode(), $e->getMessage());
                } 
            };
    }

    // api/users/{Nome}
    private function postRoute():callable {
        return function() {       
                try {
                    //Sanitiza a string
                    $sanitizedInput = $this->sanitizeString($this->queryParam);

                    //executa o POST e recebe as linhas afetadas
                    $dataBaseResponse = $this->userModel->addNewUser($sanitizedInput);
                    $result = $this->responseHandler($dataBaseResponse['affectedRows']);

                    http_response_code($result['status']);
                    return json_encode([$result], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);     

                } catch (Exception $e) {
                   return $this->exceptionHandler($e->getCode(), $e->getMessage());
                };
            };
    }
    
    // api/users/{ID, Nome}
    private function patchRoute():callable {       
        return function() {
                try {
                    $sanitizedQuery = $this->sanitizeString($this->queryParam);
                    $queryArray = explode(',', $sanitizedQuery);  

                    //verifica se a query terá 2 parametros: ID e Nome
                    if(count($queryArray) !== 2) {
                        throw new Exception('Formado de consulta fora do padrão. espera-se: {ID, NAME}.', 400);
                    }

                    //Atribui, converte e sanitiza a query para sua devida variavel
                    $id = (int) $queryArray[0];
                    $newUserName = $this->sanitizeString($queryArray[1]);
    
                    //Verifica se o ID é valido
                    if($id <= 0) {           
                        throw new Exception('ID Invalido.', 400);
                    }  
                    
                    //execução da consulta no banco e retorno em linhas afetadas
                    $dataBaseResponse = $this->userModel->patchUser($id, $newUserName);         
                    $result = $this->responseHandler($dataBaseResponse['affectedRows']);

                    http_response_code($result['status']);
                    return json_encode([$result], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
                } catch (Exception $e) {
                   return $this->exceptionHandler($e->getCode(), $e->getMessage());
                }            
        };
    }

    //concentra e devolve as respostas e o http status code 
    private function responseHandler(int $affectedRows):array {
        $requestType = [
            'POST' => [
                'messageSucess' => 'Usuário adicionado com sucesso!',
                'messageFail' => 'Erro ao criar um usuário!',
                'httpCodeSucess' => 201,
                'httpCodeFail' => 400
            ],
            'DELETE' => [
                'messageSucess' => 'Usuário deletado com sucesso!',
                'messageFail' => 'Usuário não encontrado!',
                'httpCodeSucess' => 200,
                'httpCodeFail' => 404
            ],
            'PATCH' => [
                'messageSucess' => 'Alteracão realizada com sucesso!',
                'messageFail' => 'Nenhuma mudança foi feita, ID não existente ou os dados são repetidos.',
                'httpCodeSucess' => 200,
                'httpCodeFail' => 404
            ],
        ];

        $response = [];

        //se ouve alguma linha alterada no banco, a consulta teve sucesso
        if($affectedRows > 0) {
            $response['message'] = $requestType[$this->requestMethod]['messageSucess'];
            $response['status'] = $requestType[$this->requestMethod]['httpCodeSucess'];
            return $response;
        } else {
            $response['message'] = $requestType[$this->requestMethod]['messageFail'];
            $response['status'] = $requestType[$this->requestMethod]['httpCodeFail'];
            return $response;
        }   
    }

    //Limpa as impurezas da string
    private function sanitizeString(?string $term) {
        if(empty($term)) {
            throw new Exception("Campo vazio invalido!", 400);
        };

        $newTerm = trim($term);
        $newTerm = htmlspecialchars($newTerm, ENT_QUOTES, 'UTF-8');

        return $newTerm;
    }

    //gerencia as exceções
    private function exceptionHandler(int $httpCode, string $message) {
        http_response_code($httpCode);
        return json_encode([["status" => $httpCode, "message" => $message]]);
    }
}  
?>