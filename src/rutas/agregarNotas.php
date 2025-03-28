<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

//$app = AppFactory::create();

$app->post('/apiNotas/agregarNotas', function(Request $request, Response $response, array $args) {
    $data = json_decode($request->getBody()->getContents(), true);

    if (!isset($data['id_asignatura']) || !isset($data['estudiantes'])) {
        $response->getBody()->write(json_encode(["error" => "Faltan datos requeridos: id_asignatura y estudiantes con notas"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $id_asignatura = $data['id_asignatura'];
    $estudiantesArray = $data['estudiantes'];

    try {
        $db = new db();
        $conn = $db->connectDB();

        // Iniciar transacción
        $conn->beginTransaction();
        $insertQuery = "INSERT INTO notas (id_estudiante, id_asignatura, id_aporte, titulo, descripcion, nota) 
                        VALUES (:id_estudiante, :id_asignatura, :id_aporte, :titulo, :descripcion, :nota)";
        $stmt = $conn->prepare($insertQuery);

        foreach ($estudiantesArray as $estudiante) {
            if (!isset($estudiante['id_usuario']) || !isset($estudiante['aportes'])) {
                $conn->rollBack();
                $response->getBody()->write(json_encode(["error" => "Cada estudiante debe incluir id_usuario y aportes"]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }

            $id_usuario = $estudiante['id_usuario'];

            // 🔹 PRIMERO: OBTENEMOS EL id_estudiante DESDE estudiante_asignatura
            $sqlGetEstudiante = "SELECT e.id_estudiante
                                FROM estudiante_asignatura ea
                                INNER JOIN estudiantes e ON ea.id_estudiante = e.id_estudiante
                                WHERE ea.id_asignatura = :id_asignatura AND e.id_usuario = :id_usuario";
            $stmtEstudiante = $conn->prepare($sqlGetEstudiante);
            $stmtEstudiante->bindParam(':id_asignatura', $id_asignatura, PDO::PARAM_INT);
            $stmtEstudiante->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmtEstudiante->execute();
            $estudianteData = $stmtEstudiante->fetch(PDO::FETCH_ASSOC);

            // 🔹 DEPURACIÓN: Verificar si se encontró un id_estudiante
            if (!$estudianteData) {
                $conn->rollBack();
                error_log("Error: No se encontró un id_estudiante para id_usuario = $id_usuario en asignatura = $id_asignatura.");
                $response->getBody()->write(json_encode(["error" => "El usuario con ID $id_usuario no está inscrito en la asignatura con ID $id_asignatura."]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }

            $id_estudiante = $estudianteData['id_estudiante'];
            error_log("Depuración: Encontrado id_estudiante = $id_estudiante para id_usuario = $id_usuario en asignatura = $id_asignatura.");

            // 🔹 Insertar las notas SOLO para este estudiante
            foreach ($estudiante['aportes'] as $aporte) {
                if (!isset($aporte['id_aporte']) || !isset($aporte['notas'])) {
                    $conn->rollBack();
                    $response->getBody()->write(json_encode(["error" => "Cada aporte debe incluir id_aporte y notas"]));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
                }

                foreach ($aporte['notas'] as $notaItem) {
                    if (!isset($notaItem['nota'])) {
                        $conn->rollBack();
                        $response->getBody()->write(json_encode(["error" => "Cada registro de nota debe incluir el campo nota"]));
                        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
                    }

                    $stmt->execute([
                        ':id_estudiante' => $id_estudiante,
                        ':id_asignatura' => $id_asignatura,
                        ':id_aporte' => $aporte['id_aporte'],
                        ':titulo' => "", 
                        ':descripcion' => null,
                        ':nota' => $notaItem['nota']
                    ]);

                    error_log("Nota insertada: id_estudiante = $id_estudiante, id_asignatura = $id_asignatura, id_aporte = {$aporte['id_aporte']}, nota = {$notaItem['nota']}");
                }
            }
        }

        $conn->commit();
        $response->getBody()->write(json_encode(["message" => "Notas agregadas correctamente por estudiante"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);

    } catch (PDOException $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log("Error en la base de datos: " . $e->getMessage());
        $response->getBody()->write(json_encode(["error" => "Error en la base de datos: " . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

$app->get('/apiNotas/estudiante/asignatura/{id_asignatura}/notas', function(Request $request, Response $response, array $args) {
    $id_asignatura = $args['id_asignatura'];

    try {
        $db = new db();
        $conn = $db->connectDB();

        // 🔹 PRIMERO: OBTENER LOS ESTUDIANTES ASOCIADOS A LA ASIGNATURA
        $sqlGetEstudiantes = "SELECT ea.id_estudiante, e.id_usuario, u.username
                              FROM estudiante_asignatura ea
                              INNER JOIN estudiantes e ON ea.id_estudiante = e.id_estudiante
                              INNER JOIN usuarios u ON e.id_usuario = u.id_usuario
                              WHERE ea.id_asignatura = :id_asignatura";
        
        $stmtEstudiantes = $conn->prepare($sqlGetEstudiantes);
        $stmtEstudiantes->bindParam(':id_asignatura', $id_asignatura, PDO::PARAM_INT);
        $stmtEstudiantes->execute();
        $estudiantes = $stmtEstudiantes->fetchAll(PDO::FETCH_ASSOC);

        if (!$estudiantes) {
            $response->getBody()->write(json_encode(["error" => "No hay estudiantes inscritos en la asignatura con ID $id_asignatura."]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        // 🔹 SEGUNDO: OBTENER LAS NOTAS DE LOS ESTUDIANTES EN LA ASIGNATURA
        $notasPorEstudiante = [];
        foreach ($estudiantes as $estudiante) {
            $id_estudiante = $estudiante['id_estudiante'];

            $sqlNotas = "SELECT n.id_notas, n.id_aporte, n.nota
                         FROM notas n
                         WHERE n.id_estudiante = :id_estudiante AND n.id_asignatura = :id_asignatura";
            $stmtNotas = $conn->prepare($sqlNotas);
            $stmtNotas->bindParam(':id_estudiante', $id_estudiante, PDO::PARAM_INT);
            $stmtNotas->bindParam(':id_asignatura', $id_asignatura, PDO::PARAM_INT);
            $stmtNotas->execute();
            $notas = $stmtNotas->fetchAll(PDO::FETCH_ASSOC);

            $notasPorEstudiante[] = [
                "id_estudiante" => $id_estudiante,
                "id_usuario" => $estudiante['id_usuario'],
                "username" => $estudiante['username'],
                "notas" => $notas ?: []
            ];
        }

        $response->getBody()->write(json_encode($notasPorEstudiante));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["error" => "Error en la base de datos: " . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});


$app->put('/apiNotas/estudiante/{id_usuario}/asignatura/{id_asignatura}/notas', function(Request $request, Response $response, array $args) {
    $id_usuario = $args['id_usuario'];
    $id_asignatura = $args['id_asignatura'];
    $data = json_decode($request->getBody()->getContents(), true);

    if (!isset($data['notas']) || !is_array($data['notas'])) {
        $response->getBody()->write(json_encode(["error" => "Faltan datos requeridos: notas"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    try {
        $db = new db();
        $conn = $db->connectDB();

        // 🔹 OBTENER id_estudiante DESDE estudiantes USANDO id_usuario
        $sqlGetEstudiante = "SELECT e.id_estudiante 
                             FROM estudiantes e
                             INNER JOIN estudiante_asignatura ea ON e.id_estudiante = ea.id_estudiante
                             WHERE e.id_usuario = :id_usuario AND ea.id_asignatura = :id_asignatura";
        $stmtEstudiante = $conn->prepare($sqlGetEstudiante);
        $stmtEstudiante->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmtEstudiante->bindParam(':id_asignatura', $id_asignatura, PDO::PARAM_INT);
        $stmtEstudiante->execute();
        $estudiante = $stmtEstudiante->fetch(PDO::FETCH_ASSOC);

        if (!$estudiante) {
            $response->getBody()->write(json_encode(["error" => "El usuario con ID $id_usuario no está inscrito en la asignatura con ID $id_asignatura."]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $id_estudiante = $estudiante['id_estudiante'];

        // 🔹 ELIMINAR LAS NOTAS EXISTENTES DEL ESTUDIANTE EN ESTA ASIGNATURA
        $sqlDelete = "DELETE FROM notas WHERE id_estudiante = :id_estudiante AND id_asignatura = :id_asignatura";
        $stmtDelete = $conn->prepare($sqlDelete);
        $stmtDelete->bindParam(':id_estudiante', $id_estudiante, PDO::PARAM_INT);
        $stmtDelete->bindParam(':id_asignatura', $id_asignatura, PDO::PARAM_INT);
        $stmtDelete->execute();

        // 🔹 INSERTAR LAS NUEVAS NOTAS
        $sqlInsert = "INSERT INTO notas (id_estudiante, id_asignatura, id_aporte, nota) 
                      VALUES (:id_estudiante, :id_asignatura, :id_aporte, :nota)";
        $stmtInsert = $conn->prepare($sqlInsert);

        foreach ($data['notas'] as $nota) {
            if (!isset($nota['id_aporte']) || !isset($nota['nota'])) {
                $response->getBody()->write(json_encode(["error" => "Cada nota debe incluir id_aporte y el valor de nota"]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }

            $stmtInsert->execute([
                ':id_estudiante' => $id_estudiante,
                ':id_asignatura' => $id_asignatura,
                ':id_aporte' => $nota['id_aporte'],
                ':nota' => $nota['nota']
            ]);
        }

        // 🔹 DESPUÉS DE ACTUALIZAR LAS NOTAS, RECALCULAR EL RESUMEN BIMESTRAL AUTOMÁTICAMENTE
        $sqlPromedio = "SELECT id_aporte, AVG(nota) as promedio
                        FROM notas 
                        WHERE id_estudiante = :id_estudiante AND id_asignatura = :id_asignatura
                        GROUP BY id_aporte";

        $stmtPromedio = $conn->prepare($sqlPromedio);
        $stmtPromedio->bindParam(':id_estudiante', $id_estudiante, PDO::PARAM_INT);
        $stmtPromedio->bindParam(':id_asignatura', $id_asignatura, PDO::PARAM_INT);
        $stmtPromedio->execute();
        $promedios = $stmtPromedio->fetchAll(PDO::FETCH_ASSOC);

        // 🔹 Inicializar valores
        $AD = $AP = $AA = $EX = 0;

        foreach ($promedios as $promedio) {
            switch ($promedio['id_aporte']) {
                case 1: $AD = round($promedio['promedio'], 2); break;
                case 2: $AP = round($promedio['promedio'], 2); break;
                case 3: $AA = round($promedio['promedio'], 2); break;
                case 4: $EX = round($promedio['promedio'], 2); break;
            }
        }

        // 🔹 Cálculo del promedio final con ponderación
        $promedioFinal = round(($AD * 0.25) + ($AP * 0.20) + ($AA * 0.20) + ($EX * 0.35), 2);

        // 🔹 GUARDAR EL NUEVO RESUMEN BIMESTRAL
        $sqlInsertBimestre = "INSERT INTO bimestres (id_estudiante, id_asignatura, bimestre, 
                               promedio_docencia, promedio_practico, promedio_autonomo, promedio_examen, promedio_final)
                              VALUES (:id_estudiante, :id_asignatura, 1, :AD, :AP, :AA, :EX, :Promedio)";

        $stmtInsertBimestre = $conn->prepare($sqlInsertBimestre);
        $stmtInsertBimestre->execute([
            ':id_estudiante' => $id_estudiante,
            ':id_asignatura' => $id_asignatura,
            ':AD' => $AD,
            ':AP' => $AP,
            ':AA' => $AA,
            ':EX' => $EX,
            ':Promedio' => $promedioFinal
        ]);

        $response->getBody()->write(json_encode(["message" => "Notas actualizadas y resumen bimestral recalculado correctamente"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["error" => "Error en la base de datos: " . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

$app->get('/apiNotas/asignatura/{id_asignatura}/bimestres', function(Request $request, Response $response, array $args) {
    $id_asignatura = $args['id_asignatura'];

    try {
        $db = new db();
        $conn = $db->connectDB();

        // 🔹 OBTENER EL ÚLTIMO REGISTRO DE CADA ESTUDIANTE BASADO EN id_bimestre
        $sqlBimestres = "SELECT b.id_estudiante, u.username, 
                                b.bimestre, 
                                b.promedio_docencia AS AD, 
                                b.promedio_practico AS AP, 
                                b.promedio_autonomo AS AA, 
                                b.promedio_examen AS EX, 
                                b.promedio_final AS Promedio
                         FROM bimestres b
                         INNER JOIN estudiantes e ON b.id_estudiante = e.id_estudiante
                         INNER JOIN usuarios u ON e.id_usuario = u.id_usuario
                         WHERE b.id_asignatura = :id_asignatura
                         AND b.id_bimestre = (SELECT MAX(b2.id_bimestre) 
                                              FROM bimestres b2 
                                              WHERE b2.id_estudiante = b.id_estudiante 
                                              AND b2.id_asignatura = b.id_asignatura)
                         ORDER BY b.id_bimestre DESC";

        $stmtBimestres = $conn->prepare($sqlBimestres);
        $stmtBimestres->bindParam(':id_asignatura', $id_asignatura, PDO::PARAM_INT);
        $stmtBimestres->execute();
        $bimestres = $stmtBimestres->fetchAll(PDO::FETCH_ASSOC);

        if (!$bimestres) {
            $response->getBody()->write(json_encode(["error" => "No hay registros de bimestres para la asignatura con ID $id_asignatura."]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $response->getBody()->write(json_encode($bimestres));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["error" => "Error en la base de datos: " . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

$app->get('/apiNotas/asignatura/{id_asignatura}/notas', function(Request $request, Response $response, array $args) {
    $id_asignatura = $args['id_asignatura'];

    try {
        $db = new db();
        $conn = $db->connectDB();

        $sqlNotas = "SELECT n.id_estudiante, u.username, n.id_aporte, n.nota
                     FROM notas n
                     INNER JOIN estudiantes e ON n.id_estudiante = e.id_estudiante
                     INNER JOIN usuarios u ON e.id_usuario = u.id_usuario
                     WHERE n.id_asignatura = :id_asignatura";

        $stmtNotas = $conn->prepare($sqlNotas);
        $stmtNotas->bindParam(':id_asignatura', $id_asignatura, PDO::PARAM_INT);
        $stmtNotas->execute();
        $notas = $stmtNotas->fetchAll(PDO::FETCH_ASSOC);

        if (!$notas) {
            $response->getBody()->write(json_encode(["error" => "No hay notas registradas para la asignatura con ID $id_asignatura."]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $response->getBody()->write(json_encode($notas));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["error" => "Error en la base de datos: " . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

$app->post('/apiNotas/asignaturasEstudiante', function (Request $request, Response $response) {
    $data = json_decode($request->getBody()->getContents(), true);
    $id_usuario = $data['id_usuario'] ?? null;

    if (!$id_usuario) {
        $response->getBody()->write(json_encode(["error" => "ID de usuario requerido"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    try {
        $db = new db();
        $conn = $db->connectDB();

        // Obtener todos los ID de estudiante asociados al usuario
        $sql_estudiante = "SELECT id_estudiante FROM estudiantes WHERE id_usuario = :id_usuario";
        $stmt_estudiante = $conn->prepare($sql_estudiante);
        $stmt_estudiante->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt_estudiante->execute();
        $estudiante_ids = $stmt_estudiante->fetchAll(PDO::FETCH_COLUMN);

        if (!$estudiante_ids) {
            $response->getBody()->write(json_encode(["error" => "No se encontraron asignaturas para este estudiante."]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        // Convertir los IDs en una lista para la consulta SQL
        $placeholders = implode(',', array_fill(0, count($estudiante_ids), '?'));

        // Consulta para obtener asignaturas con código QR
        $sql = "SELECT 
                    a.nombre AS asignatura, 
                    c.nombre AS carrera, 
                    n.nombre AS nivel, 
                    j.nombre AS jornada, 
                    p.nombre AS paralelo,
                    q.codigo_qr
                FROM estudiante_asignatura ea
                INNER JOIN asignaturas a ON ea.id_asignatura = a.id_asignatura
                INNER JOIN carreras c ON a.id_carrera = c.id_carrera
                INNER JOIN niveles n ON a.id_nivel = n.id_nivel
                INNER JOIN estudiantes e ON ea.id_estudiante = e.id_estudiante
                INNER JOIN jornadas j ON e.id_jornada = j.id_jornada
                INNER JOIN paralelo p ON e.id_paralelo = p.id_paralelo
                LEFT JOIN qr_estudiantes q ON e.id_estudiante = q.id_estudiante
                WHERE ea.id_estudiante IN ($placeholders)";

        $stmt = $conn->prepare($sql);
        $stmt->execute($estudiante_ids);
        $asignaturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode($asignaturas));
        return $response->withHeader('Content-Type', 'application/json');

    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["error" => $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

$app->post('/apiNotas/registrarAsistencia', function (Request $request, Response $response) {
    $data = json_decode($request->getBody()->getContents(), true);

    $id_usuario = $data['id_estudiante'] ?? null;
    $codigo_qr = $data['codigo_qr'] ?? null;
    $latitud = $data['latitud'] ?? null;
    $longitud = $data['longitud'] ?? null;
    $device_id = $data['device_id'] ?? null;

    if (!$id_usuario || !$codigo_qr || !$device_id) {
        $response->getBody()->write(json_encode(["error" => "Datos insuficientes"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    try {
        $db = new db();
        $conn = $db->connectDB();

        // 🔹 Obtener el ID del estudiante
        $sql = "SELECT id_estudiante FROM estudiantes WHERE id_usuario = :id_usuario";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();
        $estudiante_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!$estudiante_ids) {
            $response->getBody()->write(json_encode(["error" => "No se encontraron registros de estudiante."]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        // 🔹 Obtener `id_estudiante` y `id_docente_asignatura`
        $placeholders = implode(',', array_fill(0, count($estudiante_ids), '?'));
        $sql = "SELECT id_estudiante, id_docente_asignatura FROM qr_estudiantes 
                WHERE codigo_qr = ? AND id_estudiante IN ($placeholders)";
        $stmt = $conn->prepare($sql);
        $stmt->execute(array_merge([$codigo_qr], $estudiante_ids));
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            $response->getBody()->write(json_encode(["error" => "Código QR no válido o no pertenece al estudiante"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $id_estudiante_real = $result['id_estudiante'];
        $id_docente_asignatura = $result['id_docente_asignatura'];

        // 🔹 Obtener `id_asignatura`
        $sql = "SELECT id_asignatura FROM docente_asignatura WHERE id_docente_asignatura = :id_docente_asignatura";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id_docente_asignatura', $id_docente_asignatura, PDO::PARAM_INT);
        $stmt->execute();
        $asignatura = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$asignatura) {
            $response->getBody()->write(json_encode(["error" => "No se encontró la asignatura"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $id_asignatura = $asignatura['id_asignatura'];

        // 🔹 Validación 1️⃣: Verificar si ya registró asistencia hoy
        $sql = "SELECT COUNT(*) as total FROM asistencia 
                WHERE id_estudiante = :id_estudiante 
                AND id_asignatura = :id_asignatura 
                AND DATE(fecha) = CURDATE()";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id_estudiante', $id_estudiante_real, PDO::PARAM_INT);
        $stmt->bindParam(':id_asignatura', $id_asignatura, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row['total'] > 0) {
            $response->getBody()->write(json_encode(["error" => "Este QR ya fue escaneado hoy"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // 🔹 Validación 2️⃣: Verificar si este `device_id` ya ha sido usado en esta materia por otro estudiante
        $sql = "SELECT id_estudiante FROM asistencia WHERE device_id = :device_id AND id_asignatura = :id_asignatura";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':device_id', $device_id, PDO::PARAM_STR);
        $stmt->bindParam(':id_asignatura', $id_asignatura, PDO::PARAM_INT);
        $stmt->execute();
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing && $existing['id_estudiante'] != $id_estudiante_real) {
            $response->getBody()->write(json_encode(["error" => "Este dispositivo ya registró asistencia para otro estudiante"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // 🔹 Registrar asistencia con `device_id`
        $sql = "INSERT INTO asistencia (id_estudiante, id_asignatura, fecha, latitud, longitud, estado, device_id) 
                VALUES (:id_estudiante, :id_asignatura, NOW(), :latitud, :longitud, 'Asistió', :device_id)";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id_estudiante', $id_estudiante_real, PDO::PARAM_INT);
        $stmt->bindParam(':id_asignatura', $id_asignatura, PDO::PARAM_INT);
        $stmt->bindParam(':latitud', $latitud, PDO::PARAM_STR);
        $stmt->bindParam(':longitud', $longitud, PDO::PARAM_STR);
        $stmt->bindParam(':device_id', $device_id, PDO::PARAM_STR);
        $stmt->execute();

        $response->getBody()->write(json_encode(["message" => "Asistencia registrada con éxito"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["error" => $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

