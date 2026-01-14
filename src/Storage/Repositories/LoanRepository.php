<?php
declare(strict_types=1);

namespace Src\Storage\Repositories;

use Src\Domain\Loan;
use Src\Storage\CsvStorage;

/**
 * Repository dei prestiti:
 * - legge/scrive su loans.csv
 * - gestisce prestiti "aperti" (non restituiti) e "chiusi"
 */
final class LoanRepository
{
    private const FILE = 'loans.csv';
    private const HEADER = ['loan_id', 'book_id', 'member_id', 'loan_date', 'return_date'];

    public function __construct(private CsvStorage $storage) {}

    /**
     * @return Loan[]
     */
    public function findAll(): array
    {
        $rows = $this->storage->readAll(self::FILE);
        $loans = [];

        foreach ($rows as $row) {
            $loans[] = new Loan(
                (string)$row['loan_id'],
                (string)$row['book_id'],
                (string)$row['member_id'],
                (string)$row['loan_date'],
                (string)$row['return_date']
            );
        }

        return $loans;
    }

    public function findOpenLoanByBookId(string $bookId): ?Loan
    {
        foreach ($this->findAll() as $loan) {
            if ($loan->bookId() === $bookId && $loan->isOpen()) {
                return $loan;
            }
        }
        return null;
    }

    /**
     * Conta i prestiti aperti di un membro.
     */
    public function countOpenLoansByMember(string $memberId): int
    {
        $count = 0;
        foreach ($this->findAll() as $loan) {
            if ($loan->memberId() === $memberId && $loan->isOpen()) {
                $count++;
            }
        }
        return $count;
    }

    public function save(Loan $loan): void
    {
        $loans = $this->findAll();

        $found = false;
        foreach ($loans as $i => $l) {
            if ($l->loanId() === $loan->loanId()) {
                $loans[$i] = $loan;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $loans[] = $loan;
        }

        $rows = [];
        foreach ($loans as $l) {
            $rows[] = [
                'loan_id' => $l->loanId(),
                'book_id' => $l->bookId(),
                'member_id' => $l->memberId(),
                'loan_date' => $l->loanDate(),
                'return_date' => $l->returnDate(),
            ];
        }

        $this->storage->writeAll(self::FILE, self::HEADER, $rows);
    }

    /**
     * Genera un ID prestito semplice e leggibile.
     * Nota: non è "perfetto" (race conditions), ma sufficiente in un esercizio didattico.
     */
    public function nextLoanId(): string
    {
        $loans = $this->findAll();
        $max = 0;

        foreach ($loans as $loan) {
            // Atteso: L0001, L0002, ...
            $num = (int)ltrim($loan->loanId(), 'L');
            if ($num > $max) {
                $max = $num;
            }
        }

        $next = $max + 1;
        return 'L' . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
    }
}
