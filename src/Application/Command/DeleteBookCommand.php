<?php

namespace App\Application\Command;

class DeleteBookCommand
{
    public int $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }
}