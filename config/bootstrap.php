<?php
declare(strict_types=1);

/**
 * Bootstrap dell'applicazione:
 * - carica le variabili da .env
 * - registra un autoloader semplice per le classi in /src
 * - espone una funzione env() per leggere configurazioni
 */

function loadEnv(string $envPath): void
{
    if (!file_exists($envPath)) {
        // In un progetto reale potresti lanciare un'eccezione.
        // Qui restiamo "beginner friendly" e proseguiamo con valori di default.
        return;
    }

    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);

        // Ignora commenti e righe vuote
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        // Formato atteso: CHIAVE=valore
        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) {
            continue;
        }

        $key = trim($parts[0]);
        $value = trim($parts[1]);

        // Rimuove eventuali virgolette
        $value = trim($value, "\"'");

        // Salva in $_ENV e anche come variabile d'ambiente del processo
        $_ENV[$key] = $value;
        putenv($key . '=' . $value);
    }
}

/**
 * Legge una variabile d'ambiente con un valore di default.
 */
function env(string $key, ?string $default = null): string
{
    $value = $_ENV[$key] ?? getenv($key);
    if ($value === false || $value === null || $value === '') {
        return (string)$default;
    }
    return (string)$value;
}

/**
 * Autoloader minimale:
 * Trasforma "Src\Qualcosa\Classe" in "src/Qualcosa/Classe.php".
 */
spl_autoload_register(function (string $className): void {
    $prefix = 'Src\\';
    if (!str_starts_with($className, $prefix)) {
        return;
    }

    $relative = substr($className, strlen($prefix));
    $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';

    $fullPath = __DIR__ . '/../src/' . $relativePath;
    if (file_exists($fullPath)) {
        require_once $fullPath;
    }
});

// Carica .env (se presente)
loadEnv(__DIR__ . '/../.env');
