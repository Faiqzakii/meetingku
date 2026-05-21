<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database as DatabaseConfig;
use PDO;
use Throwable;

class ImportMysqlDumpToPostgres extends BaseCommand
{
    protected $group = 'Database';
    protected $name = 'db:import-mysql-dump';
    protected $description = 'Import MeetingKu data-only MySQL dump INSERT rows into the configured PostgreSQL database.';

    protected $usage = 'db:import-mysql-dump --file /path/mysql-data.sql [--group default] [--truncate] [--dry-run]';

    protected $options = [
        '--file' => 'MySQL dump file containing INSERT statements.',
        '--group' => 'Target database group from app/Config/Database.php and .env. Default: current defaultGroup.',
        '--tables' => 'Comma-separated table allowlist. Default: MeetingKu core tables.',
        '--truncate' => 'TRUNCATE target tables before import. Destructive.',
        '--dry-run' => 'Parse/count only, no database writes.',
    ];

    /** @var list<string> */
    private array $defaultTables = [
        'pegawai',
        'ruangan',
        'meeting',
        'wa_settings',
        'wa_api_keys',
        'wa_message_queue',
    ];

    public function run(array $params)
    {
        $dumpFile = (string) (CLI::getOption('file') ?? '');
        if ($dumpFile === '' || ! is_file($dumpFile) || ! is_readable($dumpFile)) {
            CLI::error('Dump file is required and must be readable. Use --file /path/mysql-data.sql');
            return EXIT_ERROR;
        }

        $tables = CLI::getOption('tables')
            ? array_values(array_filter(array_map('trim', explode(',', (string) CLI::getOption('tables')))))
            : $this->defaultTables;

        $dryRun = CLI::getOption('dry-run') !== null;
        $truncate = CLI::getOption('truncate') !== null;

        $rowsByTable = [];
        $columnsByTable = [];
        $allowedTables = array_fill_keys($tables, true);

        foreach ($this->readInsertStatements($dumpFile) as $statement) {
            $parsed = $this->parseInsertStatement($statement);
            if ($parsed === null || ! isset($allowedTables[$parsed['table']])) {
                continue;
            }

            $columnsByTable[$parsed['table']] = $parsed['columns'];
            foreach ($parsed['rows'] as $row) {
                $rowsByTable[$parsed['table']][] = $row;
            }
        }

        $totalRows = array_sum(array_map('count', $rowsByTable));
        if ($totalRows === 0) {
            CLI::error('No importable INSERT rows found for tables: ' . implode(', ', $tables));
            return EXIT_ERROR;
        }

        foreach ($tables as $table) {
            CLI::write($table . ': ' . count($rowsByTable[$table] ?? []) . ' rows');
        }

        if ($dryRun) {
            CLI::write('Dry run OK. No database writes performed.', 'green');
            return EXIT_SUCCESS;
        }

        $group = (string) (CLI::getOption('group') ?? config(DatabaseConfig::class)->defaultGroup);
        $target = $this->getDatabaseGroupConfig($group);
        if (($target['DBDriver'] ?? '') !== 'Postgre') {
            CLI::error("Target database group '{$group}' is not Postgre. Set database.{$group}.DBDriver = Postgre in .env.");
            return EXIT_ERROR;
        }

        $pdo = $this->createPostgresPdo($target);
        $columnTypes = $this->loadColumnTypes($pdo, array_keys($rowsByTable));

        // Coerce row values based on real PG column types.
        foreach ($rowsByTable as $table => $rows) {
            $columns = $columnsByTable[$table];
            $types = $columnTypes[$table] ?? [];
            foreach ($rows as $rowIndex => $row) {
                $rowsByTable[$table][$rowIndex] = $this->normalizeRow($columns, $row, $types);
            }
        }

        $this->trySetReplicationRole($pdo, 'replica');
        $pdo->beginTransaction();

        try {

            if ($truncate) {
                $truncateTables = array_values(array_filter($tables, static fn (string $table): bool => isset($rowsByTable[$table])));
                if ($truncateTables !== []) {
                    $pdo->exec('TRUNCATE TABLE ' . implode(', ', array_map($this->quoteIdent(...), $truncateTables)) . ' RESTART IDENTITY CASCADE');
                }
            }

            foreach ($tables as $table) {
                $rows = $rowsByTable[$table] ?? [];
                if ($rows === []) {
                    continue;
                }

                $this->importRows($pdo, $table, $columnsByTable[$table], $rows);
                $this->resetSequence($pdo, $table, 'id');
            }

            $pdo->commit();
            $this->trySetReplicationRole($pdo, 'origin');
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $this->trySetReplicationRole($pdo, 'origin');
            CLI::error($e->getMessage());
            return EXIT_ERROR;
        }

        CLI::write("Import complete: {$totalRows} rows into database group '{$group}'.", 'green');
        return EXIT_SUCCESS;
    }

