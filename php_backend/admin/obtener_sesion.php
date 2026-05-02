<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { exit; }
require_once '../conexion.php';

if (isset($_SESSION['usuario_id'])) {
    $stmt = $conn->prepare("SELECT id, nombre, apellido, cedula, email, telefono, tipo FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($usuario) { echo json_encode(['success' => true, 'usuario' => $usuario]); } 
    else { echo json_encode(['success' => false]); }
} else { echo json_encode(['success' => false]); }
?>