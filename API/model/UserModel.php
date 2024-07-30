<?php 
class UserModel{
    private $conn;

    public function __construct(mysqli $db)
    {
        $this->conn = $db;
    }

    //retorna todos os usuarios do banco
    public function selectAllUsers():array {
            $query = 'SELECT * FROM `usuario`';
            $result = $this->conn->query($query);
            $rows = [];
    
            while($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
    
            return $rows;
    }

    //Retorna um usuario especifico
    public function selectUserById(int $id):array {
        $query = 'SELECT * FROM `usuario` WHERE id = ?';
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i',$id); 
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = [];

        while($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        return $rows;
    }

    //Adiciona um novo Usuario no banco
    public function addNewUser(string $newUserData):array {
        $query = 'INSERT INTO `usuario` (nome) Values (?)';
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $newUserData);
        $stmt->execute();
        return ['affectedRows' => $stmt->affected_rows];
    }

    //Remove um Usuario no banco
    public function deleteUser(int $userId):array {
        $query = 'DELETE FROM `usuario` WHERE `id` = (?)';
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $affectedRows = $stmt->affected_rows;
        return ['affectedRows' => $affectedRows];
    }
    
    //Altera um Usuario no banco
    public function patchUser(int $userId, string $newUserName):array {
        $query = 'UPDATE `usuario` SET `nome` = (?) WHERE `id` = (?)';
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('si', $newUserName, $userId);
        $stmt->execute();
        $affectedRows = $stmt->affected_rows;
        return ['affectedRows' => $affectedRows];
    }
}
?>