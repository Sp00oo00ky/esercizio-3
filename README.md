# Biblioteca PHP (CLI)

Progetto in **PHP** (solo riga di comando) che simula una **biblioteca**:
- elenco libri
- prestito libro a un membro
- restituzione libro
- salvataggio dati su file **CSV**
- configurazione iniziale tramite file **.env**

## Requisiti
- PHP 8.1+ (consigliato 8.2+)

## Setup
1. Clona il repository.
2. Copia il file `.env.example` in `.env`:
   ```bash
   cp .env.example .env
   ```
3. Verifica che nella cartella `data/` esistano i file:
- `books.csv`
- `members.csv`
- `loans.csv`


## Comandi

Tutti i comandi partono dalla root del progetto.

Mostra help:
```bash
php bin/console.php help
```

Elenca i libri:
```bash
php bin/console.php books:list
```

Elenca prestiti aperti:
```bash
php bin/console.php loans:list
```

Presta un libro:
```bash
php bin/console.php book:lend B1 M1
```

Restituisci un libro:
```bash
php bin/console.php book:return B1
```


## Come funziona (in breve)
Le classi di dominio sono in src/Domain: `Book`, `Member`, `Loan`.

La logica applicativa è in `src/Library/LibraryService.php`

La persistenza CSV è in:
- `src/Storage/CsvStorage.php`
- `src/Storage/Repositories/*Repository.php`

L'entrypoint CLI è `bin/console.php`

Le date vengono salvate in CSV come `YYYY-MM-DD`.