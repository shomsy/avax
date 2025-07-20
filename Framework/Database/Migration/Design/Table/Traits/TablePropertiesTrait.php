<?php

declare(strict_types=1);

namespace Gemini\Database\Migration\Design\Table\Traits;

/**
 * Trait TablePropertiesTrait
 *
 * Provides methods for defining table-level properties such as storage engine, character set, collation, and comments.
 * These properties allow for fine-grained control over database table configurations, ensuring optimal performance and
 * compatibility.
 *
 * Supported properties:
 * - Storage engine (e.g., InnoDB, MyISAM)
 * - Character set (e.g., utf8, utf8mb4)
 * - Collation (e.g., utf8_general_ci, utf8mb4_unicode_ci)
 * - Table-level comments for documentation and indexing purposes
 *
 * Usage Example:
 * ```
 * $blueprint->engine('InnoDB');
 * $blueprint->charset('utf8mb4');
 * $blueprint->collation('utf8mb4_unicode_ci');
 * $blueprint->comment('User table storing authentication details');
 * ```
 *
 * @package Gemini\Database\Migration\Table\Traits
 */
trait TablePropertiesTrait
{
    /**
     * Sets the storage engine for the table.
     *
     * @param string $engine The storage engine (e.g., 'InnoDB', 'MyISAM').
     *
     * @return \Gemini\Database\Migration\Design\Table\Table|\Gemini\Database\Migration\Design\Traits\TablePropertiesTrait Usage
     *                                                                                                                     Example:
     *
     * Usage Example:
     * ```
     * $blueprint->engine('InnoDB');
     * ```
     */
    public function engine(string $engine) : self
    {
        $this->tableEngine = $engine;

        return $this;
    }

    /**
     * Sets the character set for the table.
     *
     * @param string $charset The character set (e.g., 'utf8mb4', 'utf8').
     *
     * @return \Gemini\Database\Migration\Design\Table\Traits\TablePropertiesTrait Usage
     *                                                                                                                     Example:
     *
     * Usage Example:
     * ```
     * $blueprint->charset('utf8mb4');
     * ```
     */
    public function charset(string $charset) : self
    {
        $this->charset = $charset;

        return $this;
    }

    /**
     * Sets the collation for the table.
     *
     * @param string $collation The collation (e.g., 'utf8mb4_unicode_ci', 'utf8_general_ci').
     *
     * @return \Gemini\Database\Migration\Design\Table\Traits\TablePropertiesTrait Usage
     *                                                                                                                     Example:
     *
     * Usage Example:
     * ```
     * $blueprint->collation('utf8mb4_unicode_ci');
     * ```
     */
    public function collation(string $collation) : self
    {
        $this->collation = $collation;

        return $this;
    }

    /**
     * Sets a comment for the table.
     *
     * @param string $text The comment text.
     *
     * @return \Gemini\Database\Migration\Design\Table\Traits\TablePropertiesTrait Usage
     *                                                                                                                     Example:
     *
     * Usage Example:
     * ```
     * $blueprint->comment('Stores user authentication data');
     * ```
     */
    public function comment(string $text) : self
    {
        $this->tableComment = addslashes($text);

        return $this;
    }
}
