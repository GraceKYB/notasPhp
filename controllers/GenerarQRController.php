<?php
session_start();
require_once '../config/database.php';
require_once '../models/AsistenciaModel.php';

// Verificar que el usuario es docente
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_perfil'] != 2) {
    header("Location: ../views/login.php");
    exit();
}

// Obtener parámetros desde el formulario (POST)
$id_docente = $_SESSION['id_usuario'];
$id_asignatura = $_POST['id_asignatura'] ?? null;
$id_carrera = $_POST['id_carrera'] ?? null;
$id_nivel = $_POST['id_nivel'] ?? null;
$id_jornada = $_POST['id_jornada'] ?? null;
$id_paralelo = $_POST['id_paralelo'] ?? null;

if (!$id_asignatura || !$id_carrera || !$id_nivel || !$id_jornada || !$id_paralelo) {
    $_SESSION['mensaje'] = "Error: Falta información de la asignatura.";
    header("Location: ../views/aportes.php");
    exit();
}

// Instancia del modelo Asistencia
$asistenciaModel = new AsistenciaModel($conexion);

// Generar QR para cada estudiante
$resultado = $asistenciaModel->generarQRCodigos($id_docente, $id_asignatura);

$_SESSION['mensaje'] = $resultado ? "Códigos QR generados correctamente." : "Error al generar los códigos QR.";

header("Location: ../views/asistencia.php?id_asignatura=$id_asignatura&id_carrera=$id_carrera&id_nivel=$id_nivel&id_jornada=$id_jornada&id_paralelo=$id_paralelo");
exit();
?>