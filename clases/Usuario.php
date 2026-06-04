<?php
class Usuario {
    private $conexion;
    private $tabla = "usuario";

    public $id_veterinario;
    public $nombre;
    public $rol;
    public $especialidad;
    public $correo;
    public $telefono;
    public $usuario;
    public $password;

    public function __construct($db) {
        $this->conexion = $db;
    }

    // 1. LOGIN
    public function login($user, $pass) {
        $query = "SELECT * FROM " . $this->tabla . " WHERE usuario = :user LIMIT 0,1";
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(":user", $user);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            // Verifica si la contraseña coincide (ajusta si usas password_hash)
            if (password_verify($pass, $fila['password']) || $pass == $fila['password']) {
                return $fila;
            }
        }
        return false;
    }

    // 2. VERIFICAR SI EL USUARIO YA EXISTE
    public function existeUsuario($user) {
        $query = "SELECT id_veterinario FROM " . $this->tabla . " WHERE usuario = :user";
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(":user", $user);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // 3. OBTENER PERFIL POR ID
    public function obtenerPerfil($id) {
        $query = "SELECT * FROM " . $this->tabla . " WHERE id_veterinario = :id";
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 4. ACTUALIZAR PERFIL (VISTA VETERINARIO)
    public function actualizarPerfil($id, $datos) {
        $query = "UPDATE " . $this->tabla . " 
                  SET nombre=:nombre, especialidad=:especialidad, correo=:correo";
        $params = [
            ":nombre" => $datos['nombre'],
            ":especialidad" => $datos['especialidad'],
            ":correo" => $datos['correo'],
            ":id" => $id
        ];
        if (!empty($datos['password'])) {
            $query .= ", password=:password";
            $params[":password"] = $datos['password'];
        }
        $query .= " WHERE id_veterinario = :id";
        $stmt = $this->conexion->prepare($query);
        return $stmt->execute($params);
    }

    // 5. LISTAR VETERINARIOS (VISTA ADMIN)
    public function listarVeterinarios() {
        $query = "SELECT * FROM " . $this->tabla . " WHERE rol = 'veterinario' ORDER BY nombre ASC";
        $stmt = $this->conexion->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // 6. CREAR VETERINARIO (VISTA ADMIN)
    public function crearVeterinarioDesdeAdmin($datos) {
        $query = "INSERT INTO " . $this->tabla . " (nombre, rol, especialidad, correo, usuario, password) 
                  VALUES (:nombre, 'veterinario', :especialidad, :correo, :usuario, :password)";
        $stmt = $this->conexion->prepare($query);
        $pass_default = 'veterinaria';
        $stmt->bindParam(":nombre", $datos['nombre']);
        $stmt->bindParam(":especialidad", $datos['especialidad']);
        $stmt->bindParam(":correo", $datos['correo']);
        $stmt->bindParam(":usuario", $datos['usuario']);
        $stmt->bindParam(":password", $pass_default);
        return $stmt->execute();
    }

    // 7. ACTUALIZAR VETERINARIO (VISTA ADMIN)
    public function actualizarVeterinarioDesdeAdmin($id, $datos) {
        $query = "UPDATE " . $this->tabla . " 
                  SET nombre=:nombre, especialidad=:especialidad, correo=:correo, usuario=:usuario";
        $params = [
            ":nombre" => $datos['nombre'],
            ":especialidad" => $datos['especialidad'],
            ":correo" => $datos['correo'],
            ":usuario" => $datos['usuario'],
            ":id" => $id
        ];
        if (!empty($datos['password'])) {
            $query .= ", password=:password";
            $params[":password"] = $datos['password'];
        }
        $query .= " WHERE id_veterinario = :id AND rol = 'veterinario'";
        $stmt = $this->conexion->prepare($query);
        return $stmt->execute($params);
    }

    // 8. ELIMINAR VETERINARIO
    public function eliminarVeterinario($id) {
        $query = "DELETE FROM " . $this->tabla . " WHERE id_veterinario = :id AND rol = 'veterinario'";
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
}
?>