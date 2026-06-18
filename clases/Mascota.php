<?php
class Mascota {
    private $conexion;
    private $tabla = "mascota";

    public $id_mascota;
    public $nombre;
    public $especie;
    public $raza;
    public $edad;
    public $id_cliente;

    public function __construct($db) {
        $this->conexion = $db;
    }

   
    public function leerTodas() {
        $query = "SELECT m.*, c.nombre as dueño_nombre, c.apellido as dueño_apellido 
                  FROM " . $this->tabla . " m
                  LEFT JOIN cliente c ON m.id_cliente = c.id_cliente
                  ORDER BY m.id_mascota DESC";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    
    public function obtenerPorId($id) {
        $query = "SELECT * FROM " . $this->tabla . " WHERE id_mascota = :id";
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    

    public function crear($datos) {
    $query = "INSERT INTO " . $this->tabla . " (nombre, especie, raza, edad, id_cliente) 
              VALUES (:nombre, :especie, :raza, :edad, :id_cliente)";
    $stmt = $this->conexion->prepare($query);
    
    $stmt->bindParam(":nombre", $datos['nombre']);
    $stmt->bindParam(":especie", $datos['especie']);
    $stmt->bindParam(":raza", $datos['raza']);
    $stmt->bindParam(":edad", $datos['edad']);
    $stmt->bindParam(":id_cliente", $datos['id_cliente']);
    
    return $stmt->execute();
    }

    public function actualizar($datos) {
    $query = "UPDATE " . $this->tabla . " 
              SET nombre=:nombre, especie=:especie, raza=:raza, edad=:edad, id_cliente=:id_cliente 
              WHERE id_mascota=:id";
    $stmt = $this->conexion->prepare($query);
    
    $stmt->bindParam(":nombre", $datos['nombre']);
    $stmt->bindParam(":especie", $datos['especie']);
    $stmt->bindParam(":raza", $datos['raza']);
    $stmt->bindParam(":edad", $datos['edad']);
    $stmt->bindParam(":id_cliente", $datos['id_cliente']);
    $stmt->bindParam(":id", $datos['id_mascota']);
    
    return $stmt->execute();
    }
}
?>