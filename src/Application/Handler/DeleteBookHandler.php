<?php

namespace App\Application\Handler;

use App\Application\Command\DeleteBookCommand;
use App\Domain\Repository\BookRepositoryInterface;

class DeleteBookHandler
{
    private BookRepositoryInterface $bookRepository;

    public function __construct(BookRepositoryInterface $bookRepository)
    {
        $this->bookRepository = $bookRepository;
    }

    public function handle(DeleteBookCommand $command): bool
    {
        return $this->bookRepository->delete($command->id);
    }
}