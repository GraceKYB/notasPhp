<?php
$host = "localhost";
$nombre_db = "notas"; 
$usuario = "root";
$contrasena = "";

try {
    $conexion = new PDO("mysql:host=$host;dbname=$nombre_db", $usuario, $contrasena);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $exc) {
    echo "Error al conectar a la base de datos: " . $exc->getMessage();
    exit;
}
?>
