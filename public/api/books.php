<?php

use App\Application\Command\CreateBookCommand;
use App\Application\Command\DeleteBookCommand;
use App\Application\Command\UpdateBookCommand;
use App\Application\DTO\BookDTO;
use App\Application\Handler\CreateBookHandler;
use App\Application\Handler\DeleteBookHandler;
use App\Application\Handler\GetBookHandler;
use App\Application\Handler\ListBooksHandler;
use App\Application\Handler\SearchBooksHandler;
use App\Application\Handler\UpdateBookHandler;
use App\Application\Query\GetBookQuery;
use App\Application\Query\ListBooksQuery;
use App\Application\Query\SearchBooksQuery;
use App\Domain\Repository\BookRepositoryInterface;
use App\Domain\Service\BookService;
use App\Infrastructure\Persistence\Repository\MySQLiBookRepository;
use App\Infrastructure\Service\BookApiService;


require_once __DIR__ . '/auth_middleware.php';


$bookRepository = new MySQLiBookRepository();
$bookApiService = new BookApiService();
$bookService = new BookService($bookRepository);


$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);
$bookId = isset($uri[3]) ? (int)$uri[3] : null;


$requestData = json_decode(file_get_contents('php://input'), true) ?: [];

switch ($method) {
    case 'GET':
        if ($bookId) {
            // Obtener un libro específico
            $handler = new GetBookHandler($bookRepository);
            $book = $handler->handle(new GetBookQuery($bookId));

            if (!$book) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Libro no encontrado']);
                exit;
            }

            echo json_encode(['success' => true, 'data' => BookDTO::fromEntity($book)]);
        } elseif (isset($_GET['search'])) {
            // Buscar libros
            $handler = new SearchBooksHandler($bookService);
            $books = $handler->handle(new SearchBooksQuery($_GET['search']));
            echo json_encode(['success' => true, 'data' => BookDTO::fromEntities($books)]);
        } else {
            // Listar todos los libros
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

            $handler = new ListBooksHandler($bookRepository);
            $books = $handler->handle(new ListBooksQuery($limit, $offset));

            echo json_encode(['success' => true, 'data' => BookDTO::fromEntities($books)]);
        }
        break;

    case 'POST':
        if (empty($requestData['isbn'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'El ISBN es obligatorio']);
            exit;
        }

        // Crear libro
        $command = new CreateBookCommand(
            $requestData['title'] ?? '',
            $requestData['author'] ?? '',
            $requestData['isbn'],
            $requestData['publication_year'] ?? null
        );

        $handler = new CreateBookHandler($bookRepository, $bookApiService);
        $book = $handler->handle($command);

        http_response_code(201);
        echo json_encode(['success' => true, 'data' => BookDTO::fromEntity($book)]);
        break;


    case 'PUT':
    case 'PATCH':
        if (!$bookId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Se requiere ID del libro']);
            exit;
        }

        if (empty($requestData['title']) || empty($requestData['author']) || empty($requestData['isbn'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Faltan datos obligatorios (título, autor, ISBN)']);
            exit;
        }

        //Actualizar libro
        $command = new UpdateBookCommand(
            $bookId,
            $requestData['title'],
            $requestData['author'],
            $requestData['isbn'],
            $requestData['publication_year'] ?? null,
            $requestData['description'] ?? null,
            $requestData['cover_url'] ?? null
        );

        $handler = new UpdateBookHandler($bookRepository, $bookApiService);

        try {
            $result = $handler->handle($command);

            if ($result) {
                //Obtener el libro actualizado
                $getHandler = new GetBookHandler($bookRepository);
                $book = $getHandler->handle(new GetBookQuery($bookId));

                echo json_encode(['success' => true, 'data' => BookDTO::fromEntity($book)]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el libro']);
            }
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'DELETE':
        if (!$bookId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Se requiere ID del libro']);
            exit;
        }

        $handler = new DeleteBookHandler($bookRepository);
        $result = $handler->handle(new DeleteBookCommand($bookId));

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Libro eliminado correctamente']);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'No se pudo eliminar el libro o no existe']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        break;
}