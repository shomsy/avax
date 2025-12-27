<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Core\Builder;

use Avax\Database\QueryBuilder\Core\Grammar\GrammarInterface;

/**
 * technical builder for constructing and compiling JOIN clause conditions.
 *
 * -- intent:
 * Provides a specialized, fluent interface for defining complex relational
 * links (ON conditions) between data sources, facilitating the use of
 * closures to group logical conditions within a join context.
 *
 * -- invariants:
 * - Conditions are captured as technical primitive mappings.
 * - Compilation must utilize the provided Grammar for secure identifier wrapping.
 * - Logical joiners (AND/OR) must be respected in the final SQL string.
 *
 * -- boundaries:
 * - Does NOT handle the high-level join type (INNER/LEFT) (delegated to HasJoins).
 * - Serves strictly as a condition builder for a single JOIN relationship.
 */
final class JoinClause
{
    /** @var array<int, array{first: string, operator: string, second: string, boolean: string}> Collection of captured join conditions. */
    private array $conditions = [];

    /**
     * @param GrammarInterface $grammar The authorized technical SQL grammar used for secure identifier projection.
     */
    public function __construct(private readonly GrammarInterface $grammar) {}

    /**
     * Coordinate the addition of an 'OR ON' logical condition to the join clause.
     *
     * -- intent:
     * Appends a new comparison constraint linked via the OR logical operator,
     * allowing for alternative relationship matches.
     *
     * @param string      $first    The structural identifier of the left-hand column.
     * @param string|null $operator The technical comparison operator (defaults to '=' if second is provided).
     * @param string|null $second   The structural identifier of the right-hand column or the value (if operator is
     *                              omitted).
     *
     * @return self The current builder instance for further fluent configuration.
     */
    public function orOn(string $first, string|null $operator = null, string|null $second = null) : self
    {
        return $this->on(first: $first, operator: $operator, second: $second, boolean: 'OR');
    }

    /**
     * Coordinate the addition of an 'ON' (AND ON) logical condition to the join clause.
     *
     * -- intent:
     * Provides the primary mechanism for defining a relational constraint,
     * supporting both the standard three-argument form and the shortcut
     * two-argument equality form.
     *
     * @param string      $first    The structural identifier of the left-hand column.
     * @param string|null $operator The technical comparison operator or the target value (for shortcuts).
     * @param string|null $second   The structural identifier of the right-hand target column.
     * @param string      $boolean  The logical joiner used to link this condition ('AND' or 'OR').
     *
     * @return self The current builder instance.
     */
    public function on(string $first, string|null $operator = null, string|null $second = null, string $boolean = 'AND') : self
    {
        // Technical shortcut: handle two-argument equality form.
        if ($operator !== null && $second === null) {
            $second   = $operator;
            $operator = '=';
        }

        $this->conditions[] = [
            'first'    => $first,
            'operator' => $operator ?? '=',
            'second'   => $second ?? '',
            'boolean'  => $boolean,
        ];

        return $this;
    }

    /**
     * Coordinate the technical compilation of all captured conditions into a valid SQL string.
     *
     * -- intent:
     * Transforms the internal condition collection into a dialect-aware SQL
     * snapshot, ensuring all identifiers are correctly escaped via the grammar technician.
     *
     * @return string The compiled technical SQL 'ON' clause string.
     */
    public function toSql() : string
    {
        if (empty($this->conditions)) {
            return '';
        }

        $sql = [];
        foreach ($this->conditions as $i => $condition) {
            $prefix   = $i === 0 ? '' : ($condition['boolean'] . ' ');
            $first    = $this->grammar->wrap(value: $condition['first']);
            $operator = $condition['operator'];
            $second   = $this->grammar->wrap(value: $condition['second']);

            $sql[] = $prefix . "{$first} {$operator} {$second}";
        }

        return implode(separator: ' ', array: $sql);
    }
}
