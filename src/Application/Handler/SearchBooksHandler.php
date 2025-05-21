<?php

namespace App\Application\Handler;

use App\Application\Query\SearchBooksQuery;
use App\Domain\Service\BookService;

class SearchBooksHandler
{
    private BookService $bookService;

    public function __construct(BookService $bookService)
    {
        $this->bookService = $bookService;
    }

    public function handle(SearchBooksQuery $query): array
    {
        return $this->bookService->searchBooks($query->searchTerm);
    }
}