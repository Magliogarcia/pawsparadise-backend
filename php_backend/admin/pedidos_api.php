<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { ob_end_clean(); exit(0); }
require_once '../conexion.php';

$accion = $_GET['accion'] ?? '';
$rol_frontend = $_POST['rol_usuario'] ?? $_GET['rol_usuario'] ?? '';

try {
    // 1. CREAR PEDIDO Y RESTAR STOCK 
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'crear') {
        $usuario_id = intval($_POST['usuario_id'] ?? 0);
        $total = floatval($_POST['total'] ?? 0);
        $metodo = $_POST['metodo_pago'] ?? '';
        $productos = json_decode($_POST['productos'], true);

        $stmt = $pdo->prepare("INSERT INTO pedidos (usuario_id, total, metodo_pago, estado) VALUES (?, ?, ?, 'Pendiente')");
        $stmt->execute([$usuario_id, $total, $metodo]);
        $pedido_id = $pdo->lastInsertId();

        $stmtDetalle = $pdo->prepare("INSERT INTO pedido_detalles (pedido_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
        $stmtStock = $pdo->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");

        foreach ($productos as $p) {
            $prod_id = isset($p['id']) ? intval($p['id']) : 0;
            $stmtDetalle->execute([$pedido_id, $prod_id, $p['quantity'], $p['price']]);
            if ($prod_id > 0) {
                $stmtStock->execute([$p['quantity'], $prod_id]);
            }
        }
        ob_end_clean(); echo json_encode(['success' => true, 'pedido_id' => $pedido_id]); exit;
    }

    // 2. LISTAR TODOS LOS PEDIDOS (Solo Administrador)
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && $accion === 'listar_admin') {
        if ($rol_frontend !== 'admin') { ob_end_clean(); echo json_encode(['success' => false, 'error' => 'Acceso denegado']); exit; }
        
        $stmt = $pdo->query("SELECT p.*, u.nombre, u.apellido, u.cedula FROM pedidos p JOIN usuarios u ON p.usuario_id = u.id ORDER BY p.id DESC");
        $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ob_end_clean(); echo json_encode(['success' => true, 'pedidos' => $pedidos]); exit;
    }

    // 3. CAMBIAR ESTADO DEL PEDIDO (Solo Administrador)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'actualizar_estado') {
        if ($rol_frontend !== 'admin') { ob_end_clean(); echo json_encode(['success' => false]); exit; }
        
        $id = intval($_POST['id']); $estado = $_POST['estado'];
        $stmt = $pdo->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
        $stmt->execute([$estado, $id]);
        
        ob_end_clean(); echo json_encode(['success' => true]); exit;
    }

    // 4. LISTAR PEDIDOS DE UN CLIENTE (NUEVO)
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && $accion === 'listar_cliente') {
        $usuario_id = intval($_GET['usuario_id'] ?? 0);
        if ($usuario_id <= 0) { ob_end_clean(); echo json_encode(['success' => false, 'error' => 'ID inválido']); exit; }

        $stmt = $pdo->prepare("SELECT * FROM pedidos WHERE usuario_id = ? ORDER BY id DESC");
        $stmt->execute([$usuario_id]);
        $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Buscar qué productos tiene cada pedido para mostrarlos
        foreach ($pedidos as &$pedido) {
            $stmtDet = $pdo->prepare("SELECT pd.cantidad, pd.precio_unitario, pr.nombre, pr.imagen FROM pedido_detalles pd JOIN productos pr ON pd.producto_id = pr.id WHERE pd.pedido_id = ?");
            $stmtDet->execute([$pedido['id']]);
            $pedido['detalles'] = $stmtDet->fetchAll(PDO::FETCH_ASSOC);
        }

        ob_end_clean(); echo json_encode(['success' => true, 'pedidos' => $pedidos]); exit;
    }

} catch (Exception $e) { ob_end_clean(); echo json_encode(['success' => false, 'error' => $e->getMessage()]); }
?>