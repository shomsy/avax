<?php

declare(strict_types=1);

namespace Avax\Components\Application\Validation\System\Configuration\Builders;

final class ValidationBuilder
{
    /** @var array<string, array<int, string>> */
    private array $rules = [];

    /**
     * @param array<int, string> $rules
     */
    public function addRule(string $field, array $rules): self
    {
        $this->rules[$field] = $rules;

        return $this;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function getRules(): array
    {
        return $this->rules;
    }
}
