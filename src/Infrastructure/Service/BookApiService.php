<?php

namespace App\Infrastructure\Service;

use App\Domain\Model\Book;
use App\Infrastructure\Api\OpenLibraryApiClient;

class BookApiService
{
    private OpenLibraryApiClient $apiClient;

    public function __construct()
    {
        $this->apiClient = new OpenLibraryApiClient();
    }

    public function enrichBookWithApiData(Book $book): Book
    {
        // Incluso si hay descripción o portada, actualizar si faltan datos básicos
        $needsEnrichment = empty($book->getTitle()) ||
            empty($book->getAuthor()) ||
            $book->getPublicationYear() === null ||
            !$book->getDescription() ||
            !$book->getCoverUrl();

        if ($needsEnrichment) {
            $apiInfo = $this->apiClient->getBookInfoByIsbn($book->getIsbn());

            if (empty($book->getTitle()) && isset($apiInfo['title'])) {
                $book->setTitle($apiInfo['title']);
            }

            if (empty($book->getAuthor()) && isset($apiInfo['author'])) {
                $book->setAuthor($apiInfo['author']);
            }

            if ($book->getPublicationYear() === null && isset($apiInfo['publication_year'])) {
                $book->setPublicationYear($apiInfo['publication_year']);
            }

            if (!$book->getDescription() && isset($apiInfo['description'])) {
                $book->setDescription($apiInfo['description']);
            }

            if (!$book->getCoverUrl() && isset($apiInfo['cover_url'])) {
                $book->setCoverUrl($apiInfo['cover_url']);
            }
        }

        return $book;
    }
}