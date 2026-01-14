<?php
declare(strict_types=1);

namespace Src\Domain;

/**
 * Rappresenta un prestito.
 * - loanId: id univoco del prestito
 * - bookId / memberId: riferimenti
 * - loanDate: YYYY-MM-DD (stringa per semplicità e compatibilità CSV)
 * - returnDate: YYYY-MM-DD o stringa vuota se non restituito
 */
final class Loan
{
    public function __construct(
        private string $loanId,
        private string $bookId,
        private string $memberId,
        private string $loanDate,
        private string $returnDate
    ) {}

    public function loanId(): string
    {
        return $this->loanId;
    }

    public function bookId(): string
    {
        return $this->bookId;
    }

    public function memberId(): string
    {
        return $this->memberId;
    }

    public function loanDate(): string
    {
        return $this->loanDate;
    }

    public function returnDate(): string
    {
        return $this->returnDate;
    }

    public function isOpen(): bool
    {
        return $this->returnDate === '';
    }

    public function close(string $returnDate): void
    {
        $this->returnDate = $returnDate;
    }
}