    /** @return array<string, mixed> */
    private function getDatabaseGroupConfig(string $group): array
    {
        $databaseConfig = config(DatabaseConfig::class);
        $config = $databaseConfig->{$group} ?? null;

        return is_array($config) ? $config : [];
    }

    /** @param array<string, mixed> $config */
    private function createPostgresPdo(array $config): PDO
    {
        $host = (string) ($config['hostname'] ?? 'localhost');
        $port = (int) ($config['port'] ?? 5432);
        $database = (string) ($config['database'] ?? '');
        $username = (string) ($config['username'] ?? '');
        $password = (string) ($config['password'] ?? '');

        return new PDO("pgsql:host={$host};port={$port};dbname={$database}", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    /**
     * Load `data_type` per column from information_schema for the given tables.
     *
     * @param list<string> $tables
     * @return array<string, array<string, string>>
     */
    private function loadColumnTypes(PDO $pdo, array $tables): array
    {
        if ($tables === []) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($tables), '?'));
        $sql = 'SELECT table_name, column_name, data_type FROM information_schema.columns '
            . 'WHERE table_schema = ANY (current_schemas(false)) AND table_name IN (' . $placeholders . ')';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($tables);

        $types = [];
        foreach ($stmt->fetchAll() as $col) {
            $types[$col['table_name']][$col['column_name']] = strtolower((string) $col['data_type']);
        }

        return $types;
    }

    /** @return list<string> */
    private function readInsertStatements(string $file): array
    {
        $statements = [];
        $buffer = '';
        $handle = fopen($file, 'rb');
        if ($handle === false) {
            return [];
        }

        while (($line = fgets($handle)) !== false) {
            if ($buffer === '' && ! preg_match('/^\s*INSERT\s+INTO\s+/i', $line)) {
                continue;
            }

            $buffer .= $line;
            if (str_ends_with(rtrim($line), ';')) {
                $statements[] = $buffer;
                $buffer = '';
            }
        }

        fclose($handle);
        return $statements;
    }

    /** @return array{table:string,columns:list<string>,rows:list<list<mixed>>}|null */
    private function parseInsertStatement(string $statement): ?array
    {
        if (! preg_match('/INSERT\s+INTO\s+`?([a-zA-Z0-9_]+)`?\s*\((.*?)\)\s*VALUES\s*(.*);\s*$/is', $statement, $matches)) {
            return null;
        }

        return [
            'table' => $matches[1],
            'columns' => array_map(static fn (string $column): string => trim($column, " `\t\n\r\0\x0B"), explode(',', $matches[2])),
            'rows' => $this->parseValuesList($matches[3]),
        ];
    }

    /** @return list<list<mixed>> */
    private function parseValuesList(string $values): array
    {
        $rows = [];
        $row = [];
        $value = '';
        $inString = false;
        $escape = false;
        $inRow = false;
        $wasQuoted = false;
        $length = strlen($values);

        for ($i = 0; $i < $length; $i++) {
            $char = $values[$i];

            if ($inString) {
                if ($escape) {
                    $value .= match ($char) {
                        'n' => "\n",
                        'r' => "\r",
                        't' => "\t",
                        '0' => "\0",
                        default => $char,
                    };
                    $escape = false;
                    continue;
                }

                if ($char === '\\') {
                    $escape = true;
                    continue;
                }

                if ($char === "'") {
                    if (($values[$i + 1] ?? '') === "'") {
                        $value .= "'";
                        $i++;
                        continue;
                    }

                    $inString = false;
                    continue;
                }

                $value .= $char;
                continue;
            }

            if ($char === "'") {
                $inString = true;
                $wasQuoted = true;
                // Discard any whitespace padding accumulated before the opening quote.
                $value = '';
                continue;
            }

            if ($char === '(') {
                $inRow = true;
                $row = [];
                $value = '';
                $wasQuoted = false;
                continue;
            }

            if (! $inRow) {
                continue;
            }

            if ($char === ',') {
                $row[] = $this->parseScalar($value, $wasQuoted);
                $value = '';
                $wasQuoted = false;
                continue;
            }

            if ($char === ')') {
                $row[] = $this->parseScalar($value, $wasQuoted);
                $rows[] = $row;
                $row = [];
                $value = '';
                $wasQuoted = false;
                $inRow = false;
                continue;
            }

            // Skip whitespace padding around already-quoted values; quoted content
            // is captured inside the inString branch only.
            if ($wasQuoted && ctype_space($char)) {
                continue;
            }

            $value .= $char;
        }

        return $rows;
    }

