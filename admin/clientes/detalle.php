<?php
require_once '../config.php';

// Verificar que sea admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header('Location: index.php');
    exit();
}

// Obtener información del cliente
$cliente = mysqli_query($conn, "
    SELECT * FROM usuarios 
    WHERE id = $id AND tipo = 'cliente'
")->fetch_assoc();

if (!$cliente) {
    header('Location: index.php');
    exit();
}

// Obtener historial de compras del cliente
$compras = mysqli_query($conn, "
    SELECT p.*, 
           (SELECT COUNT(*) FROM pedido_detalles WHERE pedido_id = p.id) as total_productos
    FROM pedidos p
    WHERE p.usuario_id = $id
    ORDER BY p.fecha_pedido DESC
");

// Estadísticas del cliente
$stats = mysqli_query($conn, "
    SELECT 
        COUNT(*) as total_compras,
        COALESCE(SUM(total), 0) as total_gastado,
        AVG(total) as ticket_promedio,
        MIN(total) as compra_min,
        MAX(total) as compra_max
    FROM pedidos
    WHERE usuario_id = $id
")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Cliente · <?php echo $cliente['nombre']; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin/assets/admin.css">
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
                <a href="../index.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="../productos/index.php">
                    <i class="fas fa-box"></i> Productos
                </a>
                <a href="index.php" class="active">
                    <i class="fas fa-users"></i> Clientes
                </a>
                <a href="../ventas/index.php">
                    <i class="fas fa-shopping-cart"></i> Ventas
                </a>
                <a href="../logout.php">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </nav>
        </aside>

        <!-- Contenido principal -->
        <main class="main-content">
            <header class="top-header">
                <h1>Detalle del Cliente</h1>
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo $_SESSION['admin_nombre']; ?></span>
                </div>
            </header>

            <!-- Información del cliente -->
            <div style="background: white; border-radius: 15px; padding: 30px; margin-bottom: 30px;">
                <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 30px;">
                    <i class="fas fa-user-circle" style="font-size: 5rem; color: var(--primary);"></i>
                    <div>
                        <h2 style="margin-bottom: 5px;"><?php echo $cliente['nombre']; ?></h2>
                        <p style="color: #7F8C8D;">Cliente desde <?php echo date('d/m/Y', strtotime($cliente['fecha_registro'])); ?></p>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                    <div>
                        <p><i class="fas fa-envelope" style="color: var(--primary); width: 25px;"></i> <?php echo $cliente['email']; ?></p>
                        <p><i class="fas fa-phone" style="color: var(--primary); width: 25px;"></i> <?php echo $cliente['telefono'] ?? 'No registrado'; ?></p>
                    </div>
                    <div>
                        <p><i class="fas fa-map-marker-alt" style="color: var(--primary); width: 25px;"></i> <?php echo $cliente['direccion'] ?? 'No registrada'; ?></p>
                        <p><i class="fas fa-city" style="color: var(--primary); width: 25px;"></i> <?php echo $cliente['ciudad'] ?? 'No registrada'; ?></p>
                    </div>
                    <div>
                        <p><i class="fas fa-clock" style="color: var(--primary); width: 25px;"></i> Última sesión: <?php echo $cliente['ultima_sesion'] ? date('d/m/Y H:i', strtotime($cliente['ultima_sesion'])) : 'Nunca'; ?></p>
                    </div>
                </div>
            </div>

            <!-- Estadísticas del cliente -->
            <div class="stats-grid" style="margin-bottom: 30px;">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_compras']; ?></h3>
                        <p>Compras realizadas</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-info">
                        <h3>$<?php echo number_format($stats['total_gastado'], 2); ?></h3>
                        <p>Total gastado</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-info">
                        <h3>$<?php echo number_format($stats['ticket_promedio'] ?? 0, 2); ?></h3>
                        <p>Ticket promedio</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-crown"></i>
                    </div>
                    <div class="stat-info">
                        <h3>$<?php echo number_format($stats['compra_max'] ?? 0, 2); ?></h3>
                        <p>Mayor compra</p>
                    </div>
                </div>
            </div>

            <!-- Historial de compras -->
            <div class="section-card">
                <h2 style="margin-bottom: 20px;"><i class="fas fa-history"></i> Historial de Compras</h2>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th># Pedido</th>
                            <th>Fecha</th>
                            <th>Productos</th>
                            <th>Total</th>
                            <th>Método Pago</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($compras) > 0): ?>
                            <?php while($compra = mysqli_fetch_assoc($compras)): ?>
                            <tr>
                                <td>#<?php echo str_pad($compra['id'], 5, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($compra['fecha_pedido'])); ?></td>
                                <td class="text-center"><?php echo $compra['total_productos']; ?></td>
                                <td><strong>$<?php echo number_format($compra['total'], 2); ?></strong></td>
                                <td>
                                    <i class="fas fa-<?php echo $compra['metodo_pago'] == 'transferencia' ? 'university' : ($compra['metodo_pago'] == 'efectivo' ? 'money-bill' : 'paypal'); ?>"></i>
                                    <?php echo ucfirst($compra['metodo_pago']); ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $compra['estado']; ?>">
                                        <?php echo $compra['estado']; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="../ventas/detalle.php?id=<?php echo $compra['id']; ?>" class="btn-ver">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px;">
                                    <i class="fas fa-shopping-bag" style="font-size: 3rem; color: #e0e0e0; margin-bottom: 10px;"></i>
                                    <p>Este cliente aún no ha realizado compras</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Botón volver -->
            <div style="text-align: center; margin-top: 20px;">
                <a href="index.php" class="btn-cancelar" style="display: inline-block; padding: 12px 30px;">
                    <i class="fas fa-arrow-left"></i> Volver a clientes
                </a>
            </div>
        </main>
    </div>

    <script src="assets/admin.js"></script>
</body>
</html>
