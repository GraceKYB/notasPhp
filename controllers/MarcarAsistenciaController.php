<?php
session_start();
require_once '../config/database.php';
require_once '../models/AsistenciaModel.php';

// Verificar que el usuario es docente
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_perfil'] != 2) {
    header("Location: ../views/login.php");
    exit();
}

// Obtener datos del formulario
$id_estudiante = $_POST['id_estudiante'] ?? null;
$id_asignatura = $_POST['id_asignatura'] ?? null;

if (!$id_estudiante || !$id_asignatura) {
    $_SESSION['mensaje'] = "Error: Datos insuficientes.";
    header("Location: ../views/asistencia.php");
    exit();
}

// Instancia del modelo
$asistenciaModel = new AsistenciaModel($conexion);
$resultado = $asistenciaModel->marcarAsistenciaManual($id_estudiante, $id_asignatura);

// Manejo de mensajes
if ($resultado == "ok") {
    $_SESSION['mensaje'] = "Asistencia marcada exitosamente.";
} elseif ($resultado == "error_ya_asistio") {
    $_SESSION['mensaje'] = "El estudiante ya tiene asistencia registrada.";
} else {
    $_SESSION['mensaje'] = "Error al registrar asistencia.";
}

header("Location: ../views/asistencia.php");
exit();
