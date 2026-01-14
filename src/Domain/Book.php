<?php
declare(strict_types=1);

namespace Src\Domain;

/**
 * Rappresenta un libro della biblioteca.
 * Per semplicità:
 * - un libro ha id, titolo, autore
 * - "available" indica se è prestabile (true) o attualmente prestato (false)
 */
final class Book
{
    public function __construct(
        private string $id,
        private string $title,
        private string $author,
        private bool $available
    ) {}

    public function id(): string
    {
        return $this->id;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function author(): string
    {
        return $this->author;
    }

    public function isAvailable(): bool
    {
        return $this->available;
    }

    /**
     * Cambia lo stato del libro.
     * Notare: in un dominio più ricco potremmo evitare il "setter"
     * e usare metodi come lend() / giveBack().
     */
    public function setAvailable(bool $available): void
    {
        $this->available = $available;
    }
}
