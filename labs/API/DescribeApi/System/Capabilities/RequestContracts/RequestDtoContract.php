<?php

declare(strict_types=1);

namespace Avax\Labs\API\DescribeApi\System\Capabilities\RequestContracts;

final class RequestDtoContract
{
    /**
     * @param  array<string, mixed>  $properties
     * @param  list<string>  $required
     */
    public function __construct(
        public readonly string $name,
        public readonly string $description,
        public readonly array $properties = [],
        public readonly array $required = [],
        public readonly ?string $example = null,
    ) {
    }

    public function isRequired(string $field): bool
    {
        return in_array($field, $this->required, true);
    }

    public function hasProperty(string $field): bool
    {
        return array_key_exists($field, $this->properties);
    }

    public function getProperty(string $field): mixed
    {
        return $this->properties[$field] ?? null;
    }
}
