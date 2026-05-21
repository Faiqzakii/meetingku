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
    public function testNormalizeRowConvertsMysqlBooleanIntegers(): void
    {
        $command = new ImportMysqlDumpToPostgres(new NullLogger(), new Commands(new NullLogger()));
        $method = new ReflectionMethod($command, 'normalizeRow');
        $method->setAccessible(true);

        $row = $method->invoke($command, ['id', 'nama_ruangan', 'is_active', 'is_admin'], [1, 'Aula', 1, 0]);

        $this->assertSame([1, 'Aula', true, false], $row);
    }

    public function testNormalizeRowConvertsZeroDatesToNull(): void
    {
        $command = new ImportMysqlDumpToPostgres(new NullLogger(), new Commands(new NullLogger()));
        $method = new ReflectionMethod($command, 'normalizeRow');
        $method->setAccessible(true);

        $row = $method->invoke($command, ['id', 'created_at', 'updated_at'], [1, '0000-00-00', '0000-00-00 00:00:00']);

        $this->assertSame([1, null, null], $row);
    }
}