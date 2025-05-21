<?php


use App\Domain\Model\Book;
use PHPUnit\Framework\TestCase;

class BookTest extends TestCase
{
    public function testCreateBook()
    {
        $title = 'El Señor de los Anillos';
        $author = 'J.R.R. Tolkien';
        $isbn = '9780544003415';
        $publicationYear = 1954;

        $book = new Book($title, $author, $isbn, $publicationYear);

        $this->assertEquals($title, $book->getTitle());
        $this->assertEquals($author, $book->getAuthor());
        $this->assertEquals($isbn, $book->getIsbn());
        $this->assertEquals($publicationYear, $book->getPublicationYear());
        $this->assertNull($book->getDescription());
        $this->assertNull($book->getCoverUrl());
    }

    public function testUpdateBook()
    {

        $book = new Book('Harry Potter y la Piedra Filosofal', 'J.K. Rowling', '9788478884957');


        $newTitle = 'Harry Potter inventado';
        $book->setTitle($newTitle);
        $this->assertEquals($newTitle, $book->getTitle());

        $newAuthor = 'Paco Cubel';
        $book->setAuthor($newAuthor);
        $this->assertEquals($newAuthor, $book->getAuthor());


        $newIsbn = '9788478884000';
        $book->setIsbn($newIsbn);
        $this->assertEquals($newIsbn, $book->getIsbn());


        $newYear = 2025;
        $book->setPublicationYear($newYear);
        $this->assertEquals($newYear, $book->getPublicationYear());


        $description = 'Descripción inventada';
        $book->setDescription($description);
        $this->assertEquals($description, $book->getDescription());


        $coverUrl = 'https://covers.openlibrary.org/b/isbn/9788478884964-L.jpg';
        $book->setCoverUrl($coverUrl);
        $this->assertEquals($coverUrl, $book->getCoverUrl());
    }

    public function testValidateIsbn()
    {
        // ISBN-10 válido
        $book = new Book('1984', 'George Orwell', '0306406152');
        $this->assertTrue($book->validateIsbn(), "ISBN-10 válido debería validarse");

        // ISBN-13 válido
        $book = new Book('Cien años de soledad', 'Gabriel García Márquez', '9780306406157');
        $this->assertTrue($book->validateIsbn(), "ISBN-13 válido debería validarse");

        // ISBN con guiones - debe limpiarse y validarse correctamente
        $book = new Book('El Hobbit', 'J.R.R. Tolkien', '978-84-450-0232-7');
        $this->assertTrue($book->validateIsbn(), "ISBN con guiones debería validarse");

        // ISBN inválido (muy corto)
        $book = new Book('Libro inválido', 'Autor desconocido', '12345');
        $this->assertFalse($book->validateIsbn(), "ISBN demasiado corto debería fallar");

        // ISBN con espacios - debe limpiarse y validarse correctamente
        $book = new Book('Don Quijote', 'Miguel de Cervantes', '978 84 233 5392 3');
        $this->assertTrue($book->validateIsbn(), "ISBN con espacios debería validarse");
    }
}