<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Core\Grammar;

use Avax\Database\Query\QueryState;

/**
 * Functional contract for translating query builder state into dialect-specific SQL.
 *
 * -- intent: decouple the logical representation of a query from its physical SQL dialect.
 */
interface GrammarInterface
{
    /**
     * Compile a full SELECT statement from the provided query state.
     *
     * -- intent: transform the metadata container into a valid SQL retrieval string.
     *
     * @param QueryState $state Current builder parameters
     *
     * @return string
     */
    public function compileSelect(QueryState $state) : string;

    /**
     * Compile a technical INSERT statement.
     *
     * -- intent: transform the mutation state into a valid SQL insertion string.
     *
     * @param QueryState $state Current builder parameters
     *
     * @return string
     */
    public function compileInsert(QueryState $state) : string;

    /**
     * Compile a technical UPDATE statement.
     *
     * -- intent: transform the mutation state and filters into a valid SQL modification string.
     *
     * @param QueryState $state Current builder parameters
     *
     * @return string
     */
    public function compileUpdate(QueryState $state) : string;

    /**
     * Compile a technical DELETE statement.
     *
     * -- intent: transform the query criteria into a valid SQL removal string.
     *
     * @param QueryState $state Current builder parameters
     *
     * @return string
     */
    public function compileDelete(QueryState $state) : string;

    /**
     * Compile the SQL to create a new database.
     *
     * @param string $name Database name
     *
     * @return string
     */
    public function compileCreateDatabase(string $name) : string;

    /**
     * Compile the SQL to drop a database.
     *
     * @param string $name Database name
     *
     * @return string
     */
    public function compileDropDatabase(string $name) : string;

    /**
     * Securely wrap a database identifier (table or column) or Expression.
     *
     * -- intent: protect against reserved keyword conflicts and SQL injection risks.
     *
     * @param mixed $value Technical name or Expression object
     *
     * @return string
     */
    public function wrap(mixed $value) : string;
}
