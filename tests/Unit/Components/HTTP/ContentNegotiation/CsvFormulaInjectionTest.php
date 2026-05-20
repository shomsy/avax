<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\HTTP\ContentNegotiation;

use Avax\Components\HTTP\ContentNegotiation\System\Capabilities\Formats\CsvFormat;
use Avax\Components\HTTP\ContentNegotiation\System\Capabilities\Formats\NeutralizeFormulaCell;
use Avax\Components\HTTP\ContentNegotiation\System\PublicSurface\CsvFormatter;
use PHPUnit\Framework\TestCase;

final class CsvFormulaInjectionTest extends TestCase
{
    // --- NeutralizeFormulaCell unit tests ---

    public function test_equals_formula_cell_is_neutralized() : void
    {
        $result = NeutralizeFormulaCell::escape('=SUM(A1:A10)');

        self::assertSame("'=SUM(A1:A10)", $result);
    }

    public function test_plus_formula_cell_is_neutralized() : void
    {
        $result = NeutralizeFormulaCell::escape('+1+2');

        self::assertSame("'+1+2", $result);
    }

    public function test_minus_formula_cell_is_neutralized() : void
    {
        $result = NeutralizeFormulaCell::escape('-1+2');

        self::assertSame("'-1+2", $result);
    }

    public function test_at_formula_cell_is_neutralized() : void
    {
        $result = NeutralizeFormulaCell::escape('@SUM(A1)');

        self::assertSame("'@SUM(A1)", $result);
    }

    public function test_tab_prefixed_formula_cell_is_neutralized() : void
    {
        $result = NeutralizeFormulaCell::escape("\t=cmd|'/C calc'!A0");

        self::assertSame("'\t=cmd|'/C calc'!A0", $result);
    }

    public function test_cr_prefixed_cell_is_neutralized() : void
    {
        $result = NeutralizeFormulaCell::escape("\r=malicious");

        self::assertSame("'\r=malicious", $result);
    }

    public function test_lf_prefixed_cell_is_neutralized() : void
    {
        $result = NeutralizeFormulaCell::escape("\n=malicious");

        self::assertSame("'\n=malicious", $result);
    }

    public function test_normal_text_passes_unchanged() : void
    {
        $result = NeutralizeFormulaCell::escape('Hello World');

        self::assertSame('Hello World', $result);
    }

    public function test_numeric_string_passes_unchanged() : void
    {
        $result = NeutralizeFormulaCell::escape('12345');

        self::assertSame('12345', $result);
    }

    public function test_negative_number_is_neutralized() : void
    {
        $result = NeutralizeFormulaCell::escape('-42');

        self::assertSame("'-42", $result);
    }

    public function test_empty_string_passes_unchanged() : void
    {
        $result = NeutralizeFormulaCell::escape('');

        self::assertSame('', $result);
    }

    public function test_non_string_value_is_cast_to_string() : void
    {
        $result = NeutralizeFormulaCell::escape(42);

        self::assertSame('42', $result);
    }

    public function test_null_value_is_cast_to_empty_string() : void
    {
        $result = NeutralizeFormulaCell::escape(null);

        self::assertSame('', $result);
    }

    // --- CsvFormat integration tests ---

    public function test_csv_format_neutralizes_formula_cells() : void
    {
        $formatter = new CsvFormat();
        $data = [
            ['name' => '=cmd|"/C calc"!', 'value' => 'safe'],
            ['name' => 'normal', 'value' => '@SUM(A1)'],
        ];

        $output = $formatter->format($data);

        // fputcsv escapes double quotes, so " becomes ""
        self::assertStringContainsString("'=cmd|\"/C calc\"!", str_replace('""', '"', $output));
        self::assertStringContainsString("'@SUM(A1)", $output);
        self::assertStringContainsString('safe', $output);
        self::assertStringContainsString('normal', $output);
    }

