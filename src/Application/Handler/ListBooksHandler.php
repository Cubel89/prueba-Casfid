<?php

namespace App\Application\Handler;

use App\Application\Query\ListBooksQuery;
use App\Domain\Repository\BookRepositoryInterface;

class ListBooksHandler
{
    private BookRepositoryInterface $bookRepository;

    public function __construct(BookRepositoryInterface $bookRepository)
    {
        $this->bookRepository = $bookRepository;
    }

    public function handle(ListBooksQuery $query): array
    {
        return $this->bookRepository->findAll($query->limit, $query->offset);
    }
}