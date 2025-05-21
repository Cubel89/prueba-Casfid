<?php

namespace App\Infrastructure\Persistence\Repository;

use App\Domain\Model\Book;
use App\Domain\Repository\BookRepositoryInterface;
use App\Infrastructure\Persistence\Database\MySQLiConnection;

class MySQLiBookRepository implements BookRepositoryInterface
{
    private MySQLiConnection $db;

    public function __construct()
    {
        $this->db = MySQLiConnection::getInstance();
    }

    public function findById(int $id): ?Book
    {
        $id = (int)$id;
        $sql = "SELECT * FROM books WHERE id = {$id} LIMIT 1";
        $row = $this->db->getRow($sql);

        if (!$row) {
            return null;
        }

        return $this->createBookFromRow($row);
    }

    public function findByIsbn(string $isbn): ?Book
    {
        $isbn = $this->db->escape($isbn);
        $sql = "SELECT * FROM books WHERE isbn = '{$isbn}' LIMIT 1";
        $row = $this->db->getRow($sql);

        if (!$row) {
            return null;
        }

        return $this->createBookFromRow($row);
    }

    public function findByTitle(string $title): array
    {
        $title = $this->db->escape($title);
        $sql = "SELECT * FROM books WHERE title LIKE '%{$title}%'";
        $rows = $this->db->getAllRows($sql);

        return $this->createBooksFromRows($rows);
    }

    public function findByAuthor(string $author): array
    {
        $author = $this->db->escape($author);
        $sql = "SELECT * FROM books WHERE author LIKE '%{$author}%'";
        $rows = $this->db->getAllRows($sql);

        return $this->createBooksFromRows($rows);
    }

    public function findAll(int $limit = 50, int $offset = 0): array
    {
        $limit = (int)$limit;
        $offset = (int)$offset;
        $sql = "SELECT * FROM books LIMIT {$limit} OFFSET {$offset}";
        $rows = $this->db->getAllRows($sql);

        return $this->createBooksFromRows($rows);
    }

    public function save(Book $book): Book
    {
        $title = $this->db->escape($book->getTitle());
        $author = $this->db->escape($book->getAuthor());
        $isbn = $this->db->escape($book->getIsbn());
        $publicationYear = $book->getPublicationYear() ? (int)$book->getPublicationYear() : 'NULL';
        $description = $book->getDescription() ? "'" . $this->db->escape($book->getDescription()) . "'" : 'NULL';
        $coverUrl = $book->getCoverUrl() ? "'" . $this->db->escape($book->getCoverUrl()) . "'" : 'NULL';

        // Si publicationYear es NULL, no debe ir entre comillas
        $yearValue = $publicationYear === 'NULL' ? $publicationYear : "'{$publicationYear}'";

        $sql = "INSERT INTO books (title, author, isbn, publication_year, description, cover_url) 
                VALUES ('{$title}', '{$author}', '{$isbn}', {$yearValue}, {$description}, {$coverUrl})";

        $this->db->query($sql);

        if ($this->db->affectedRows() <= 0) {
            throw new \RuntimeException("No se pudo guardar el libro");
        }

        $book->setId($this->db->lastInsertId());

        return $book;
    }

    public function update(Book $book): bool
    {
        $id = (int)$book->getId();
        $title = $this->db->escape($book->getTitle());
        $author = $this->db->escape($book->getAuthor());
        $isbn = $this->db->escape($book->getIsbn());
        $publicationYear = $book->getPublicationYear() ? (int)$book->getPublicationYear() : 'NULL';
        $description = $book->getDescription() ? "'" . $this->db->escape($book->getDescription()) . "'" : 'NULL';
        $coverUrl = $book->getCoverUrl() ? "'" . $this->db->escape($book->getCoverUrl()) . "'" : 'NULL';

        // Si publicationYear es NULL, no debe ir entre comillas
        $yearValue = $publicationYear === 'NULL' ? $publicationYear : "'{$publicationYear}'";

        $sql = "UPDATE books 
                SET title = '{$title}', 
                    author = '{$author}', 
                    isbn = '{$isbn}', 
                    publication_year = {$yearValue}, 
                    description = {$description}, 
                    cover_url = {$coverUrl} 
                WHERE id = {$id}";

        $this->db->query($sql);

        return $this->db->affectedRows() > 0;
    }

    public function delete(int $id): bool
    {
        $id = (int)$id;
        $sql = "DELETE FROM books WHERE id = {$id}";

        $this->db->query($sql);

        return $this->db->affectedRows() > 0;
    }

    private function createBookFromRow(array $row): Book
    {
        $book = new Book(
            $row['title'],
            $row['author'],
            $row['isbn'],
            $row['publication_year'],
            $row['description'],
            $row['cover_url']
        );

        $book->setId($row['id']);

        return $book;
    }

    private function createBooksFromRows(array $rows): array
    {
        $books = [];

        foreach ($rows as $row) {
            $books[] = $this->createBookFromRow($row);
        }

        return $books;
    }
}