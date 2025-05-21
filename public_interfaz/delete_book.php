<?php
session_start();
require_once 'config.php';


requireAuth();


$bookId = intval($_GET['id'] ?? 0);

if ($bookId <= 0) {
    header('Location: index.php?error=ID de libro inválido');
    exit;
}


$result = callAPI('DELETE', "/books/{$bookId}", [], $_SESSION[SESSION_TOKEN_KEY]);


if ($result['success']) {
    header('Location: index.php?success=Libro eliminado correctamente');
} else {
    header('Location: index.php?error=' . urlencode('Error al eliminar el libro: ' . ($result['data']['message'] ?? 'Error desconocido')));
}
exit;