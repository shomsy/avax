<?php

declare(strict_types=1);

namespace Avax\Database\Query\AST;

/**
 * Immutable AST node representing a sorting instruction (ORDER BY).
 *
 * @see docs/DSL/QueryStates.md
 */
final readonly class OrderNode
{
    /**
     * @param string|null $column    The technical identifier of the field to be used for sorting.
     * @param string      $direction The sorting orientation, strictly 'ASC' (ascending) or 'DESC' (descending).
     * @param string|null $sql       The literal SQL fragment to be used if the type is 'Raw'.
     * @param string      $type      The classification of the sorting node (e.g., 'Basic', 'Raw').
     */
    public function __construct(
        public string|null $column = null,
        public string      $direction = 'ASC',
        public string|null $sql = null,
        public string      $type = 'Basic'
    ) {}
}
