<?php
class Cliente {
    private $conexion;
    private $tabla = "cliente";

    public function __construct($db) {
        $this->conexion = $db;
    }

    
    public function leerTodos() {
        $query = "SELECT * FROM " . $this->tabla . " ORDER BY apellido ASC";
        $stmt = $this->conexion->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    
    public function obtenerPorId($id) {
        $query = "SELECT * FROM " . $this->tabla . " WHERE id_cliente = :id";
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

   
    public function crear($datos) {
        $query = "INSERT INTO " . $this->tabla . " (nombre, apellido, direccion, telefono, correo) 
                  VALUES (:nombre, :apellido, :direccion, :telefono, :correo)";
        $stmt = $this->conexion->prepare($query);
        
        $stmt->bindParam(":nombre", $datos['nombre']);
        $stmt->bindParam(":apellido", $datos['apellido']);
        $stmt->bindParam(":direccion", $datos['direccion']);
        $stmt->bindParam(":telefono", $datos['telefono']);
        $stmt->bindParam(":correo", $datos['correo']);
        
        return $stmt->execute();
    }

    
    public function actualizar($datos) {
        $query = "UPDATE " . $this->tabla . " 
                  SET nombre=:nombre, apellido=:apellido, direccion=:direccion, 
                      telefono=:telefono, correo=:correo 
                  WHERE id_cliente=:id";
        $stmt = $this->conexion->prepare($query);
        
        $stmt->bindParam(":nombre", $datos['nombre']);
        $stmt->bindParam(":apellido", $datos['apellido']);
        $stmt->bindParam(":direccion", $datos['direccion']);
        $stmt->bindParam(":telefono", $datos['telefono']);
        $stmt->bindParam(":correo", $datos['correo']);
        $stmt->bindParam(":id", $datos['id_cliente']);
        
        return $stmt->execute();
    }

    
    public function eliminar($id) {
        $query = "DELETE FROM " . $this->tabla . " WHERE id_cliente = :id";
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
}
?>