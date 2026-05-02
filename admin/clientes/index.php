<?php
require_once '../config.php';

// Verificar que sea admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Obtener todos los clientes con estadísticas
$clientes = mysqli_query($conn, "
    SELECT 
        u.*,
        COUNT(p.id) as total_compras,
        COALESCE(SUM(p.total), 0) as total_gastado,
        MAX(p.fecha_pedido) as ultima_compra
    FROM usuarios u
    LEFT JOIN pedidos p ON u.id = p.usuario_id
    WHERE u.tipo = 'cliente'
    GROUP BY u.id
    ORDER BY u.fecha_registro DESC
");

// Estadísticas globales
$stats = mysqli_query($conn, "
    SELECT 
        COUNT(*) as total_clientes,
        COUNT(DISTINCT p.usuario_id) as clientes_activos,
        AVG(total_compras) as promedio_compras
    FROM usuarios u
    LEFT JOIN (
        SELECT usuario_id, COUNT(*) as total_compras
        FROM pedidos
        GROUP BY usuario_id
    ) p ON u.id = p.usuario_id
    WHERE u.tipo = 'cliente'
")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes · Admin Paws Paradise</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin/assets/admin.css">
    <style>
        .cliente-info small {
            color: #7F8C8D;
        }
        .ultima-compra {
            font-size: 0.85rem;
            color: #7F8C8D;
        }
    </style>
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
                <h1>Gestión de Clientes</h1>
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo $_SESSION['admin_nombre']; ?></span>
                </div>
            </header>

            <!-- Estadísticas de clientes -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_clientes']; ?></h3>
                        <p>Clientes totales</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['clientes_activos']; ?></h3>
                        <p>Clientes activos</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['promedio_compras'] ?? 0, 1); ?></h3>
                        <p>Compras promedio</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo date('Y'); ?></h3>
                        <p>Año actual</p>
                    </div>
                </div>
            </div>

            <!-- Buscador de clientes -->
            <div style="margin-bottom: 20px;">
                <input type="text" id="buscarCliente" placeholder="Buscar cliente por nombre o email..." 
                       style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 10px;">
            </div>

            <!-- Tabla de clientes -->
            <div class="section-card">
                <table class="data-table" id="tablaClientes">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Cliente</th>
                            <th>Contacto</th>
                            <th>Registro</th>
                            <th>Compras</th>
                            <th>Total gastado</th>
                            <th>Última compra</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($cliente = mysqli_fetch_assoc($clientes)): ?>
                        <tr class="cliente-row">
                            <td>#<?php echo str_pad($cliente['id'], 3, '0', STR_PAD_LEFT); ?></td>
                            <td>
                                <strong><?php echo $cliente['nombre']; ?></strong>
                            </td>
                            <td class="cliente-info">
                                <i class="fas fa-envelope"></i> <?php echo $cliente['email']; ?><br>
                                <i class="fas fa-phone"></i> <?php echo $cliente['telefono'] ?? 'No registrado'; ?>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($cliente['fecha_registro'])); ?></td>
                            <td class="text-center">
                                <span class="badge badge-<?php echo $cliente['total_compras'] > 0 ? 'success' : 'warning'; ?>">
                                    <?php echo $cliente['total_compras']; ?>
                                </span>
                            </td>
                            <td><strong>$<?php echo number_format($cliente['total_gastado'], 2); ?></strong></td>
                            <td class="ultima-compra">
                                <?php echo $cliente['ultima_compra'] ? date('d/m/Y', strtotime($cliente['ultima_compra'])) : 'Nunca'; ?>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <a href="detalle.php?id=<?php echo $cliente['id']; ?>" class="btn-ver">
                                        <i class="fas fa-eye"></i>
                                    </a>
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
    <script>
        // Buscador de clientes
        document.getElementById('buscarCliente').addEventListener('keyup', function() {
            let texto = this.value.toLowerCase();
            let filas = document.querySelectorAll('.cliente-row');
            
            filas.forEach(fila => {
                let textoFila = fila.textContent.toLowerCase();
                if (textoFila.includes(texto)) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
