<?php

declare(strict_types=1);

namespace Gemini\Database\Migration\Runner\Generators\Code;

use Gemini\Database\Migration\Design\Table\Table;
use RuntimeException;

/**
 * Class BlueprintCodeGenerator
 *
 * Responsible for converting an instance of the Table class into
 * syntactically valid PHP schema definition statements (e.g. `$table->string('name')...`).
 *
 * This class is used during migration stub rendering to inject generated code
 * for table schema directly from the domain blueprint object.
 *
 * @package Gemini\Database\Migration\Generators\Code
 */
final readonly class BlueprintCodeGenerator
{
    /**
     * Indentation used for formatting output.
     *
     * @var string
     */
    private const string INDENT = '            ';

    /**
     * Generates formatted PHP code lines from the given Table object.
     *
     * @param Table $blueprint The domain object containing table column definitions.
     *
     * @return string Fully formatted PHP schema definition lines suitable for migration stub.
     *
     * @throws RuntimeException If blueprint contains invalid structures or unsupported definitions.
     */
    public function generate(Table $blueprint) : string
    {
        // Retrieve all raw column definitions from the Table instance.
        $columns = $blueprint->getRawColumnDefinitions();

        // Check for an empty schema and return a placeholder comment if needed.
        if (empty($columns)) {
            return self::INDENT . '// No schema defined in Table.';
        }

        // Map each raw SQL/DSL definition into a properly indented PHP statement.
        $lines = array_map(
            static fn(string $line) : string => self::INDENT . '$table->addColumn(' . var_export($line, true) . ');',
            $columns
        );

        // Join all formatted lines into a single block.
        return implode(PHP_EOL, $lines);
    }
}
