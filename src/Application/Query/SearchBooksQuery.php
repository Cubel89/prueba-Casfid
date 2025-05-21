<?php

namespace App\Application\Query;

class SearchBooksQuery
{
    public string $searchTerm;

    public function __construct(string $searchTerm)
    {
        $this->searchTerm = $searchTerm;
    }
}