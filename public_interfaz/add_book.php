<?php
session_start();
require_once 'config.php';

requireAuth();

$errors = [];
$bookData = [
    'title' => '',
    'author' => '',
    'isbn' => '',
    'publication_year' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookData = [
        'title' => trim($_POST['title'] ?? ''),
        'author' => trim($_POST['author'] ?? ''),
        'isbn' => trim($_POST['isbn'] ?? ''),
        'publication_year' => !empty($_POST['publication_year']) ? intval($_POST['publication_year']) : null
    ];

    // Validaciones básicas
    if (empty($bookData['isbn'])) {
        $errors[] = 'El ISBN es obligatorio';
    } elseif (!preg_match('/^[0-9X\-]{10,17}$/', $bookData['isbn'])) {
        $errors[] = 'El formato del ISBN es inválido';
    }

    if (!empty($bookData['publication_year'])) {
        $currentYear = date('Y');
        if ($bookData['publication_year'] < 1000 || $bookData['publication_year'] > $currentYear) {
            $errors[] = "El año de publicación debe estar entre 1000 y {$currentYear}";
        }
    }

    // Si no hay errores, crear el libro
    if (empty($errors)) {
        $result = callAPI('POST', '/books', $bookData, $_SESSION[SESSION_TOKEN_KEY]);

        if ($result['success']) {
            $newBookId = $result['data']['data']['id'] ?? 0;
            $redirectUrl = 'view_book.php?id=' . $newBookId;
            header('Location: ' . $redirectUrl . '&success=Libro añadido correctamente');
            exit;
        } else {
            $errors[] = 'Error al crear el libro: ' . ($result['data']['message'] ?? 'Error desconocido');
        }
    }
}

$pageTitle = 'Añadir Libro';
$pageHeader = 'Añadir Nuevo Libro';


ob_start();
?>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Información del Libro</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="title" class="form-label">Título</label>
                            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($bookData['title']); ?>">
                            <small class="text-muted">Se completará automáticamente si se deja en blanco.</small>
                        </div>

                        <div class="mb-3">
                            <label for="author" class="form-label">Autor</label>
                            <input type="text" class="form-control" id="author" name="author" value="<?php echo htmlspecialchars($bookData['author']); ?>">
                            <small class="text-muted">Se completará automáticamente si se deja en blanco.</small>
                        </div>

                        <div class="mb-3">
                            <label for="isbn" class="form-label">ISBN</label>
                            <input type="text" class="form-control" id="isbn" name="isbn" value="<?php echo htmlspecialchars($bookData['isbn']); ?>"
                                   placeholder="Ej: 978-3-16-148410-0" required>
                            <small class="text-muted">El ISBN será utilizado para obtener la descripción y portada del libro automáticamente.</small>
                        </div>

                        <div class="mb-3">
                            <label for="publication_year" class="form-label">Año de Publicación</label>
                            <input type="number" class="form-control" id="publication_year" name="publication_year"
                                   value="<?php echo htmlspecialchars($bookData['publication_year'] ?? ''); ?>"
                                   min="1000" max="<?php echo date('Y'); ?>">
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar
                            </button>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php
$content = ob_get_clean();
require_once 'layout.php';
?>