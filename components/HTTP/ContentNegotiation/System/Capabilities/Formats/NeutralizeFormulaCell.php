<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\ContentNegotiation\System\Capabilities\Formats;

/**
 * Neutralizes cell values that could trigger formula execution in spreadsheet applications.
 *
 * OWASP CSV Injection: values starting with =, +, -, @, or tab/CR/LF
 * can execute as formulas when a CSV is opened in Excel/LibreOffice.
 */
final class NeutralizeFormulaCell
{
    private const FORMULA_PREFIXES = ['=', '+', '-', '@'];

    /**
     * Prefix dangerous cell values with a single quote to prevent formula execution.
     *
     * Safe values pass through unchanged.
     */
    public static function escape(mixed $value) : string
    {
        $string = (string) $value;

        if ($string === '') {
            return $string;
        }

        $firstChar = $string[0];

        if (in_array($firstChar, self::FORMULA_PREFIXES, strict: true)) {
            return "'" . $string;
        }

        if ($firstChar === "\t" || $firstChar === "\r" || $firstChar === "\n") {
            return "'" . $string;
        }

        return $string;
    }
}
