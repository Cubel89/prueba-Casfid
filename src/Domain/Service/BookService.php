<?php

namespace App\Domain\Service;

use App\Domain\Model\Book;
use App\Domain\Repository\BookRepositoryInterface;

class BookService
{
    private BookRepositoryInterface $bookRepository;

    public function __construct(BookRepositoryInterface $bookRepository)
    {
        $this->bookRepository = $bookRepository;
    }

    /**
     * Crea un nuevo libro validando primero que el ISBN sea único
     */
    public function createBook(Book $book): Book
    {
        // Verificar que el ISBN es válido
        if (!$book->validateIsbn()) {
            throw new \InvalidArgumentException("El ISBN proporcionado no es válido");
        }

        // Verificar que el ISBN no existe ya
        $existingBook = $this->bookRepository->findByIsbn($book->getIsbn());
        if ($existingBook) {
            throw new \RuntimeException("Ya existe un libro con el ISBN: " . $book->getIsbn());
        }

        return $this->bookRepository->save($book);
    }

    /**
     * Actualiza un libro existente
     */
    public function updateBook(Book $book): bool
    {
        // Verificar que el libro existe
        if (!$book->getId()) {
            throw new \InvalidArgumentException("No se puede actualizar un libro sin ID");
        }

        $existingBook = $this->bookRepository->findById($book->getId());
        if (!$existingBook) {
            throw new \RuntimeException("No se encontró el libro con ID: " . $book->getId());
        }

        // Si se cambió el ISBN, verificar que el nuevo ISBN sea único
        if ($book->getIsbn() !== $existingBook->getIsbn()) {
            $bookWithSameIsbn = $this->bookRepository->findByIsbn($book->getIsbn());
            if ($bookWithSameIsbn && $bookWithSameIsbn->getId() !== $book->getId()) {
                throw new \RuntimeException("Ya existe otro libro con el ISBN: " . $book->getIsbn());
            }
        }

        return $this->bookRepository->update($book);
    }

    /**
     * Busca libros por título o autor
     */
    public function searchBooks(string $query): array
    {
        $booksByTitle = $this->bookRepository->findByTitle($query);
        $booksByAuthor = $this->bookRepository->findByAuthor($query);

        // Combinar y eliminar duplicados
        $allBooks = array_merge($booksByTitle, $booksByAuthor);

        // Eliminar duplicados basados en ID
        $uniqueBooks = [];
        $seenIds = [];

        foreach ($allBooks as $book) {
            if (!in_array($book->getId(), $seenIds)) {
                $uniqueBooks[] = $book;
                $seenIds[] = $book->getId();
            }
        }

        return $uniqueBooks;
    }
}