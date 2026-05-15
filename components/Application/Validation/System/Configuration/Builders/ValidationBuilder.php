<?php

declare(strict_types=1);

namespace Avax\Components\Application\Validation\System\Configuration\Builders;

final class ValidationBuilder
{
    private array $rules = [];

    public function addRule(string $field, array $rules): self
    {
        $this->rules[$field] = $rules;

        return $this;
    }

    public function getRules(): array
    {
        return $this->rules;
    }
}
