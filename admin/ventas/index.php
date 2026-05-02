<?php
require_once '../config.php';

// Verificar que sea admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Obtener todas las ventas con información del cliente
$ventas = mysqli_query($conn, "
    SELECT p.*, u.nombre as cliente_nombre, u.email as cliente_email,
           (SELECT COUNT(*) FROM pedido_detalles WHERE pedido_id = p.id) as total_productos
    FROM pedidos p
    JOIN usuarios u ON p.usuario_id = u.id
    ORDER BY p.fecha_pedido DESC
");

// Estadísticas de ventas
$stats = mysqli_query($conn, "
    SELECT 
        COUNT(*) as total_ventas,
        SUM(total) as ingresos_totales,
        AVG(total) as ticket_promedio,
        SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as ventas_pendientes
    FROM pedidos
")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ventas · Admin Paws Paradise</title>
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
                <a href="../clientes/index.php">
                    <i class="fas fa-users"></i> Clientes
                </a>
                <a href="index.php" class="active">
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
                <h1>Gestión de Ventas</h1>
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo $_SESSION['admin_nombre']; ?></span>
                </div>
            </header>

            <!-- Estadísticas de ventas -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_ventas']; ?></h3>
                        <p>Ventas totales</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-info">
                        <h3>$<?php echo number_format($stats['ingresos_totales'] ?? 0, 2); ?></h3>
                        <p>Ingresos totales</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div class="stat-info">
                        <h3>$<?php echo number_format($stats['ticket_promedio'] ?? 0, 2); ?></h3>
                        <p>Ticket promedio</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['ventas_pendientes']; ?></h3>
                        <p>Pendientes</p>
                    </div>
                </div>
            </div>

            <!-- Filtros rápidos -->
            <div style="margin-bottom: 20px; display: flex; gap: 10px;">
                <a href="?estado=todas" class="btn-ver-todas <?php echo !isset($_GET['estado']) || $_GET['estado'] == 'todas' ? 'active' : ''; ?>">Todas</a>
                <a href="?estado=pendiente" class="btn-ver-todas <?php echo isset($_GET['estado']) && $_GET['estado'] == 'pendiente' ? 'active' : ''; ?>">Pendientes</a>
                <a href="?estado=completado" class="btn-ver-todas <?php echo isset($_GET['estado']) && $_GET['estado'] == 'completado' ? 'active' : ''; ?>">Completadas</a>
                <a href="?estado=cancelado" class="btn-ver-todas <?php echo isset($_GET['estado']) && $_GET['estado'] == 'cancelado' ? 'active' : ''; ?>">Canceladas</a>
            </div>

            <!-- Tabla de ventas -->
            <div class="section-card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th># Pedido</th>
                            <th>Cliente</th>
                            <th>Fecha</th>
                            <th>Productos</th>
                            <th>Total</th>
                            <th>Método Pago</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($venta = mysqli_fetch_assoc($ventas)): ?>
                        <tr>
                            <td>#<?php echo str_pad($venta['id'], 5, '0', STR_PAD_LEFT); ?></td>
                            <td>
                                <strong><?php echo $venta['cliente_nombre']; ?></strong><br>
                                <small><?php echo $venta['cliente_email']; ?></small>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($venta['fecha_pedido'])); ?></td>
                            <td class="text-center"><?php echo $venta['total_productos']; ?></td>
                            <td><strong>$<?php echo number_format($venta['total'], 2); ?></strong></td>
                            <td>
                                <?php
                                $icono_pago = [
                                    'transferencia' => 'university',
                                    'efectivo' => 'money-bill',
                                    'paypal' => 'paypal'
                                ];
                                $icono = $icono_pago[$venta['metodo_pago']] ?? 'credit-card';
                                ?>
                                <i class="fas fa-<?php echo $icono; ?>"></i> 
                                <?php echo ucfirst($venta['metodo_pago']); ?>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo $venta['estado']; ?>">
                                    <?php echo $venta['estado']; ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <a href="detalle.php?id=<?php echo $venta['id']; ?>" class="btn-ver">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($venta['estado'] == 'pendiente'): ?>
                                    <a href="actualizar_estado.php?id=<?php echo $venta['id']; ?>&estado=procesando" 
                                       class="btn-editar" onclick="return confirm('¿Marcar como procesando?')">
                                        <i class="fas fa-clock"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script src="assets/admin.js"></script>
</body>
</html>
