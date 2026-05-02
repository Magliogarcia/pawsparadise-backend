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

// Obtener información de la venta
$venta = mysqli_query($conn, "
    SELECT p.*, u.nombre as cliente_nombre, u.email, u.telefono, 
           u.direccion, u.ciudad
    FROM pedidos p
    JOIN usuarios u ON p.usuario_id = u.id
    WHERE p.id = $id
")->fetch_assoc();

if (!$venta) {
    header('Location: index.php');
    exit();
}

// Obtener productos de la venta
$productos = mysqli_query($conn, "
    SELECT pd.*, pr.nombre as producto_nombre, pr.descripcion
    FROM pedido_detalles pd
    JOIN productos pr ON pd.producto_id = pr.id
    WHERE pd.pedido_id = $id
");

// Calcular subtotal
$subtotal = 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Venta #<?php echo str_pad($id, 5, '0', STR_PAD_LEFT); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin/assets/admin.css">
    <style>
        .detalle-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .info-item {
            margin-bottom: 15px;
        }
        
        .info-label {
            font-weight: 600;
            color: var(--text-muted);
            display: block;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-size: 1.1rem;
            color: var(--dark);
        }
        
        .total-box {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: right;
        }
        
        .total-box h3 {
            color: white;
            margin-bottom: 10px;
        }
        
        .total-box .cantidad {
            font-size: 2rem;
            font-weight: 700;
        }
        
        .btn-imprimir {
            background: var(--secondary);
            color: white;
            padding: 12px 25px;
            border-radius: 50px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .btn-imprimir:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(52, 152, 219, 0.3);
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
                <h1>Detalle de Venta #<?php echo str_pad($id, 5, '0', STR_PAD_LEFT); ?></h1>
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo $_SESSION['admin_nombre']; ?></span>
                </div>
            </header>

            <!-- Información del pedido -->
            <div class="detalle-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2><i class="fas fa-info-circle"></i> Información del Pedido</h2>
                    <span class="badge badge-<?php echo $venta['estado']; ?>" style="font-size: 1rem;">
                        <?php echo strtoupper($venta['estado']); ?>
                    </span>
                </div>
                
                <div class="info-grid">
                    <div>
                        <div class="info-item">
                            <span class="info-label">Fecha del pedido:</span>
                            <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($venta['fecha_pedido'])); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Método de pago:</span>
                            <span class="info-value">
                                <i class="fas fa-<?php echo $venta['metodo_pago'] == 'transferencia' ? 'university' : ($venta['metodo_pago'] == 'efectivo' ? 'money-bill' : 'paypal'); ?>"></i>
                                <?php echo ucfirst($venta['metodo_pago']); ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Pago confirmado:</span>
                            <span class="info-value">
                                <?php if ($venta['pago_confirmado']): ?>
                                    <span style="color: var(--success);">Sí <i class="fas fa-check-circle"></i></span>
                                <?php else: ?>
                                    <span style="color: var(--danger);">No <i class="fas fa-times-circle"></i></span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                    <div>
                        <div class="info-item">
                            <span class="info-label">Cliente:</span>
                            <span class="info-value"><?php echo $venta['cliente_nombre']; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email:</span>
                            <span class="info-value"><?php echo $venta['email']; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Teléfono:</span>
                            <span class="info-value"><?php echo $venta['telefono']; ?></span>
                        </div>
                    </div>
                </div>
                
                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #ecf0f1;">
                    <h3><i class="fas fa-map-marker-alt"></i> Dirección de envío</h3>
                    <p><?php echo $venta['direccion']; ?>, <?php echo $venta['ciudad']; ?></p>
                </div>
            </div>

            <!-- Productos -->
            <div class="detalle-card">
                <h2 style="margin-bottom: 20px;"><i class="fas fa-box"></i> Productos</h2>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Descripción</th>
                            <th>Cantidad</th>
                            <th>Precio Unit.</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($prod = mysqli_fetch_assoc($productos)): 
                            $subtotal += $prod['subtotal'];
                        ?>
                        <tr>
                            <td><strong><?php echo $prod['producto_nombre']; ?></strong></td>
                            <td><small><?php echo substr($prod['descripcion'], 0, 50); ?>...</small></td>
                            <td class="text-center"><?php echo $prod['cantidad']; ?></td>
                            <td>$<?php echo number_format($prod['precio_unitario'], 2); ?></td>
                            <td><strong>$<?php echo number_format($prod['subtotal'], 2); ?></strong></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                
                <div style="margin-top: 30px;">
                    <div class="total-box">
                        <h3>Total del pedido</h3>
                        <div class="cantidad">$<?php echo number_format($venta['total'], 2); ?></div>
                        <small>Incluye envío: $5.00</small>
                    </div>
                </div>
            </div>

            <!-- Acciones -->
            <div style="display: flex; gap: 20px; justify-content: center;">
                <a href="index.php" class="btn-cancelar">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <button onclick="window.print()" class="btn-imprimir">
                    <i class="fas fa-print"></i> Imprimir
                </button>
                <?php if ($venta['estado'] == 'pendiente'): ?>
                <a href="actualizar_estado.php?id=<?php echo $id; ?>&estado=procesando" 
                   class="btn-guardar" style="flex: 0 auto;" 
                   onclick="return confirm('¿Marcar como procesando?')">
                    <i class="fas fa-clock"></i> Procesar pedido
                </a>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="assets/admin.js"></script>
</body>
</html>

