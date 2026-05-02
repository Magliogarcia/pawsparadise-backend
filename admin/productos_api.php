<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { ob_end_clean(); exit(0); }
require_once '../conexion.php';

$rol_frontend = $_POST['rol_usuario'] ?? $_GET['rol_usuario'] ?? '';
$esAdmin = ($rol_frontend === 'admin' || (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin'));

$accion = $_GET['accion'] ?? '';

try {
    // 1. LEER TODOS LOS PRODUCTOS
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && $accion === 'listar') {
        $stmt = $pdo->query("SELECT * FROM productos ORDER BY id DESC");
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ob_end_clean();
        echo json_encode(['success' => true, 'productos' => $productos]);
        exit;
    }

    if (!$esAdmin) {
        ob_end_clean();
        echo json_encode(['success' => false, 'error' => 'Acceso denegado. Se requiere rol de administrador.']);
        exit;
    }

    // 2. CREAR O EDITAR PRODUCTO (CON IMAGEN)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'guardar') {
        $id = $_POST['id'] ?? '';
        $nombre = limpiarDato($_POST['nombre'] ?? '');
        $descripcion = limpiarDato($_POST['descripcion'] ?? '');
        $categoria = limpiarDato($_POST['categoria'] ?? '');
        $precio = floatval($_POST['precio'] ?? 0);
        $stock = intval($_POST['stock'] ?? 0);
        
        $imagen = $_POST['imagen_actual'] ?? ''; 

        // === MANEJO MEJORADO DE IMÁGENES ===
        if (isset($_FILES['imagen_archivo'])) {
            $error_codigo = $_FILES['imagen_archivo']['error'];
            
            if ($error_codigo === UPLOAD_ERR_OK) {
                // Navegamos hacia atrás desde php_backend/admin/ hasta PawsParadise/static/uploads/
                $directorio_subida = realpath(__DIR__ . '/../../static') . '/uploads/';
                
                // Si realpath falla (ej. static no existe), intentamos ruta absoluta básica
                if (!$directorio_subida || !is_dir(realpath(__DIR__ . '/../../static'))) {
                     $directorio_subida = __DIR__ . '/../../static/uploads/';
                }

                if (!is_dir($directorio_subida)) {
                    if (!mkdir($directorio_subida, 0777, true)) {
                        ob_end_clean();
                        echo json_encode(['success' => false, 'error' => 'No se pudo crear la carpeta uploads. Revisa permisos de Windows.']);
                        exit;
                    }
                }
                
                $nombre_archivo = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", basename($_FILES['imagen_archivo']['name']));
                $ruta_destino = $directorio_subida . $nombre_archivo;
                
                if (move_uploaded_file($_FILES['imagen_archivo']['tmp_name'], $ruta_destino)) {
                    $imagen = 'uploads/' . $nombre_archivo; 
                } else {
                    ob_end_clean();
                    echo json_encode(['success' => false, 'error' => 'No se pudo mover el archivo. Carpeta destino: ' . $ruta_destino]);
                    exit;
                }
            } elseif ($error_codigo !== UPLOAD_ERR_NO_FILE) {
                // Hubo un error al subir (ej. archivo muy pesado)
                ob_end_clean();
                echo json_encode(['success' => false, 'error' => 'Error de subida PHP Código: ' . $error_codigo]);
                exit;
            }
        }

        if (empty($nombre) || $precio <= 0) {
            ob_end_clean();
            echo json_encode(['success' => false, 'error' => 'Nombre y precio son obligatorios']);
            exit;
        }

        if (empty($id)) {
            $stmt = $pdo->prepare("INSERT INTO productos (nombre, descripcion, precio, stock, categoria, imagen) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nombre, $descripcion, $precio, $stock, $categoria, $imagen]);
            $mensaje = "Producto añadido correctamente";
        } else {
            $stmt = $pdo->prepare("UPDATE productos SET nombre=?, descripcion=?, precio=?, stock=?, categoria=?, imagen=? WHERE id=?");
            $stmt->execute([$nombre, $descripcion, $precio, $stock, $categoria, $imagen, $id]);
            $mensaje = "Producto actualizado";
        }

        ob_end_clean();
        echo json_encode(['success' => true, 'message' => $mensaje]);
        exit;
    }

    // 3. ELIMINAR PRODUCTO
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'eliminar') {
        $id = $_POST['id'] ?? '';
        $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
        $stmt->execute([$id]);
        ob_end_clean();
        echo json_encode(['success' => true, 'message' => 'Producto eliminado']);
        exit;
    }

} catch (PDOException $e) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Error de BD: ' . $e->getMessage()]);
}
?>