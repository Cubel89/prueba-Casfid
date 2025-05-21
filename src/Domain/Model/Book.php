<?php

namespace App\Domain\Model;

class Book
{
    private int $id;
    private string $title;
    private string $author;
    private string $isbn;
    private ?int $publicationYear;
    private ?string $description;
    private ?string $coverUrl;
    private \DateTime $createdAt;
    private \DateTime $updatedAt;

    public function __construct(
        string $title,
        string $author,
        string $isbn,
        ?int $publicationYear = null,
        ?string $description = null,
        ?string $coverUrl = null
    ) {
        $this->title = $title;
        $this->author = $author;
        $this->isbn = $isbn;
        $this->publicationYear = $publicationYear;
        $this->description = $description;
        $this->coverUrl = $coverUrl;
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function setAuthor(string $author): self
    {
        $this->author = $author;
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function getIsbn(): string
    {
        return $this->isbn;
    }

    public function setIsbn(string $isbn): self
    {
        $this->isbn = $isbn;
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function getPublicationYear(): ?int
    {
        return $this->publicationYear;
    }

    public function setPublicationYear(?int $publicationYear): self
    {
        $this->publicationYear = $publicationYear;
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function getCoverUrl(): ?string
    {
        return $this->coverUrl;
    }

    public function setCoverUrl(?string $coverUrl): self
    {
        $this->coverUrl = $coverUrl;
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }
    public function validateIsbn(): bool
    {
        // Limpiar el ISBN - eliminar cualquier carácter que no sea dígito o 'X'
        $cleanIsbn = preg_replace('/[^0-9X]/', '', $this->isbn);

        return (strlen($cleanIsbn) === 10 || strlen($cleanIsbn) === 13);
    }
}