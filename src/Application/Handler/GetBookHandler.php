<?php

namespace App\Application\Handler;

use App\Application\Query\GetBookQuery;
use App\Domain\Model\Book;
use App\Domain\Repository\BookRepositoryInterface;

class GetBookHandler
{
    private BookRepositoryInterface $bookRepository;

    public function __construct(BookRepositoryInterface $bookRepository)
    {
        $this->bookRepository = $bookRepository;
    }

    public function handle(GetBookQuery $query): ?Book
    {
        return $this->bookRepository->findById($query->id);
    }
}