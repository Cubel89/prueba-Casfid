<?php

namespace App\Application\Command;

class CreateBookCommand
{
    public string $title;
    public string $author;
    public string $isbn;
    public ?int $publicationYear;

    public function __construct(
        string $title,
        string $author,
        string $isbn,
        ?int $publicationYear = null
    ) {
        $this->title = $title;
        $this->author = $author;
        $this->isbn = $isbn;
        $this->publicationYear = $publicationYear;
    }
}