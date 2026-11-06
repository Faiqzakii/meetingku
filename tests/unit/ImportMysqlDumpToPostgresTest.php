<?php

use App\Commands\ImportMysqlDumpToPostgres;
use CodeIgniter\CLI\Commands;
use CodeIgniter\Test\CIUnitTestCase;
use Psr\Log\NullLogger;

/**
 * @internal
 */
final class ImportMysqlDumpToPostgresTest extends CIUnitTestCase
{
    private function makeCommand(): ImportMysqlDumpToPostgres
    {
        return new ImportMysqlDumpToPostgres(new NullLogger(), new Commands(new NullLogger()));
    }

    public function testDefaultTablesExcludeRemovedApiKeys(): void
    {
        $command = $this->makeCommand();
        $property = new ReflectionProperty($command, 'defaultTables');
        $property->setAccessible(true);

        $this->assertSame(['pegawai', 'ruangan', 'meeting', 'wa_settings', 'wa_message_queue'], $property->getValue($command));
    }

    public function testParseInsertStatementRemovesLegacyQueueKeyWithoutChangingHistory(): void
    {
        $command = $this->makeCommand();
        $method = new ReflectionMethod($command, 'parseInsertStatement');
        $method->setAccessible(true);

        $parsed = $method->invoke($command, "INSERT INTO `wa_message_queue` (`id`, `api_key_id`, `message`, `status`, `attempts`, `sent_at`) VALUES (1, 42, 'First', 'sent', 2, '2026-10-22 10:00:00'), (2, NULL, 'Second', 'pending', 0, NULL);");

        $this->assertSame('wa_message_queue', $parsed['table']);
        $this->assertSame(['id', 'message', 'status', 'attempts', 'sent_at'], $parsed['columns']);
        $this->assertSame([
            [1, 'First', 'sent', 2, '2026-10-22 10:00:00'],
            [2, 'Second', 'pending', 0, null],
        ], $parsed['rows']);
    }

    public function testParseInsertStatementPreservesCurrentQueueAndOtherTableColumns(): void
    {
        $command = $this->makeCommand();
        $method = new ReflectionMethod($command, 'parseInsertStatement');
        $method->setAccessible(true);

        $this->assertSame([
            'table' => 'wa_message_queue',
            'columns' => ['id', 'message'],
            'rows' => [[1, 'Current']],
        ], $method->invoke($command, "INSERT INTO `wa_message_queue` (`id`, `message`) VALUES (1, 'Current');"));
        $this->assertSame([
            'table' => 'other_table',
            'columns' => ['id', 'api_key_id'],
            'rows' => [[1, 42]],
        ], $method->invoke($command, 'INSERT INTO `other_table` (`id`, `api_key_id`) VALUES (1, 42);'));
    }

    public function testNormalizeRowConvertsMysqlBooleanIntegers(): void
    {
        $command = $this->makeCommand();
        $method  = new ReflectionMethod($command, 'normalizeRow');
        $method->setAccessible(true);

        $columns = ['id', 'nama_ruangan', 'is_active', 'is_admin'];
        $types   = [
            'id'           => 'integer',
            'nama_ruangan' => 'character varying',
            'is_active'    => 'boolean',
            'is_admin'     => 'boolean',
        ];

        $row = $method->invoke($command, $columns, [1, 'Aula', 1, 0], $types);

        $this->assertSame([1, 'Aula', 't', 'f'], $row);
    }

    public function testNormalizeRowConvertsZeroDatesToNull(): void
    {
        $command = $this->makeCommand();
        $method  = new ReflectionMethod($command, 'normalizeRow');
        $method->setAccessible(true);

        $columns = ['id', 'created_at', 'updated_at'];
        $types   = [
            'id'         => 'integer',
            'created_at' => 'timestamp without time zone',
            'updated_at' => 'timestamp without time zone',
        ];

        $row = $method->invoke($command, $columns, [1, '0000-00-00', '0000-00-00 00:00:00'], $types);

        $this->assertSame([1, null, null], $row);
    }

    public function testNormalizeRowEmptyStringInNonTextColumnBecomesNull(): void
    {
        $command = $this->makeCommand();
        $method  = new ReflectionMethod($command, 'normalizeRow');
        $method->setAccessible(true);

        $columns = ['id', 'is_admin', 'created_at'];
        $types   = [
            'id'         => 'integer',
            'is_admin'   => 'boolean',
            'created_at' => 'timestamp without time zone',
        ];

        $row = $method->invoke($command, $columns, [1, '', ''], $types);

        $this->assertSame([1, null, null], $row);
    }

    public function testParseValuesListStripsPaddingAroundQuotedValues(): void
    {
        $command = $this->makeCommand();
        $method  = new ReflectionMethod($command, 'parseValuesList');
        $method->setAccessible(true);

        // Mimic phpMyAdmin output where commas have padding spaces around quoted values.
        $values = "(6, 'Admin', '123456789123456789', '6281252622621', 'admin', 1)";

        $rows = $method->invoke($command, $values);

        $this->assertCount(1, $rows);
        $this->assertSame([6, 'Admin', '123456789123456789', '6281252622621', 'admin', 1], $rows[0]);
        $this->assertSame(18, strlen($rows[0][2]));
    }

    public function testParseValuesListPreservesEscapedSingleQuoteInsideString(): void
    {
        $command = $this->makeCommand();
        $method  = new ReflectionMethod($command, 'parseValuesList');
        $method->setAccessible(true);

        // Backslash-escaped single quote, as produced by mysqldump.
        $values = "(19, 'Ferika Ainun Nisa\\', S.Tr.Stat.', '199803312019122001')";

        $rows = $method->invoke($command, $values);

        $this->assertCount(1, $rows);
        $this->assertSame(19, $rows[0][0]);
        $this->assertSame("Ferika Ainun Nisa', S.Tr.Stat.", $rows[0][1]);
        $this->assertSame('199803312019122001', $rows[0][2]);
    }
}