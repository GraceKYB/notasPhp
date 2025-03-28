<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

//$app = AppFactory::create();

$app->post('/apiNotas/login', function (Request $request, Response $response) {
    $data = json_decode($request->getBody()->getContents(), true);
    
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';

    if (empty($username) || empty($password)) {
        $response->getBody()->write(json_encode(["error" => "Faltan credenciales"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    try {
        $db = new db();
        $conn = $db->connectDB();

        // Verificar si el usuario existe
        $sql = "SELECT id_usuario, username, password, id_perfil FROM usuarios WHERE username = :username";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            if ($user['id_perfil'] == 2) {
                $message = ["message" => "Bienvenido docente", "id_usuario" => $user["id_usuario"], "perfil" => "docente"];
            } elseif ($user['id_perfil'] == 1) {
                $message = ["message" => "Bienvenido estudiante", "id_usuario" => $user["id_usuario"], "perfil" => "estudiante"];
            } else {
                $message = ["error" => "Acceso denegado. Perfil no permitido."];
            }
        } else {
            $message = ["error" => "Credenciales incorrectas"];
        }

        $response->getBody()->write(json_encode($message));
        return $response->withHeader('Content-Type', 'application/json');

    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["error" => $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});
