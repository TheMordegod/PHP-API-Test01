<?php 
use Dotenv\Dotenv;
require "/Xampp/htdocs/First/vendor/autoload.php";

$dotenv = Dotenv::createImmutable("/Xampp/htdocs/First/");
$dotenv->load();

class DataBaseAcess {
    private string $DB_HOST;
    private string $DB_USER;
    private string $DB_PASS;
    private string $DB_NAME;
    private $conn;

    public function __construct() {
        $this->DB_HOST = $_ENV['DB_HOST'];
        $this->DB_USER = $_ENV['DB_USER'];
        $this->DB_PASS = $_ENV['DB_PASS'];
        $this->DB_NAME = $_ENV['DB_NAME'];

        $this->connect();
    }

    public function connect() {
        //remove a instância de conexão para não criar duplicatas
        $this->conn = null;

        //Cria a conexão com o banco e faz tratamento de erros
        try {
            $this->conn = new mysqli($this->DB_HOST,$this->DB_USER,$this->DB_PASS,$this->DB_NAME);
        } 
        catch(mysqli_sql_exception $e) {
            header($e->getMessage(), true, 401);
            die('connection failed: '. $e->getMessage());
        }
                
    }

    public function getConnection():mysqli {
        return $this->conn;
    }

    public function closeConnection() {
        return $this->conn->close();
    }
} 
?>