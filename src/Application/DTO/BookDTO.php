<?php

namespace App\Application\DTO;

use App\Domain\Model\Book;

class BookDTO
{
    public int $id;
    public string $title;
    public string $author;
    public string $isbn;
    public ?int $publicationYear;
    public ?string $description;
    public ?string $coverUrl;

    public static function fromEntity(Book $book): self
    {
        $dto = new self();
        $dto->id = $book->getId();
        $dto->title = $book->getTitle();
        $dto->author = $book->getAuthor();
        $dto->isbn = $book->getIsbn();
        $dto->publicationYear = $book->getPublicationYear();
        $dto->description = $book->getDescription();
        $dto->coverUrl = $book->getCoverUrl();

        return $dto;
    }

    public static function fromEntities(array $books): array
    {
        return array_map(fn(Book $book) => self::fromEntity($book), $books);
    }
}