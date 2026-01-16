<?php
declare(strict_types=1);

/**
 * Entry point CLI.
 * Esempi:
 *   php bin/console.php help
 *   php bin/console.php books:list
 *   php bin/console.php loans:list
 *   php bin/console.php book:lend B1 M1
 *   php bin/console.php book:return B1
 */

require_once __DIR__ . '/../config/bootstrap.php';

use Src\Library\LibraryService;
use Src\Storage\CsvStorage;
use Src\Storage\Repositories\BookRepository;
use Src\Storage\Repositories\LoanRepository;
use Src\Storage\Repositories\MemberRepository;

// Legge configurazione da .env
$dataDir = env('DATA_DIR', './data');
$dateFormat = env('DATE_FORMAT', 'd/m/Y'); // usato solo per eventuali stampe (qui è pronto per estensioni)
$maxLoans = (int)env('MAX_LOANS_PER_MEMBER', '2');

// Costruisce dipendenze (manuale, senza container DI, per semplicità didattica)
$storage = new CsvStorage($dataDir);
$booksRepo = new BookRepository($storage);
$membersRepo = new MemberRepository($storage);
$loansRepo = new LoanRepository($storage);

$service = new LibraryService($booksRepo, $membersRepo, $loansRepo, $maxLoans);

// Parsing argomenti
$args = $argv;
array_shift($args); // rimuove nome script

$command = $args[0] ?? 'help';
$todayYmd = date('Y-m-d'); // nel CSV salviamo sempre in formato stabile

switch ($command) {
    case 'help':
        echo "  help                      Mostra questa guida, e visualizza i comandi eseguibili insieme ad alcuni esempi.\n";

        echo "  books:list                Elenca tutti, dichiarando se sono in prestito o disponibil, insieme al nome dell'autore ed il nome del Libro.\n";

        echo "  loans:list                Mostra l'elenco dei libri prestati fino ad ora, identificando ogni libro ed ogni membro con un codice identificativo ed unico,\ninsieme alla data dell'avvenuto prestito.\n";

        echo "  book:lend <BOOK> <MEM>    Presta un libro a un membro, ed identifica ogni prestito con un codice numerico\n";

        echo "  book:return <BOOK>        Registra la restituzione di un libro, mostrando il codice del libro e del prestito appena chiuso \n";

        echo "  book:status               Stampa i dati del libro e se il libro è prestato, mostra  loan_id e member_id del prestito aperto\n";

        echo "\nEsempi:\n";

        echo "  php bin/console.php books:list\n";

        echo "  php bin/console.php book:lend B1 M1\n";

        echo "  php bin/console.php book:return B1\n";

        echo "  php bin/console.php member:list\n";

        echo " \nConfigurazione (.env) \n";

        echo " DATA_DIR   {$dataDir}            \n";  
        echo " Mostra percorso della directory contenente i file CSV dei dati (libri,membri,prestiti).\n\n";

        echo " DATE_FORMAT   {$dateFormat}          \n";
        echo " Mostra il formato della data di ogni prestito ('giorno'-'mese'-'anno')\n\n";

        echo " MAX_LOANS_PER_MEMBER   {$maxLoans}  \n";
        echo " Mostra il massimo numero di prestiti che una persona puo effettuare, e cioe '2' \n\n";







        exit(0);
     //creazione funzione member list per stampare nome e id tutti membri//
        
        case 'members:list':
            $members = $membersRepo->findAll();
            if ($members){
                foreach($members as $member){
                    echo $member->id()." | " .$member->fullName()."\n";
             }}
             else {
                echo "Nessun membro";
             }
             exit (0);

    case 'books:list':
        foreach ($service->listBooks() as $line) {
            echo $line . "\n";
        }
        exit(0);

    case 'loans:list':
        foreach ($service->listOpenLoans() as $line) {
            echo $line . "\n";
        }
        exit(0);

    case 'book:lend':
        // Nota: qui è facile introdurre errori -> utile per i corsisti
        $bookId = $args[1] ?? '';
        $memberId = $args[2] ?? '';

        if ($bookId === '' || $memberId === '') {
            echo "Uso: php bin/console.php book:lend <BOOK_ID> <MEMBER_ID>\n";
            exit(1);
        }

        echo $service->lendBook($bookId, $memberId, $todayYmd) . "\n";
        exit(0);

    case 'book:return':
        $bookId = $args[1] ?? '';
        if ($bookId === '') {
            echo "Uso: php bin/console.php book:return <BOOK_ID>\n";
            exit(1);
        }

        echo $service->returnBook($bookId, $todayYmd) . "\n";
        exit(0);

    case 'book:status':
    $bookId = $args[1] ?? '';

    if ($bookId === '') {
        echo "Uso: php bin/console.php book:status <BOOK_ID>\n";
        exit(1);
    }

    $result = $service->bookStatus($bookId);

    // Se ritorna una stringa → errore
    if (is_string($result)) {
        echo $result . "\n";
        exit(0);
    }

    // Stampa dati libro
    echo "ID: " . $result['id'] . "\n";
    echo "Titolo: " . $result['titolo'] . "\n";
    echo "Autore: " . $result['autore'] . "\n";
    echo "Stato: " . $result['stato'] . "\n";

    // Se in prestito, mostra info prestito
    if ($result['stato'] === 'PRESTITO') {
        echo "Loan ID: " . $result['loan_id'] . "\n";
        echo "Member ID: " . $result['member_id'] . "\n";
    }

    exit(0);


    default:
        echo "Comando sconosciuto: $command\n";
        echo "Suggerimento: php bin/console.php help\n";
        exit(1);
}
