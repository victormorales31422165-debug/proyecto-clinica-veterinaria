<?php
class DB {
    private $host = "localhost";
    private $db_name = "clinica_colibri"; // Cambia esto al nombre de tu BD
    private $usuario = "root";
    private $password = "";
    public $conexion;

    public function conectar() {
        $this->conexion = null;
        try {
            $this->conexion = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->usuario,
                $this->password
            );
            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conexion->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }
        return $this->conexion;
    }
}
?>