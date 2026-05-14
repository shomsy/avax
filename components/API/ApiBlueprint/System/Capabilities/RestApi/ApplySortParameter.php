<?php

declare(strict_types=1);

namespace Avax\Components\API\ApiBlueprint\System\Capabilities\RestApi;

final readonly class ApplySortParameter
{
    /**
     * @param array<string, string> $sorts
     */
    public function __construct(
        private array $sorts = [],
    ) {}

    /**
     * @return array<string, string>
     */
    public function all() : array
    {
        return $this->sorts;
    }

    public function orderBy(string $column, string $direction = 'asc') : self
    {
        $sorts          = $this->sorts;
        $sorts[$column] = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return new self($sorts);
    }

    public function directions() : string
    {
        $parts = [];

        foreach ($this->sorts as $column => $direction) {
            $parts[] = "{$column} {$direction}";
        }

        return implode(', ', $parts);
    }

    public function isEmpty() : bool
    {
        return $this->sorts === [];
    }
}
