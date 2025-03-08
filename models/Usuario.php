<?php
class Usuario {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }
    // Método para obtener todos los usuarios con sus detalles
    public function obtenerUsuariosDetalles()
    {
        try {
            // Modificar la consulta para seleccionar el username
            $sql = "SELECT d.*, u.username 
                                FROM detalles_usuarios d
                                JOIN usuarios u ON d.id_usuario = u.id_usuario
                                WHERE d.estado = 'A'"; 
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    
    public function obtenerUsuario($id) {
        $sql = "SELECT * FROM usuarios WHERE id_usuario = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function crearUsuario($username, $password, $id_perfil, $nombre, $apellido, $cedula, $telefono, $email)
    {
        try {
            $this->conexion->beginTransaction(); // Iniciar transacción
            // Insertar en tabla usuarios
            $sqlUsuario = "INSERT INTO usuarios (username, password, id_perfil, estado) VALUES (:username, :password, :id_perfil, 'A')";
            $stmtUsuario = $this->conexion->prepare($sqlUsuario);
            $stmtUsuario->execute([
                ':username' => $username,
                ':password' => password_hash($password, PASSWORD_BCRYPT),
                ':id_perfil' => $id_perfil,
            ]);

            // Obtener el último ID insertado
            $id_usuario = $this->conexion->lastInsertId();

            // Insertar en tabla detalles_usuarios
            $sqlDetalle = "INSERT INTO detalles_usuarios (id_usuario, nombre, apellido, cedula, telefono, email, estado) VALUES (:id_usuario, :nombre, :apellido, :cedula, :telefono, :email, 'A')";
            $stmtDetalle = $this->conexion->prepare($sqlDetalle);
            $stmtDetalle->execute([
                ':id_usuario' => $id_usuario,
                ':nombre' => $nombre,
                ':apellido' => $apellido,
                ':cedula' => $cedula,
                ':telefono' => $telefono,
                ':email' => $email,
            ]);

            $this->conexion->commit(); // Confirmar transacción
            return true;
        } catch (PDOException $e) {
            $this->conexion->rollBack(); // Revertir si hay error
            echo "Error SQL: " . $e->getMessage();
            return false;
        }
    }
    
    public function obtenerUsuarioPorId($id_usuario) {
        try {
            $sql = "SELECT u.id_usuario, u.username, u.id_perfil, d.nombre, d.apellido, d.cedula, d.telefono, d.email 
                    FROM usuarios u
                    JOIN detalles_usuarios d ON u.id_usuario = d.id_usuario
                    WHERE u.id_usuario = :id_usuario";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id_usuario' => $id_usuario]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }
    
    public function actualizarUsuario($id_usuario, $username, $id_perfil, $nombre, $apellido, $cedula, $telefono, $email) {
        try {
            $this->conexion->beginTransaction();
    
            // Actualizar tabla usuarios
            $sqlUsuario = "UPDATE usuarios SET username = :username, id_perfil = :id_perfil WHERE id_usuario = :id_usuario";
            $stmtUsuario = $this->conexion->prepare($sqlUsuario);
            $stmtUsuario->execute([
                ':id_usuario' => $id_usuario,
                ':username' => $username,
                ':id_perfil' => $id_perfil
            ]);
    
            // Actualizar tabla detalles_usuarios
            $sqlDetalle = "UPDATE detalles_usuarios SET nombre = :nombre, apellido = :apellido, cedula = :cedula, 
                            telefono = :telefono, email = :email WHERE id_usuario = :id_usuario";
            $stmtDetalle = $this->conexion->prepare($sqlDetalle);
            $stmtDetalle->execute([
                ':id_usuario' => $id_usuario,
                ':nombre' => $nombre,
                ':apellido' => $apellido,
                ':cedula' => $cedula,
                ':telefono' => $telefono,
                ':email' => $email
            ]);
    
            $this->conexion->commit();
            return true;
        } catch (PDOException $e) {
            $this->conexion->rollBack();
            return false;
        }
    }
    

    public function eliminarUsuario($id_usuario)
    {
        try {
            $sql = "UPDATE usuarios SET estado = 'I' WHERE id_usuario = :id_usuario";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id_usuario' => $id_usuario]);

            $sqlDetalle = "UPDATE detalles_usuarios SET estado = 'I' WHERE id_usuario = :id_usuario";
            $stmtDetalle = $this->conexion->prepare($sqlDetalle);
            $stmtDetalle->execute([':id_usuario' => $id_usuario]);

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Obtener todos los usuarios
    public function obtenerUsuarios() {
        $query = "SELECT * FROM usuarios WHERE estado = 'A'";
        $stmt = $this->conexion->prepare($query);   
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
