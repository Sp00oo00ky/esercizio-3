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
        echo "Biblioteca CLI - comandi disponibili:\n";
        echo "  help                      Mostra questa guida\n";
        echo "  books:list                Elenca libri\n";
        echo "  loans:list                Elenca prestiti aperti\n";
        echo "  book:lend <BOOK> <MEM>    Presta un libro a un membro\n";
        echo "  book:return <BOOK>        Registra la restituzione di un libro\n";
        echo "  members:list              Visualizza lista membri";
        echo "\nEsempi:\n";
        echo "  php bin/console.php books:list\n";
        echo "  php bin/console.php book:lend B1 M1\n";
        echo "  php bin/console.php book:return B1\n";
        

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

    default:
        echo "Comando sconosciuto: $command\n";
        echo "Suggerimento: php bin/console.php help\n";
        exit(1);
}
