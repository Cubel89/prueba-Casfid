<?php

namespace App\Domain\Repository;

use App\Domain\Model\Book;

interface BookRepositoryInterface
{
    public function findById(int $id): ?Book;

    public function findByIsbn(string $isbn): ?Book;

    public function findByTitle(string $title): array;

    public function findByAuthor(string $author): array;

    public function findAll(int $limit = 50, int $offset = 0): array;

    public function save(Book $book): Book;

    public function update(Book $book): bool;

    public function delete(int $id): bool;
}