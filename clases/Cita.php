<?php
class Cita {
    private $conexion;
    private $tabla = "cita";

    
    public $id_cita;
    public $motivo;
    public $estado;
    public $id_mascota;
    public $id_veterinario;
    public $diagnostico;
    public $fecha_hora;

    public function __construct($db) {
        $this->conexion = $db;
    }

    
    public function leerTodas() {
        $query = "SELECT c.*, m.nombre AS nombre_mascota, u.nombre AS v_nom 
                  FROM " . $this->tabla . " c
                  LEFT JOIN mascota m ON c.id_mascota = m.id_mascota 
                  LEFT JOIN usuario u ON c.id_veterinario = u.id_veterinario 
                  ORDER BY c.fecha_hora DESC";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    
    public function obtenerPorId($id) {
        $query = "SELECT * FROM " . $this->tabla . " WHERE id_cita = :id";
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

   
    public function crearCitaCompleta($datos) {
        $query = "INSERT INTO " . $this->tabla . " (fecha_hora, motivo, id_mascota, id_veterinario, estado) 
                  VALUES (:fh, :mot, :id_m, :id_v, 'pendiente')";
        
        $stmt = $this->conexion->prepare($query);
        
        $stmt->bindParam(":fh", $datos['fecha_hora']);
        $stmt->bindParam(":mot", $datos['motivo']);
        $stmt->bindParam(":id_m", $datos['id_mascota']);
        
        
        $id_v = !empty($datos['id_veterinario']) ? $datos['id_veterinario'] : null;
        $stmt->bindParam(":id_v", $id_v, is_null($id_v) ? PDO::PARAM_NULL : PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    
    public function actualizarCitaCompleta($datos) {
        $query = "UPDATE " . $this->tabla . " 
                  SET fecha_hora=:fh, motivo=:mot, id_veterinario=:id_v, estado=:est 
                  WHERE id_cita=:id_c";
        
        $stmt = $this->conexion->prepare($query);
        
        $stmt->bindParam(":fh", $datos['fecha_hora']);
        $stmt->bindParam(":mot", $datos['motivo']);
        $stmt->bindParam(":est", $datos['estado']);
        $stmt->bindParam(":id_c", $datos['id_cita']);
        
        $id_v = !empty($datos['id_veterinario']) ? $datos['id_veterinario'] : null;
        $stmt->bindParam(":id_v", $id_v, is_null($id_v) ? PDO::PARAM_NULL : PDO::PARAM_INT);
        
        return $stmt->execute();
    }

   
    public function agregarDiagnostico($id, $texto_diagnostico) {
        $query = "UPDATE " . $this->tabla . " 
                  SET diagnostico = :diagnostico, estado = 'completada' 
                  WHERE id_cita = :id_cita";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(":diagnostico", $texto_diagnostico);
        $stmt->bindParam(":id_cita", $id);

        return $stmt->execute();
    }

    
    public function actualizarEstado($id, $nuevo_estado) {
        $query = "UPDATE " . $this->tabla . " SET estado = :estado WHERE id_cita = :id_cita";
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(":estado", $nuevo_estado);
        $stmt->bindParam(":id_cita", $id);
        return $stmt->execute();
    }

    
    public function eliminar($id) {
        $query = "DELETE FROM " . $this->tabla . " WHERE id_cita = :id_cita";
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(":id_cita", $id);
        return $stmt->execute();
    }

    
    public function leerResumenDashboard() {
        $query = "SELECT c.*, m.nombre as mascota_nombre, u.nombre as vet_nombre 
                  FROM " . $this->tabla . " c 
                  JOIN mascota m ON c.id_mascota = m.id_mascota 
                  LEFT JOIN usuario u ON c.id_veterinario = u.id_veterinario 
                  ORDER BY c.fecha_hora ASC LIMIT 5";

        $stmt = $this->conexion->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    
    public function leerPorVeterinario($id_vet) {
        $query = "SELECT c.*, m.nombre as mascota, cl.nombre as dueño, cl.apellido 
                  FROM " . $this->tabla . " c 
                  JOIN mascota m ON c.id_mascota = m.id_mascota 
                  JOIN cliente cl ON m.id_cliente = cl.id_cliente
                  WHERE c.id_veterinario = :id_vet 
                  ORDER BY c.fecha_hora DESC";
                  
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(":id_vet", $id_vet);
        $stmt->execute();
        return $stmt;
    }

    
    public function obtenerDetallesCompletos($id_cita) {
        $query = "SELECT c.*, m.nombre as mascota, m.especie, m.raza, m.edad, cl.nombre as dueño, cl.apellido, cl.telefono 
                  FROM " . $this->tabla . " c
                  JOIN mascota m ON c.id_mascota = m.id_mascota
                  JOIN cliente cl ON m.id_cliente = cl.id_cliente
                  WHERE c.id_cita = :id_cita";

        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(":id_cita", $id_cita);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    
    public function obtenerDatosReporte($id_cita) {
        $query = "SELECT c.*, m.nombre as mascota, m.especie, m.raza, m.edad, 
                         cl.nombre as dueño, cl.apellido, 
                         u.nombre as vet_nom, u.especialidad as vet_esp
                  FROM " . $this->tabla . " c
                  JOIN mascota m ON c.id_mascota = m.id_mascota
                  JOIN cliente cl ON m.id_cliente = cl.id_cliente
                  JOIN usuario u ON c.id_veterinario = u.id_veterinario
                  WHERE c.id_cita = :id_cita";

        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(":id_cita", $id_cita);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>