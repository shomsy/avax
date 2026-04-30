<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\State\AST;

/**
 * Immutable AST node representing a sorting instruction (ORDER BY).
 *
 * @see /docs/Foundation/Database/DSL/QueryStates.md
 */
final readonly class OrderNode
{
    public string $type;
    public string|null $sql;
    public string $direction;
    public string|null $column;

    /**
     * @param string|null $column    The technical identifier of the field to be used for sorting.
     * @param string      $direction The sorting orientation, strictly 'ASC' (ascending) or 'DESC' (descending).
     * @param string|null $sql       The literal SQL fragment to be used if the type is 'Raw'.
     * @param string      $type      The classification of the sorting node (e.g., 'Basic', 'Raw').
     */
    public function __construct(
        string $column = null,
        string $direction = null,
        string $sql = null,
        string $type = 'Basic',
    )
    {
        $direction ??= 'ASC';
        $this->column    = $column;
        $this->direction = $direction;
        $this->sql       = $sql;
        $this->type      = $type;
    }
}
