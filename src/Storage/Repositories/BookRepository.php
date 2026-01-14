<?php
declare(strict_types=1);

namespace Src\Storage\Repositories;

use Src\Domain\Book;
use Src\Storage\CsvStorage;

/**
 * Repository dei libri:
 * - legge/scrive su books.csv
 * - converte righe CSV in oggetti Book
 */
final class BookRepository
{
    private const FILE = 'books.csv';
    private const HEADER = ['id', 'title', 'author', 'available'];

    public function __construct(private CsvStorage $storage) {}

    /**
     * @return Book[]
     */
    public function findAll(): array
    {
        $rows = $this->storage->readAll(self::FILE);
        $books = [];

        foreach ($rows as $row) {
            $books[] = new Book(
                (string)$row['id'],
                (string)$row['title'],
                (string)$row['author'],
                ((string)$row['available']) === '1'
            );
        }

        return $books;
    }

    public function findById(string $bookId): ?Book
    {
        foreach ($this->findAll() as $book) {
            if ($book->id() === $bookId) {
                return $book;
            }
        }
        return null;
    }

    public function save(Book $book): void
    {
        $books = $this->findAll();

        // Aggiorna o aggiunge
        $found = false;
        foreach ($books as $i => $b) {
            if ($b->id() === $book->id()) {
                $books[$i] = $book;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $books[] = $book;
        }

        // Serializza in righe CSV
        $rows = [];
        foreach ($books as $b) {
            $rows[] = [
                'id' => $b->id(),
                'title' => $b->title(),
                'author' => $b->author(),
                'available' => $b->isAvailable() ? '1' : '0',
            ];
        }

        $this->storage->writeAll(self::FILE, self::HEADER, $rows);
    }
}
