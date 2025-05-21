<?php
require_once 'config.php';

function pageTitle($title = '') {
    return empty($title) ? APP_NAME : "$title - " . APP_NAME;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo pageTitle($pageTitle ?? ''); ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Fontawesome para iconos -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Estilos personalizados -->
    <style>
        .navbar-brand {
            font-weight: bold;
        }
        .book-cover {
            max-height: 200px;
            object-fit: cover;
        }
        .book-card {
            height: 100%;
            transition: transform 0.3s;
        }
        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
    </style>

    <?php if (isset($extraCSS)) echo $extraCSS; ?>
</head>
<body>
<!-- Barra de navegación -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php"><?php echo APP_NAME; ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <?php if (isAuthenticated()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Libros</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="add_book.php">Añadir Libro</a>
                    </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <?php if (isAuthenticated()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Cerrar Sesión</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Iniciar Sesión</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Contenido principal -->
<div class="container mb-4">
    <?php if (isset($pageHeader)): ?>
        <h1 class="mb-4"><?php echo $pageHeader; ?></h1>
    <?php endif; ?>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_GET['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_GET['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Aquí irá el contenido específico de cada página -->
    <?php if (isset($content)) echo $content; ?>
</div>

<!-- Footer -->
<footer class="bg-light py-3 mt-auto">
    <div class="container text-center">
        <p class="text-muted mb-0">&copy; <?php echo date('Y'); ?> - <?php echo APP_NAME; ?></p>
    </div>
</footer>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Script común -->
<script>
    // Función para mostrar alertas con SweetAlert
    function showAlert(title, text, icon = 'success') {
        Swal.fire({
            title: title,
            text: text,
            icon: icon,
            confirmButtonColor: '#0d6efd'
        });
    }

    // Confirmación para eliminación
    function confirmDelete(url, title = '¿Está seguro?', text = 'Esta acción no se puede deshacer') {
        Swal.fire({
            title: title,
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
        return false;
    }
</script>

<?php if (isset($extraJS)) echo $extraJS; ?>
</body>
</html>