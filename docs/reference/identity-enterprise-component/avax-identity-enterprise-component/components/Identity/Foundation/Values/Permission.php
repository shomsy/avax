<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Foundation\Values;

use Avax\Components\Identity\Foundation\Failures\InvalidIdentityValue;

final readonly class Permission
{
    private function __construct(private string $action, private string $resource) {}

    public static function for(string $action, string $resource): self
    {
        $action = trim($action);
        $resource = trim($resource);
        if ($action === '' || $resource === '') {
            throw InvalidIdentityValue::because('Permission action and resource must be present.');
        }

        return new self($action, $resource);
    }

    public function key(): string
    {
        return $this->action.':'.$this->resource;
    }

    public function action(): string
    {
        return $this->action;
    }

    public function resource(): string
    {
        return $this->resource;
    }
}
