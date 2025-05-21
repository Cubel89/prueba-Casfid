<?php
session_start();
require_once 'config.php';


requireAuth();


$limit = $_GET['limit'] ?? 10;
$offset = $_GET['offset'] ?? 0;
$search = $_GET['search'] ?? '';
$page = ($offset / $limit) + 1;

//Construir URL para la API
$apiUrl = '/books?limit=' . $limit . '&offset=' . $offset;
if (!empty($search)) {
    $apiUrl = '/books?search=' . urlencode($search);
}

// Get libros
$result = callAPI('GET', $apiUrl, [], $_SESSION[SESSION_TOKEN_KEY]);


if (!$result['success']) {
    $error = 'Error al obtener los libros: ' . ($result['data']['message'] ?? 'Error desconocido');
}


$books = $result['success'] ? $result['data']['data'] : [];
$totalBooks = count($books);

$pageTitle = 'Listado de Libros';
$pageHeader = 'Biblioteca de Libros';


ob_start();
?>
    <div class="mb-4">
        <div class="row">
            <div class="col-md-6">
                <form action="" method="get" class="d-flex">
                    <input type="text" name="search" class="form-control me-2" placeholder="Buscar por título o autor..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary">Buscar</button>
                    <?php if (!empty($search)): ?>
                        <a href="index.php" class="btn btn-secondary ms-2">Limpiar</a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="add_book.php" class="btn btn-success">
                    <i class="fas fa-plus"></i> Añadir Libro
                </a>
            </div>
        </div>
    </div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php elseif (empty($books)): ?>
    <div class="alert alert-info">
        <?php if (!empty($search)): ?>
            No se encontraron libros que coincidan con la búsqueda.
        <?php else: ?>
            No hay libros en la biblioteca. ¡Añade el primero!
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">
        <?php foreach ($books as $book): ?>
            <div class="col">
                <div class="card h-100 book-card">
                    <div class="text-center p-3">
                        <?php if (!empty($book['coverUrl'])): ?>
                            <img src="<?php echo htmlspecialchars($book['coverUrl']); ?>" class="book-cover" alt="<?php echo htmlspecialchars($book['title']); ?>">
                        <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="fas fa-book fa-4x text-secondary"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($book['title']); ?></h5>
                        <p class="card-text">
                            <strong>Autor:</strong> <?php echo htmlspecialchars($book['author']); ?><br>
                            <strong>ISBN:</strong> <?php echo htmlspecialchars($book['isbn']); ?><br>
                            <?php if (!empty($book['publicationYear'])): ?>
                                <strong>Año:</strong> <?php echo htmlspecialchars($book['publicationYear']); ?>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="card-footer bg-transparent border-top-0">
                        <a href="view_book.php?id=<?php echo $book['id']; ?>" class="btn btn-primary w-100">
                            Ver detalles
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Paginación -->
    <?php if (!empty($search)): ?>
        <div class="d-flex justify-content-center mt-4">
            <a href="index.php" class="btn btn-secondary">Volver a todos los libros</a>
        </div>
    <?php elseif ($totalBooks >= $limit): ?>
        <div class="d-flex justify-content-center mt-4">
            <nav aria-label="Navegación de páginas">
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?limit=<?php echo $limit; ?>&offset=<?php echo ($page - 2) * $limit; ?>">
                                Anterior
                            </a>
                        </li>
                    <?php endif; ?>

                    <li class="page-item active">
                        <span class="page-link"><?php echo $page; ?></span>
                    </li>

                    <?php if ($totalBooks == $limit): ?>
                        <li class="page-item">
                            <a class="page-link" href="?limit=<?php echo $limit; ?>&offset=<?php echo $page * $limit; ?>">
                                Siguiente
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php
$content = ob_get_clean();
require_once 'layout.php';
?>