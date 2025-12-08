<?php

/**
 * Provides base functionality for SQL column alteration definitions.
 *
 * This abstract class serves as a foundation for implementing various column
 * alteration strategies in database migrations, following the Domain-Driven Design
 * pattern and Single Responsibility Principle.
 *
 * @category Database
 * @package  Avax\Database\Migration\Design\Table\Alter\Definitions\Base
 * @author   Development Team
 * @version  1.0.0
 * @since    1.0.0
 */
declare(strict_types=1);

/**
 * Base abstract class representing a column alteration definition.
 *
 * This class serves as a blueprint for defining SQL representation of
 * a column alteration in a database migration. It provides an abstract
 * method that subclasses must implement to define specific
 * SQL generation logic for altering database table columns.
 */

namespace Avax\Database\Migration\Design\Table\Alter\Definitions\Base;

abstract readonly class AlterColumnDefinition
{
    /**
     * Converts the column alteration definition to its SQL representation.
     *
     * This method must be implemented by concrete classes to provide specific SQL
     * generation logic for different types of column alterations.
     *
     * @return string The SQL statement representing the column alteration
     *
     * @throws \RuntimeException When SQL generation fails
     */
    abstract public function toSql() : string;
}