<?php
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/config/db.php';

$app = AppFactory::create();

// Habilitar CORS (para pruebas en Postman)
$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});

// Incluir la ruta del login
require __DIR__ . '/../src/rutas/login.php';
require __DIR__ . '/../src/rutas/notas.php';
require __DIR__ . '/../src/rutas/agregarNotas.php';


// Ejecutar la aplicación
$app->run();
