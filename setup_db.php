<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'conexion.php';

try {
    // Limpiamos todo para empezar de cero
    $pdo->exec("DROP TABLE IF EXISTS pedido_detalles");
    $pdo->exec("DROP TABLE IF EXISTS pedidos");
    $pdo->exec("DROP TABLE IF EXISTS usuarios");
    $pdo->exec("DROP TABLE IF EXISTS productos");

    // 1. Tabla Usuarios
    $pdo->exec("CREATE TABLE usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        apellido VARCHAR(100) NOT NULL,
        cedula VARCHAR(20) UNIQUE NOT NULL,
        email VARCHAR(150) UNIQUE NOT NULL,
        telefono VARCHAR(20),
        password VARCHAR(255) NOT NULL,
        tipo VARCHAR(20) DEFAULT 'cliente',
        fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 2. Tabla Productos
    $pdo->exec("CREATE TABLE productos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(150) NOT NULL,
        descripcion TEXT,
        precio DECIMAL(10,2) NOT NULL,
        stock INT NOT NULL,
        imagen VARCHAR(255),
        categoria VARCHAR(100),
        activo INT DEFAULT 1
    )");

    // 3. Tabla Pedidos
    $pdo->exec("CREATE TABLE pedidos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT,
        total DECIMAL(10,2) NOT NULL,
        metodo_pago VARCHAR(50) NOT NULL,
        estado VARCHAR(50) DEFAULT 'pendiente',
        fecha_pedido DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(usuario_id) REFERENCES usuarios(id)
    )");

    // 4. Tabla Pedido Detalles
    $pdo->exec("CREATE TABLE pedido_detalles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pedido_id INT,
        producto_id INT,
        cantidad INT NOT NULL,
        precio_unitario DECIMAL(10,2) NOT NULL,
        FOREIGN KEY(pedido_id) REFERENCES pedidos(id),
        FOREIGN KEY(producto_id) REFERENCES productos(id)
    )");

    // Usuario Administrador
    $password_hash = password_hash('Francis28$', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, apellido, cedula, email, telefono, password, tipo) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute(['Admin', 'Principal', 'V-00000000', 'admin@gmail.com', '0000000000', $password_hash, 'admin']);

    // Productos
    $stmtProd = $pdo->prepare("INSERT INTO productos (nombre, descripcion, precio, stock, categoria, imagen) VALUES (?, ?, ?, ?, ?, ?)");
    $productos = [
        ['Alimento para perros adultos', 'Nutrición completa para perros de razas grandes.', 45.00, 20, 'Alimento', 'uploads/1777100628_alimento_perro.webp'],
        ['Alimento para cachorros', 'Fórmula especial para un crecimiento fuerte y sano.', 50.00, 15, 'Alimento', 'uploads/1777100616_alimento_cachorro.webp'],
        ['Alimento para gatos', 'Rico en taurina para la salud del corazón y visión.', 40.00, 25, 'Alimento', 'uploads/1777100601_alimento_gato.webp'],
        ['Alimento Premium', 'Calidad superior con ingredientes 100% naturales.', 60.00, 10, 'Alimento', 'uploads/1777100594_alimento_premium.webp'],
        ['Collar Ajustable Reflectivo', 'Seguridad para paseos nocturnos. Talla M.', 15.99, 45, 'Accesorios', 'uploads/1777100587_collar_perro.webp'],
        ['Correa Retráctil 5m', 'Mecanismo de freno. Hasta 25kg.', 24.99, 32, 'Accesorios', 'uploads/1777100582_correa_retractil.webp'],
        ['Juguete de Cuerda Nudos', 'Resistente para juegos de tira y afloja.', 9.99, 67, 'Accesorios', 'uploads/1777100574_juguete_cuerda.webp'],
        ['Comedero Elevado Ajustable', 'Mejora la postura al comer. Incluye 2 bowls.', 34.99, 14, 'Accesorios', 'uploads/1777100566_comedero_elevado_ejustable.webp'],
    ];
    foreach ($productos as $p) {
        $stmtProd->execute($p);
    }

    echo "✅ Base de datos creada con éxito. Tablas, admin y productos listos.";

} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>