    private function parseScalar(string $value, bool $wasQuoted = false): mixed
    {
        if ($wasQuoted) {
            // Quoted value: keep as string verbatim (already stripped of outer quotes).
            return $value;
        }

        $trimmed = trim($value);
        if ($trimmed === '' || strcasecmp($trimmed, 'NULL') === 0) {
            return null;
        }

        if (is_numeric($trimmed)) {
            return str_contains($trimmed, '.') ? (float) $trimmed : (int) $trimmed;
        }

        return $trimmed;
    }

    /**
     * Coerce values to types Postgres can accept via PDO string binding.
     *
     * @param list<string> $columns
     * @param list<mixed> $row
     * @param array<string, string> $columnTypes Map of column_name => PG data_type
     * @return list<mixed>
     */
    private function normalizeRow(array $columns, array $row, array $columnTypes): array
    {
        foreach ($row as $index => $value) {
            $column = $columns[$index] ?? '';
            $type = $columnTypes[$column] ?? '';

            // MySQL zero dates -> NULL.
            if (is_string($value) && in_array($value, ['0000-00-00', '0000-00-00 00:00:00'], true)) {
                $row[$index] = null;
                continue;
            }

            if ($value === null) {
                continue;
            }

            // Empty string in non-text PG columns -> NULL (avoids 22P02 invalid input syntax).
            if ($value === '' && ! $this->isTextLikeType($type)) {
                $row[$index] = null;
                continue;
            }

            if ($type === 'boolean') {
                $row[$index] = $this->coerceBoolean($value);
                continue;
            }

            // Bool inputs targeting non-bool columns: convert to 1/0.
            if (is_bool($value)) {
                $row[$index] = $value ? 1 : 0;
            }
        }

        return $row;
    }

    private function coerceBoolean(mixed $value): ?string
    {
        if (is_bool($value)) {
            return $value ? 't' : 'f';
        }

        if (is_int($value) || is_float($value)) {
            return ((int) $value) === 0 ? 'f' : 't';
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));
            if ($normalized === '') {
                return null;
            }
            if (in_array($normalized, ['1', 't', 'true', 'y', 'yes', 'on'], true)) {
                return 't';
            }
            if (in_array($normalized, ['0', 'f', 'false', 'n', 'no', 'off'], true)) {
                return 'f';
            }
        }

        return null;
    }

    private function isTextLikeType(string $type): bool
    {
        return in_array($type, [
            '',
            'text',
            'character varying',
            'character',
            'varchar',
            'char',
            'json',
            'jsonb',
            'uuid',
            'bytea',
        ], true);
    }

    /** @param list<string> $columns @param list<list<mixed>> $rows */
    private function importRows(PDO $pdo, string $table, array $columns, array $rows): void
    {
        $columnSql = implode(', ', array_map($this->quoteIdent(...), $columns));
        $placeholders = implode(', ', array_map(static fn (string $column): string => ':' . $column, $columns));
        $stmt = $pdo->prepare('INSERT INTO ' . $this->quoteIdent($table) . " ({$columnSql}) VALUES ({$placeholders}) ON CONFLICT DO NOTHING");

        foreach ($rows as $row) {
            $params = [];
            foreach ($columns as $index => $column) {
                $params[':' . $column] = $row[$index] ?? null;
            }
            try {
                $stmt->execute($params);
            } catch (Throwable $e) {
                $id = $params[':id'] ?? 'unknown';
                throw new \RuntimeException("Failed importing {$table} row id {$id}: " . $e->getMessage(), 0, $e);
            }
        }
    }

    private function resetSequence(PDO $pdo, string $table, string $column): void
    {
        $tableLiteral = $pdo->quote($table);
        $columnLiteral = $pdo->quote($column);
        $pdo->exec('SELECT setval(pg_get_serial_sequence(' . $tableLiteral . ', ' . $columnLiteral . '), COALESCE((SELECT MAX(' . $this->quoteIdent($column) . ') FROM ' . $this->quoteIdent($table) . '), 1), true) WHERE pg_get_serial_sequence(' . $tableLiteral . ', ' . $columnLiteral . ') IS NOT NULL');
    }

    private function trySetReplicationRole(PDO $pdo, string $role): void
    {
        try {
            $pdo->exec("SET session_replication_role = {$role}");
        } catch (Throwable) {
            // Non-superuser roles cannot set this. Import still proceeds in table order.
        }
    }

    private function quoteIdent(string $identifier): string
    {
        return '"' . str_replace('"', '""', $identifier) . '"';
    }
}
