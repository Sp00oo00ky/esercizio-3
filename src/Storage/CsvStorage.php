<?php
declare(strict_types=1);

namespace Src\Storage;

/**
 * CsvStorage incapsula lettura/scrittura CSV.
 * Scelte didattiche:
 * - Usiamo fgetcsv/fputcsv (standard PHP)
 * - Ogni CSV ha intestazione (header) nella prima riga
 * - I repository si occupano di mappare righe <-> oggetti Domain
 */
final class CsvStorage
{
    public function __construct(private string $dataDir)
    {
        // Normalizza la directory: rimuove eventuale slash finale
        $this->dataDir = rtrim($this->dataDir, DIRECTORY_SEPARATOR);
    }

    /**
     * Legge tutte le righe di un CSV come array associativi.
     * Esempio: [ ["id"=>"B1","title"=>"..."], ... ]
     */
    public function readAll(string $filename): array
    {
        $path = $this->dataDir . DIRECTORY_SEPARATOR . $filename;
        if (!file_exists($path)) {
            return [];
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            return [];
        }

        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);
            return [];
        }

        $rows = [];
        while (($data = fgetcsv($handle)) !== false) {
            $row = [];
            foreach ($header as $i => $key) {
                $row[$key] = $data[$i] ?? '';
            }
            $rows[] = $row;
        }

        fclose($handle);
        return $rows;
    }

    /**
     * Sovrascrive il CSV con le righe fornite.
     * $header: elenco delle colonne
     * $rows: array di array associativi con chiavi coerenti con $header
     */
    public function writeAll(string $filename, array $header, array $rows): void
    {
        $path = $this->dataDir . DIRECTORY_SEPARATOR . $filename;

        $handle = fopen($path, 'w');
        if ($handle === false) {
            // In un progetto reale lanceremmo un'eccezione.
            return;
        }

        // Scrive intestazione
        fputcsv($handle, $header);

        // Scrive righe
        foreach ($rows as $row) {
            $line = [];
            foreach ($header as $column) {
                $line[] = $row[$column] ?? '';
            }
            fputcsv($handle, $line);
        }

        fclose($handle);
    }
}
