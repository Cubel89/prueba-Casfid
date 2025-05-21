<?php

namespace App\Application\Query;

class GetBookQuery
{
    public int $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }
}