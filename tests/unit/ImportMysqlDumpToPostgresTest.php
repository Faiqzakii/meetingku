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