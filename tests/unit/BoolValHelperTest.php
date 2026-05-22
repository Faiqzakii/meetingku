<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class BoolValHelperTest extends CIUnitTestCase
{
    /**
     * @dataProvider truthyProvider
     */
    public function testReturnsTrueForTruthyValues(mixed $input): void
    {
        $this->assertTrue(bool_val($input), 'Expected true for: ' . var_export($input, true));
    }

    /**
     * @dataProvider falsyProvider
     */
    public function testReturnsFalseForFalsyValues(mixed $input): void
    {
        $this->assertFalse(bool_val($input), 'Expected false for: ' . var_export($input, true));
    }

    public static function truthyProvider(): array
    {
        return [
            'php true'      => [true],
            'int 1'         => [1],
            'int 2'         => [2],
            'string 1'      => ['1'],
            'pg t lower'    => ['t'],
            'pg t upper'    => ['T'],
            'pg true'       => ['true'],
            'pg true mixed' => ['True'],
            'yes'           => ['yes'],
            'y'             => ['y'],
            'on'            => ['on'],
            'padded t'      => [' t '],
        ];
    }

    public static function falsyProvider(): array
    {
        return [
            'php false'  => [false],
            'null'       => [null],
            'int 0'      => [0],
            'string 0'   => ['0'],
            'pg f'       => ['f'],
            'pg F upper' => ['F'],
            'false str'  => ['false'],
            'no'         => ['no'],
            'n'          => ['n'],
            'off'        => ['off'],
            'empty str'  => [''],
            'padded f'   => ['  f  '],
            'gibberish'  => ['banana'],
        ];
    }
}
