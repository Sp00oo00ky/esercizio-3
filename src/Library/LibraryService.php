<?php
declare(strict_types=1);

namespace Src\Library;

use Src\Domain\Loan;
use Src\Storage\Repositories\BookRepository;
use Src\Storage\Repositories\LoanRepository;
use Src\Storage\Repositories\MemberRepository;

/**
 * LibraryService contiene la logica applicativa principale:
 * - elenco libri e stato
 * - prestito libro
 * - restituzione libro
 *
 * Nota didattica: qui teniamo le regole di business, i repository gestiscono solo I/O.
 */
final class LibraryService
{
    public function __construct(
        private BookRepository $books,
        private MemberRepository $members,
        private LoanRepository $loans,
        private int $maxLoansPerMember
    ) {}

    public function listBooks(): array
    {
        // Restituiamo array di stringhe "pronte" per la console (semplice per i principianti)
        $out = [];
        foreach ($this->books->findAll() as $book) {
            $status = $book->isAvailable() ? 'DISPONIBILE' : 'PRESTITO';
            $out[] = sprintf(
                '%s | %s | %s | %s',
                $book->id(),
                $book->title(),
                $book->author(),
                $status
            );
        }
        return $out;
    }

    public function lendBook(string $bookId, string $memberId, string $todayYmd): string
    {
        $book = $this->books->findById($bookId);
        if ($book === null) {
            return "Errore: libro non trovato (id=$bookId).";
        }

        $member = $this->members->findById($memberId);
        if ($member === null) {
            return "Errore: membro non trovato (id=$memberId).";
        }

        if (!$book->isAvailable()) {
            return "Errore: il libro $bookId non è disponibile.";
        }

        $openLoans = $this->loans->countOpenLoansByMember($memberId);
        if ($openLoans >= $this->maxLoansPerMember) {
            return "Errore: il membro $memberId ha già raggiunto il limite prestiti ({$this->maxLoansPerMember}).";
        }

        // Crea prestito
        $loanId = $this->loans->nextLoanId();
        $loan = new Loan($loanId, $bookId, $memberId, $todayYmd, '');

        // Aggiorna stato libro
        $book->setAvailable(false);

        // Salva tutto
        $this->loans->save($loan);
        $this->books->save($book);

        return "OK: prestito creato ($loanId). Libro $bookId assegnato a $memberId.";
    }

    public function returnBook(string $bookId, string $todayYmd): string
    {
        $book = $this->books->findById($bookId);
        if ($book === null) {
            return "Errore: libro non trovato (id=$bookId).";
        }

        $openLoan = $this->loans->findOpenLoanByBookId($bookId);
        if ($openLoan === null) {
            return "Errore: nessun prestito aperto trovato per il libro $bookId.";
        }

        // Chiude il prestito
        $openLoan->close($todayYmd);

        // Rende disponibile il libro
        $book->setAvailable(true);

        // Salva
        $this->loans->save($openLoan);
        $this->books->save($book);

        return "OK: libro $bookId restituito. Prestito {$openLoan->loanId()} chiuso.";
    }

    public function listOpenLoans(): array
    {
        $out = [];
        foreach ($this->loans->findAll() as $loan) {
            if ($loan->isOpen()) {
                $out[] = sprintf(
                    '%s | book=%s | member=%s | dal=%s',
                    $loan->loanId(),
                    $loan->bookId(),
                    $loan->memberId(),
                    $loan->loanDate()
                );
            }
        }

        if ($out === []) {
            $out[] = 'Nessun prestito aperto.';
        }

        return $out;
    }
}
