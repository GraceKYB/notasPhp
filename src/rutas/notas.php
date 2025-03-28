<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

//$app = AppFactory::create();

// Obtener datos del docente por ID de usuario
$app->get('/apiNotas/docente/{id_usuario}', function (Request $request, Response $response, array $args) {
    $id_usuario = $args['id_usuario'];

    try {
        $db = new db();
        $conn = $db->connectDB();

        // Obtener datos del docente
        $stmt = $conn->prepare("SELECT d.nombre, d.apellido, d.cedula, d.telefono, d.email 
                                FROM detalles_usuarios d
                                INNER JOIN usuarios u ON d.id_usuario = u.id_usuario
                                WHERE d.id_usuario = :id_usuario AND u.id_perfil = 2");
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->execute();
        $docente = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$docente) {
            $response->getBody()->write(json_encode(["error" => "Docente no encontrado"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $response->getBody()->write(json_encode($docente));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["error" => "Error en la base de datos: " . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// Obtener asignaturas del docente
// Obtener asignaturas del docente incluyendo id_asignatura
$app->get('/apiNotas/docente/asignaturas/{id_usuario}', function (Request $request, Response $response, array $args) {
    $id_usuario = $args['id_usuario'];

    try {
        $db = new db();
        $conn = $db->connectDB();

        $sql = "SELECT da.id_asignatura, c.nombre AS carrera, a.nombre AS asignatura, n.nombre AS nivel, j.nombre AS jornada, p.nombre AS paralelo
                FROM docente_asignatura da
                INNER JOIN asignaturas a ON da.id_asignatura = a.id_asignatura
                INNER JOIN carrera_niveles cn ON a.id_carrera = cn.id_carrera AND a.id_nivel = cn.id_nivel
                INNER JOIN carreras c ON cn.id_carrera = c.id_carrera
                INNER JOIN niveles n ON cn.id_nivel = n.id_nivel
                INNER JOIN jornadas j ON da.id_jornada = j.id_jornada
                INNER JOIN paralelo p ON da.id_paralelo = p.id_paralelo
                WHERE da.id_usuario = :id_usuario";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->execute();
        $asignaturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$asignaturas) {
            $response->getBody()->write(json_encode(["error" => "No se encontraron asignaturas para este docente"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $response->getBody()->write(json_encode($asignaturas));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["error" => "Error en la base de datos: " . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});


$app->get('/apiNotas/docente/{id_usuario}/asignatura/{id_asignatura}/estudiantes', function (Request $request, Response $response, array $args) {
    $id_usuario = $args['id_usuario'];
    $id_asignatura = $args['id_asignatura'];

    try {
        $db = new db();
        $conn = $db->connectDB();

        $sql = "SELECT DISTINCT u.id_usuario, u.username, e.id_estudiante, da.id_asignatura, 
                        c.nombre AS carrera, n.nombre AS nivel, j.nombre AS jornada, 
                        p.nombre AS paralelo, a.nombre AS asignatura
                FROM docente_asignatura da
                INNER JOIN estudiante_asignatura ea ON da.id_asignatura = ea.id_asignatura
                INNER JOIN estudiantes e ON ea.id_estudiante = e.id_estudiante
                INNER JOIN usuarios u ON e.id_usuario = u.id_usuario
                INNER JOIN carrera_niveles cn ON e.id_carrera = cn.id_carrera AND e.id_nivel = cn.id_nivel
                INNER JOIN carreras c ON cn.id_carrera = c.id_carrera
                INNER JOIN niveles n ON cn.id_nivel = n.id_nivel
                INNER JOIN jornadas j ON e.id_jornada = j.id_jornada
                INNER JOIN paralelo p ON e.id_paralelo = p.id_paralelo
                INNER JOIN asignaturas a ON da.id_asignatura = a.id_asignatura
                WHERE da.id_usuario = :id_usuario AND da.id_asignatura = :id_asignatura
                GROUP BY e.id_estudiante, da.id_asignatura";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->bindParam(':id_asignatura', $id_asignatura, PDO::PARAM_INT);
        $stmt->execute();
        $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$estudiantes) {
            $response->getBody()->write(json_encode(["error" => "No se encontraron estudiantes en esta asignatura"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $response->getBody()->write(json_encode($estudiantes));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["error" => "Error en la base de datos: " . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});


