<?php
session_start();
require_once 'config.php';

// Verificar autenticación
requireAuth();

$bookId = intval($_GET['id'] ?? 0);

if ($bookId <= 0) {
    header('Location: index.php?error=ID de libro inválido');
    exit;
}

//Get datos del libro
$result = callAPI('GET', "/books/{$bookId}", [], $_SESSION[SESSION_TOKEN_KEY]);


if (!$result['success']) {
    header('Location: index.php?error=' . urlencode('Libro no encontrado o error al obtener la información'));
    exit;
}

$book = $result['data']['data'];

$pageTitle = $book['title'];
$pageHeader = $book['title'];


ob_start();
?>
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="text-center">
                <?php if (!empty($book['coverUrl'])): ?>
                    <img src="<?php echo htmlspecialchars($book['coverUrl']); ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($book['title']); ?>">
                <?php else: ?>
                    <div class="bg-light d-flex align-items-center justify-content-center rounded" style="height: 300px;">
                        <i class="fas fa-book fa-5x text-secondary"></i>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Información del Libro</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 30%">Título</th>
                            <td><?php echo htmlspecialchars($book['title']); ?></td>
                        </tr>
                        <tr>
                            <th>Autor</th>
                            <td><?php echo htmlspecialchars($book['author']); ?></td>
                        </tr>
                        <tr>
                            <th>ISBN</th>
                            <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                        </tr>
                        <?php if (!empty($book['publicationYear'])): ?>
                            <tr>
                                <th>Año de Publicación</th>
                                <td><?php echo htmlspecialchars($book['publicationYear']); ?></td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>

            <div class="d-flex gap-2 mb-4">
                <a href="edit_book.php?id=<?php echo $book['id']; ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar
                </a>
                <a href="#" onclick="return confirmDelete('delete_book.php?id=<?php echo $book['id']; ?>', '¿Eliminar este libro?', 'Esta acción no se puede deshacer.')" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Eliminar
                </a>
                <a href="index.php" class="btn btn-secondary ms-auto">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>

<?php if (!empty($book['description'])): ?>
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">Descripción</h5>
        </div>
        <div class="card-body">
            <p class="card-text"><?php echo nl2br(htmlspecialchars($book['description'])); ?></p>
        </div>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require_once 'layout.php';
?>