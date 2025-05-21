<?php
session_start();
require_once 'config.php';


requireAuth();


$bookId = intval($_GET['id'] ?? 0);

if ($bookId <= 0) {
    header('Location: index.php?error=ID de libro inválido');
    exit;
}


$result = callAPI('GET', "/books/{$bookId}", [], $_SESSION[SESSION_TOKEN_KEY]);


if (!$result['success']) {
    header('Location: index.php?error=' . urlencode('Libro no encontrado o error al obtener la información'));
    exit;
}

$book = $result['data']['data'];
$errors = [];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener y validar datos
    $bookData = [
        'title' => trim($_POST['title'] ?? ''),
        'author' => trim($_POST['author'] ?? ''),
        'isbn' => trim($_POST['isbn'] ?? ''),
        'publication_year' => !empty($_POST['publication_year']) ? intval($_POST['publication_year']) : null,
        'description' => trim($_POST['description'] ?? ''),
        'cover_url' => trim($_POST['cover_url'] ?? '')
    ];

    // Validaciones básicas
    if (empty($bookData['title'])) {
        $errors[] = 'El título es obligatorio';
    }

    if (empty($bookData['author'])) {
        $errors[] = 'El autor es obligatorio';
    }

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

    // Si no hay errores, actualizar el libro
    if (empty($errors)) {
        $result = callAPI('PUT', "/books/{$bookId}", $bookData, $_SESSION[SESSION_TOKEN_KEY]);

        if ($result['success']) {
            header('Location: view_book.php?id=' . $bookId . '&success=Libro actualizado correctamente');
            exit;
        } else {
            $errors[] = 'Error al actualizar el libro: ' . ($result['data']['message'] ?? 'Error desconocido');
        }
    }
} else {
    // Pre-cargar los datos del libro para edición
    $bookData = [
        'title' => $book['title'],
        'author' => $book['author'],
        'isbn' => $book['isbn'],
        'publication_year' => $book['publicationYear'],
        'description' => $book['description'] ?? '',
        'cover_url' => $book['coverUrl'] ?? ''
    ];
}

$pageTitle = 'Editar Libro';
$pageHeader = 'Editar: ' . htmlspecialchars($book['title']);

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
                            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($bookData['title']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="author" class="form-label">Autor</label>
                            <input type="text" class="form-control" id="author" name="author" value="<?php echo htmlspecialchars($bookData['author']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="isbn" class="form-label">ISBN</label>
                            <input type="text" class="form-control" id="isbn" name="isbn" value="<?php echo htmlspecialchars($bookData['isbn']); ?>" required>
                            <small class="text-muted">Cambiar el ISBN puede actualizar la descripción y portada del libro automáticamente.</small>
                        </div>

                        <div class="mb-3">
                            <label for="publication_year" class="form-label">Año de Publicación</label>
                            <input type="number" class="form-control" id="publication_year" name="publication_year"
                                   value="<?php echo htmlspecialchars($bookData['publication_year'] ?? ''); ?>"
                                   min="1000" max="<?php echo date('Y'); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Descripción</label>
                            <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($bookData['description']); ?></textarea>
                            <small class="text-muted">Dejar en blanco para obtener la descripción automáticamente desde la API.</small>
                        </div>

                        <div class="mb-3">
                            <label for="cover_url" class="form-label">URL de Portada</label>
                            <input type="url" class="form-control" id="cover_url" name="cover_url" value="<?php echo htmlspecialchars($bookData['cover_url']); ?>">
                            <small class="text-muted">Dejar en blanco para obtener la portada automáticamente desde la API.</small>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Cambios
                            </button>
                            <a href="view_book.php?id=<?php echo $bookId; ?>" class="btn btn-secondary">
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