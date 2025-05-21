<?php

namespace App\Application\Handler;

use App\Application\Command\UpdateBookCommand;
use App\Domain\Repository\BookRepositoryInterface;
use App\Infrastructure\Service\BookApiService;

class UpdateBookHandler
{
    private BookRepositoryInterface $bookRepository;
    private BookApiService $bookApiService;

    public function __construct(
        BookRepositoryInterface $bookRepository,
        BookApiService $bookApiService
    ) {
        $this->bookRepository = $bookRepository;
        $this->bookApiService = $bookApiService;
    }

    public function handle(UpdateBookCommand $command): bool
    {
        // Obtener el libro existente
        $book = $this->bookRepository->findById($command->id);

        if ($book === null) {
            throw new \RuntimeException("No se encontró el libro con ID: {$command->id}");
        }

        // Actualizar los campos
        $book->setTitle($command->title);
        $book->setAuthor($command->author);
        $book->setIsbn($command->isbn);
        $book->setPublicationYear($command->publicationYear);

        // Si se proporcionan descripción y URL de portada, actualizarlas
        if ($command->description !== null) {
            $book->setDescription($command->description);
        }

        if ($command->coverUrl !== null) {
            $book->setCoverUrl($command->coverUrl);
        }

        //Si no hay descripción o URL de portada, intentar obtenerlas de la API
        if (!$book->getDescription() || !$book->getCoverUrl()) {
            $book = $this->bookApiService->enrichBookWithApiData($book);
        }


        return $this->bookRepository->update($book);
    }
}