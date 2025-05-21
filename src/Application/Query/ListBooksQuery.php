<?php

namespace App\Application\Query;

class ListBooksQuery
{
    public int $limit;
    public int $offset;

    public function __construct(int $limit = 50, int $offset = 0)
    {
        $this->limit = $limit;
        $this->offset = $offset;
    }
}