    public function test_csv_format_preserves_normal_numeric_cells() : void
    {
        $formatter = new CsvFormat();
        $data = [
            ['amount' => '100', 'tax' => '25'],
        ];

        $output = $formatter->format($data);

        self::assertStringContainsString('100', $output);
        self::assertStringContainsString('25', $output);
        self::assertStringNotContainsString("'", $output);
    }

    public function test_csv_format_handles_single_row_array() : void
    {
        $formatter = new CsvFormat();
        $data = ['name' => '=malicious', 'amount' => '50'];

        $output = $formatter->format($data);

        self::assertStringContainsString("'=malicious", $output);
        self::assertStringContainsString('50', $output);
    }

    public function test_csv_format_handles_non_array_data() : void
    {
        $formatter = new CsvFormat();

        $output = $formatter->format('not-an-array');

        self::assertSame('not-an-array', $output);
    }

    // --- CsvFormatter integration tests ---

    public function test_csv_formatter_neutralizes_formula_cells() : void
    {
        $formatter = new CsvFormatter();
        $data = [
            ['name' => '=cmd|"/C calc"!', 'value' => 'safe'],
            ['name' => 'normal', 'value' => '+1+2'],
        ];

        $output = $formatter->format($data);

        // fputcsv escapes double quotes, so " becomes ""
        self::assertStringContainsString("'=cmd|\"/C calc\"!", str_replace('""', '"', $output));
        self::assertStringContainsString("'+1+2", $output);
    }

    public function test_csv_formatter_preserves_normal_numeric_cells() : void
    {
        $formatter = new CsvFormatter();
        $data = [
            ['amount' => '100', 'tax' => '25'],
        ];

        $output = $formatter->format($data);

        self::assertStringContainsString('100', $output);
        self::assertStringContainsString('25', $output);
        self::assertStringNotContainsString("'", $output);
    }

    public function test_csv_formatter_handles_single_row_array() : void
    {
        $formatter = new CsvFormatter();
        $data = ['name' => '-malicious', 'amount' => '50'];

        $output = $formatter->format($data);

        self::assertStringContainsString("'-malicious", $output);
        self::assertStringContainsString('50', $output);
    }

    public function test_csv_formatter_handles_non_array_data() : void
    {
        $formatter = new CsvFormatter();

        $output = $formatter->format('not-an-array');

        self::assertSame('not-an-array', $output);
    }

    // --- Combined negative test: all dangerous prefixes ---

    public function test_csv_format_neutralizes_all_dangerous_prefixes() : void
    {
        $formatter = new CsvFormat();
        $data = [
            [
                'equals'  => '=SUM(A1)',
                'plus'    => '+A1+B1',
                'minus'   => '-A1',
                'at'      => '@AVERAGE(A1:A10)',
                'tab'     => "\t=DDE('cmd','/C calc')",
            ],
        ];

        $output = $formatter->format($data);

        self::assertStringContainsString("'=SUM(A1)", $output);
        self::assertStringContainsString("'+A1+B1", $output);
        self::assertStringContainsString("'-A1", $output);
        self::assertStringContainsString("'@AVERAGE(A1:A10)", $output);
        self::assertStringContainsString("'\t=DDE('cmd','/C calc')", $output);
    }

    public function test_csv_formatter_neutralizes_all_dangerous_prefixes() : void
    {
        $formatter = new CsvFormatter();
        $data = [
            [
                'equals'  => '=SUM(A1)',
                'plus'    => '+A1+B1',
                'minus'   => '-A1',
                'at'      => '@AVERAGE(A1:A10)',
                'tab'     => "\t=DDE('cmd','/C calc')",
            ],
        ];

        $output = $formatter->format($data);

        self::assertStringContainsString("'=SUM(A1)", $output);
        self::assertStringContainsString("'+A1+B1", $output);
        self::assertStringContainsString("'-A1", $output);
        self::assertStringContainsString("'@AVERAGE(A1:A10)", $output);
        self::assertStringContainsString("'\t=DDE('cmd','/C calc')", $output);
    }
}
