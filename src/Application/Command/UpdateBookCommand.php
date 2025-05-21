<?php

namespace App\Application\Command;

class UpdateBookCommand
{
    public int $id;
    public string $title;
    public string $author;
    public string $isbn;
    public ?int $publicationYear;
    public ?string $description;
    public ?string $coverUrl;

    public function __construct(
        int $id,
        string $title,
        string $author,
        string $isbn,
        ?int $publicationYear = null,
        ?string $description = null,
        ?string $coverUrl = null
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->author = $author;
        $this->isbn = $isbn;
        $this->publicationYear = $publicationYear;
        $this->description = $description;
        $this->coverUrl = $coverUrl;
    }
}