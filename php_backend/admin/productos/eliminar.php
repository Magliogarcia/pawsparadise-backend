<?php
require_once '../config.php';

// Verificar que sea admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Obtener ID del producto a eliminar
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header('Location: index.php');
    exit();
}

// Verificar que el producto existe
$query = "SELECT * FROM productos WHERE id = $id AND activo = 1";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    header('Location: index.php');
    exit();
}

// Eliminar producto (soft delete - solo desactivar)
$query = "UPDATE productos SET activo = 0 WHERE id = $id";

if (mysqli_query($conn, $query)) {
    // Redirigir con mensaje de éxito
    header('Location: index.php?msg=deleted');
} else {
    // Si hay error, redirigir con mensaje de error
    header('Location: index.php?error=delete_failed');
}
exit();
?>

admin/productos/index.php
<?php
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Obtener todos los productos
$productos = mysqli_query($conn, "
    SELECT * FROM productos 
    WHERE activo = 1 
    ORDER BY id DESC
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · Productos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin/assets/admin.css">
</head>
<body>
    <div class="admin-container">
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
                <a href="index.php" class="active">
                    <i class="fas fa-box"></i> Productos
                </a>
                <a href="../clientes/index.php">
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

        <main class="main-content">
            <header class="top-header">
                <h1>Gestión de Productos</h1>
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo $_SESSION['admin_nombre']; ?></span>
                </div>
            </header>

            <?php if (isset($_GET['msg'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php 
                    if ($_GET['msg'] == 'added') echo 'Producto agregado correctamente';
                    if ($_GET['msg'] == 'updated') echo 'Producto actualizado correctamente';
                    if ($_GET['msg'] == 'deleted') echo 'Producto eliminado correctamente';
                    ?>
                </div>
            <?php endif; ?>

            <div style="margin-bottom: 20px;">
                <a href="agregar.php" class="btn-ver-todas" style="background: var(--primary); color: white; padding: 12px 25px;">
                    <i class="fas fa-plus"></i> Agregar Producto
                </a>
            </div>

            <div class="section-card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($p = mysqli_fetch_assoc($productos)): ?>
                        <tr>
                            <td>#<?php echo str_pad($p['id'], 3, '0', STR_PAD_LEFT); ?></td>
                            <td>
                                <strong><?php echo $p['nombre']; ?></strong><br>
                                <small><?php echo substr($p['descripcion'], 0, 50); ?>...</small>
                            </td>
                            <td><?php echo ucfirst($p['categoria']); ?></td>
                            <td>$<?php echo number_format($p['precio'], 2); ?></td>
                            <td>
                                <?php if ($p['stock'] < 10): ?>
                                    <span class="badge badge-danger"><?php echo $p['stock']; ?></span>
                                <?php else: ?>
                                    <span class="badge badge-success"><?php echo $p['stock']; ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="editar.php?id=<?php echo $p['id']; ?>" class="btn-editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="eliminar.php?id=<?php echo $p['id']; ?>" 
                                   class="btn-eliminar"
                                   onclick="return confirm('¿Eliminar este producto?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table

