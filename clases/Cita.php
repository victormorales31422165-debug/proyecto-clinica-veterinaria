<?php
class Cita {
    private $conexion;
    private $tabla = "cita";

    // Atributos exactos según tu base de datos
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

    // 1. LEER TODAS LAS CITAS (Para el administrador o lista general)
    public function leerTodas() {
        // Hacemos un JOIN para que en la tabla se vea el nombre de la mascota y no solo el ID
        $query = "SELECT c.*, m.nombre AS nombre_mascota 
                  FROM " . $this->tabla . " c
                  LEFT JOIN mascota m ON c.id_mascota = m.id_mascota 
                  ORDER BY c.fecha_hora DESC";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // 2. CREAR NUEVA CITA
    public function crear() {
        $query = "INSERT INTO " . $this->tabla . " 
                  SET motivo=:motivo, 
                      estado=:estado, 
                      id_mascota=:id_mascota, 
                      id_veterinario=:id_veterinario, 
                      diagnostico=:diagnostico, 
                      fecha_hora=:fecha_hora";

        $stmt = $this->conexion->prepare($query);

        // Limpiar datos por seguridad
        $this->motivo = htmlspecialchars(strip_tags($this->motivo));
        $this->estado = htmlspecialchars(strip_tags($this->estado));
        $this->id_mascota = htmlspecialchars(strip_tags($this->id_mascota));
        $this->id_veterinario = htmlspecialchars(strip_tags($this->id_veterinario));
        $this->diagnostico = htmlspecialchars(strip_tags($this->diagnostico));
        $this->fecha_hora = htmlspecialchars(strip_tags($this->fecha_hora));

        // Vincular parámetros
        $stmt->bindParam(":motivo", $this->motivo);
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":id_mascota", $this->id_mascota);
        $stmt->bindParam(":id_veterinario", $this->id_veterinario);
        $stmt->bindParam(":diagnostico", $this->diagnostico);
        $stmt->bindParam(":fecha_hora", $this->fecha_hora);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // 3. ACTUALIZAR ESTADO (Para cuando confirmas o cancelas una cita)
    public function actualizarEstado($id, $nuevo_estado) {
        $query = "UPDATE " . $this->tabla . " SET estado = :estado WHERE id_cita = :id_cita";
        $stmt = $this->conexion->prepare($query);
        
        $stmt->bindParam(":estado", $nuevo_estado);
        $stmt->bindParam(":id_cita", $id);

        return $stmt->execute();
    }

    // 4. AGREGAR DIAGNÓSTICO (Para cuando el veterinario termina la cita)
    public function agregarDiagnostico($id, $texto_diagnostico) {
        $query = "UPDATE " . $this->tabla . " 
                  SET diagnostico = :diagnostico, estado = 'completada' 
                  WHERE id_cita = :id_cita";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(":diagnostico", $texto_diagnostico);
        $stmt->bindParam(":id_cita", $id);

        return $stmt->execute();
    }

    // 5. ELIMINAR CITA
    public function eliminar($id) {
        $query = "DELETE FROM " . $this->tabla . " WHERE id_cita = :id_cita";
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(":id_cita", $id);
        
        return $stmt->execute();
    }

    // Método nuevo para agregar a clases/Cita.php
    public function obtenerDetallesCompletos($id_cita) {
    $query = "SELECT c.*, m.nombre as mascota, m.especie, m.raza, m.edad, cl.nombre as dueño, cl.apellido, cl.telefono 
              FROM " . $this->tabla . " c
              JOIN mascota m ON c.id_mascota = m.id_mascota
              JOIN cliente cl ON m.id_cliente = cl.id_cliente
              WHERE c.id_cita = :id_cita";

    $stmt = $this->conexion->prepare($query);
    $stmt->bindParam(":id_cita", $id_cita);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna un solo registro
    }

    // Dentro de la clase Cita en clases/Cita.php
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

    // Añadir/Ajustar estos métodos en clases/Cita.php

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

    // Dentro de clases/Cita.php

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
    
    // Dentro de clases/Cita.php

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