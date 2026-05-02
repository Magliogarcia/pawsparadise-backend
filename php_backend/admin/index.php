<?php
session_start();

// 1. Apuntamos a la nueva conexión de SQLite
require_once '../conexion.php';

// 2. Verificamos la seguridad: que tenga sesión y sea administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    // Si intenta entrar alguien que no es admin, lo devolvemos al login de Flask
    header("Location: http://127.0.0.1:5000/login.html");
    exit;
}
?>

// Obtener estadísticas
$stats = [];

// Total productos activos
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM productos WHERE activo = 1");
$stats['productos'] = $result ? mysqli_fetch_assoc($result)['total'] : 0;

// Total clientes
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM usuarios WHERE tipo = 'cliente'");
$stats['clientes'] = $result ? mysqli_fetch_assoc($result)['total'] : 0;

// Total ventas e ingresos
$result = mysqli_query($conn, "SELECT COUNT(*) as total, COALESCE(SUM(total), 0) as ingresos FROM pedidos");
$ventas = $result ? mysqli_fetch_assoc($result) : ['total' => 0, 'ingresos' => 0];
$stats['ventas'] = $ventas['total'];
$stats['ingresos'] = $ventas['ingresos'];

// Ventas recientes (últimas 5)
$ventas_recientes = mysqli_query($conn, "
    SELECT p.*, u.nombre as cliente_nombre 
    FROM pedidos p 
    JOIN usuarios u ON p.usuario_id = u.id 
    ORDER BY p.fecha_pedido DESC 
    LIMIT 5
");

// Productos con stock bajo (menos de 10)
$stock_bajo = mysqli_query($conn, "
    SELECT * FROM productos 
    WHERE stock < 10 AND activo = 1 
    ORDER BY stock ASC 
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard · Paws Paradise Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ url_for('static', filename='css/estilos.css') }}">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-paw"></i>
                <h2>Paws Paradise</h2>
                <p>Admin Panel</p>
            </div>
            
            <nav class="sidebar-nav">
                <a href="index.php" class="active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="productos/index.php">
                    <i class="fas fa-box"></i>
                    <span>Productos</span>
                </a>
                <a href="clientes/index.php">
                    <i class="fas fa-users"></i>
                    <span>Clientes</span>
                </a>
                <a href="ventas/index.php">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Ventas</span>
                </a>
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Cerrar Sesión</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="top-header">
                <h1>Dashboard</h1>
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo $_SESSION['usuario_nombre']; ?></span>
                </div>
            </header>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['productos']; ?></h3>
                        <p>Productos</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['clientes']; ?></h3>
                        <p>Clientes</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['ventas']; ?></h3>
                        <p>Ventas</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-info">
                        <h3>$<?php echo number_format($stats['ingresos'], 2); ?></h3>
                        <p>Ingresos</p>
                    </div>
                </div>
            </div>

            <!-- Ventas Recientes -->
            <div class="section-card">
                <div class="section-header">
                    <h2><i class="fas fa-history"></i> Ventas Recientes</h2>
                    <a href="ventas/index.php" class="btn-ver-todas">Ver todas</a>
                </div>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th># Pedido</th>
                            <th>Cliente</th>
                            <th>Fecha</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($ventas_recientes && mysqli_num_rows($ventas_recientes) > 0): ?>
                            <?php while($venta = mysqli_fetch_assoc($ventas_recientes)): ?>
                            <tr>
                                <td>#<?php echo str_pad($venta['id'], 5, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo $venta['cliente_nombre']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($venta['fecha_pedido'])); ?></td>
                                <td>$<?php echo number_format($venta['total'], 2); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $venta['estado']; ?>">
                                        <?php echo $venta['estado']; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="ventas/detalle.php?id=<?php echo $venta['id']; ?>" class="btn-ver">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center;">No hay ventas registradas</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Stock Bajo -->
            <div class="section-card">
                <div class="section-header">
                    <h2><i class="fas fa-exclamation-triangle"></i> Productos con Stock Bajo</h2>
                    <a href="productos/index.php" class="btn-ver-todas">Gestionar</a>
                </div>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Stock</th>
                            <th>Precio</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($stock_bajo && mysqli_num_rows($stock_bajo) > 0): ?>
                            <?php while($producto = mysqli_fetch_assoc($stock_bajo)): ?>
                            <tr>
                                <td><?php echo $producto['nombre']; ?></td>
                                <td>
                                    <span class="badge badge-danger">
                                        <?php echo $producto['stock']; ?> unidades
                                    </span>
                                </td>
                                <td>$<?php echo number_format($producto['precio'], 2); ?></td>
                                <td>
                                    <a href="productos/editar.php?id=<?php echo $producto['id']; ?>" class="btn-editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center;">No hay productos con stock bajo</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>