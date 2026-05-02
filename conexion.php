<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Datos de Railway (los reemplazas con tus valores del PASO 2)
$host     = "switchyard.proxy.rlwy.net";
$port     = "24146";
$dbname   = "railway";
$username = "root";
$password = "odaWlwLmgJPVrmuaCidtoZaNBHvbljVT";

function limpiarDato($dato) {
    return htmlspecialchars(strip_tags(trim($dato)));
}

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    die(json_encode([
        "success" => false,
        "error"   => "Error de conexión: " . $e->getMessage()
    ]));
}
?>
