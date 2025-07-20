<?php

declare(strict_types=1);

namespace Gemini\Database\QueryBuilder;

use Gemini\Database\DatabaseConnection;
use Gemini\Database\QueryBuilder\Exception\QueryBuilderException;
use Gemini\Database\QueryBuilder\Traits\BaseQueryBuilderTrait;
use InvalidArgumentException;
use PDO;
use Psr\Log\LoggerInterface;

/**
 * **BaseQueryBuilder**
 *
 * A **robust and extendable** base class for query builders, handling:
 * - 🔌 **Database connection management**
 * - 🏗 **Unit of Work for transactional operations**
 * - 📌 **Table name handling**
 * - ♻ **Reusable query logic for child classes**
 *
 * ### **Key Features**
 * - ✅ Centralized database connection logic.
 * - ✅ Ensures consistency across different query builders.
 * - ✅ Provides a **foundation** for extending advanced query-building capabilities.
 *
 * 🏗 **Design Principles:**
 * - **Separation of Concerns (SoC)** → Keeps query execution separate from the logic that builds queries.
 * - **Extensibility** → Child classes (e.g., `QueryBuilder`) can extend this for additional functionality.
 * - **Reusability** → Common logic (transactions, caching, joins, etc.) lives here.
 *
 * 🚀 **Usage Example:**
 * ```
 * class QueryBuilder extends BaseQueryBuilder
 * {
 *     // Custom query logic specific to QueryBuilder
 * }
 * ```
 */
abstract class BaseQueryBuilder
{
    use BaseQueryBuilderTrait;

    /**
     * The table name for the query.
     */
    protected string|null $tableName = null;

    /**
     * Initializes the query builder with a database connection, unit of work, and logger.
     */
    public function __construct(
        public readonly DatabaseConnection $databaseConnection,
        public readonly UnitOfWork         $unitOfWork,
        public readonly LoggerInterface    $logger
    ) {}

    /**
     * Retrieves the table name for the query.
     *
     * @throws QueryBuilderException If the table name is not set.
     */
    public function getTableName() : string
    {
        return $this->tableName ?? throw new QueryBuilderException(message: 'Table name is not set.');
    }

    /**
     * Sets the table name for the query.
     *
     * @throws QueryBuilderException If the table name is empty or invalid.
     */
    public function table(string $tableName) : static
    {
        // Trim any leading or trailing whitespace from the `$tableName` value.
        $tableName = trim($tableName);

        // Validate the table name format (OWASP Recommendation ✅)
        if ($tableName === '' || ! preg_match('/^[a-zA-Z0-9_]+(?:\.[a-zA-Z0-9_]+)?$/', $tableName)) {
            throw new QueryBuilderException(message: 'Invalid table name format.');
        }

        $this->tableName = $tableName;

        return $this;
    }

    /**
     * Quotes a table or column name safely.
     *
     * The provided code first checks if the identifier (`$name`) is already wrapped with the given quoting character
     * (`$quoteChar`) at both the start and end; if so, the identifier is returned as-is. If not, it sanitizes the
     * identifier by stripping away characters that are not letters, digits, underscores, dollar signs, dots, or
     * Unicode characters in the allowed range, ensuring only valid characters remain. If the sanitized result is
     * empty, it throws an exception indicating the identifier is invalid or empty. Finally, it returns the sanitized
     * identifier wrapped with the specified quoting character, ensuring the identifier is securely escaped for use,
     * such as in SQL queries.
     */
    protected function quoteIdentifier(string $name) : string
    {
        // Validate the column name to ensure it contains only safe characters (a-z, A-Z, 0-9, _).
        $this->validateColumnName(name: $name);

        // Retrieve the database driver's name (e.g., mysql, pgsql, sqlite) from the active connection.
        $driver = $this->getConnection()->getAttribute(attribute: PDO::ATTR_DRIVER_NAME);

        // Determine the proper quoting characters based on the database driver.
        $quoteChars = match ($driver) {
            // PostgreSQL and SQLite use double quotes for identifiers.
            'pgsql', 'sqlite' => ['"', '"'],
            // MySQL uses backticks for identifiers.
            'mysql'           => ['`', '`'],
            // SQL Server uses square brackets for identifiers.
            'sqlsrv'          => ['[', ']'],
            // Default fallback to double quotes if the driver is unknown.
            default           => ['"', '"'],
        };

        // Destructure the opening and closing quote characters from the determined array.
        [$openQuote, $closeQuote] = $quoteChars;

        // Split the column name by dots (.) to handle cases like schema.table or table.column.
        $parts = explode('.', $name);

        // Quote and sanitize each part of the split name (e.g., schema or table names).
        $quotedParts = array_map(static function ($part) use ($openQuote, $closeQuote) {
            // If the part is already properly quoted with the correct opening and closing quotes, leave it unchanged.
            if (
                str_starts_with($part, $openQuote)
                && str_ends_with($part, $closeQuote)
            ) {
                return $part;
            }

            // Sanitize the part, allowing only alphanumeric characters, underscores, and multibyte characters.
            $sanitized = preg_replace('/[^a-zA-Z0-9_$\x80-\xFF]/u', '', $part);
            // Validate that the sanitized part is not empty after cleaning.
            if (empty($sanitized)) {
                throw new InvalidArgumentException(message: "Invalid identifier segment: '$part'");
            }

            // Return the properly quoted and sanitized identifier part.
            return $openQuote . $sanitized . $closeQuote;
        }, $parts);

        // Combine the quoted and sanitized parts back into a single string separated by dots (schema.table format).
        return implode('.', $quotedParts);
    }

    /**
     * Validates a column name to prevent SQL injection.
     */
    protected function validateColumnName(string $name) : void
    {
        if (! preg_match('/^[a-zA-Z0-9_]+$/', $name)) {
            throw new InvalidArgumentException(message: "Invalid column name: {$name}");
        }
    }

    /**
     * Retrieves the active database connection.
     */
    public function getConnection() : PDO
    {
        return $this->databaseConnection->getConnection();
    }
}
