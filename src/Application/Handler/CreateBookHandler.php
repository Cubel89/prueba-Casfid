<?php

namespace App\Application\Handler;

use App\Application\Command\CreateBookCommand;
use App\Domain\Model\Book;
use App\Domain\Repository\BookRepositoryInterface;
use App\Infrastructure\Service\BookApiService;

class CreateBookHandler
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

    public function handle(CreateBookCommand $command): Book
    {
        // Crear el libro con los datos básicos
        $book = new Book(
            $command->title,
            $command->author,
            $command->isbn,
            $command->publicationYear
        );

        //Datos de la API
        $book = $this->bookApiService->enrichBookWithApiData($book);

        // Guardar en la base de datos
        return $this->bookRepository->save($book);
    }
}