<?php
require_once '../config.php';

// Verificar que sea admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$error = '';
$success = '';

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recoger y limpiar datos
    $nombre = limpiarDato($_POST['nombre']);
    $descripcion = limpiarDato($_POST['descripcion']);
    $precio = floatval($_POST['precio']);
    $stock = intval($_POST['stock']);
    $categoria = limpiarDato($_POST['categoria']);
    
    // Validar que no estén vacíos
    if (empty($nombre) || empty($descripcion) || $precio <= 0 || $stock < 0) {
        $error = "Todos los campos son obligatorios";
    } else {
        // Insertar en la base de datos
        $query = "INSERT INTO productos (nombre, descripcion, precio, stock, categoria) 
                  VALUES ('$nombre', '$descripcion', $precio, $stock, '$categoria')";
        
        if (mysqli_query($conn, $query)) {
            $success = "Producto agregado correctamente";
            // Redirigir después de 2 segundos
            header("refresh:2;url=index.php?msg=added");
        } else {
            $error = "Error al guardar: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · Agregar Producto</title>
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

        <!-- Contenido principal -->
        <main class="main-content">
            <header class="top-header">
                <h1>Agregar Producto</h1>
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo $_SESSION['admin_nombre']; ?></span>
                </div>
            </header>

            <!-- Formulario -->
            <div class="form-container">
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="POST" action="" onsubmit="return validarProducto()">
                    <div class="form-group">
                        <label for="nombre">
                            <i class="fas fa-tag"></i> Nombre del Producto
                        </label>
                        <input type="text" id="nombre" name="nombre" required 
                               placeholder="Ej: Alimento Premium para Perros">
                    </div>

                    <div class="form-group">
                        <label for="descripcion">
                            <i class="fas fa-align-left"></i> Descripción
                        </label>
                        <textarea id="descripcion" name="descripcion" rows="4" required 
                                  placeholder="Describe el producto..."></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="categoria">
                                <i class="fas fa-list"></i> Categoría
                            </label>
                            <select id="categoria" name="categoria" required>
                                <option value="">Seleccionar...</option>
                                <option value="alimentos">Alimentos</option>
                                <option value="accesorios">Accesorios</option>
                                <option value="juguetes">Juguetes</option>
                                <option value="camas">Camas</option>
                                <option value="medicinas">Medicinas</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="precio">
                                <i class="fas fa-dollar-sign"></i> Precio ($)
                            </label>
                            <input type="number" id="precio" name="precio" step="0.01" required 
                                   placeholder="0.00" min="0">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="stock">
                            <i class="fas fa-boxes"></i> Stock disponible
                        </label>
                        <input type="number" id="stock" name="stock" required 
                               placeholder="0" min="0">
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-guardar">
                            <i class="fas fa-save"></i> Guardar Producto
                        </button>
                        <a href="index.php" class="btn-cancelar">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script src="assets/admin.js"></script>
</body>
</html>
