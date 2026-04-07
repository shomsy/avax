<?php

declare(strict_types=1);

namespace Avax\Container\Compilation;

abstract class CompiledContainer
{
    protected string $fingerprint = '';

    /** @var array<string, string> */
    protected array $entries = [];

    public function fingerprint() : string
    {
        return $this->fingerprint;
    }

    public function has(string $serviceId) : bool
    {
        return isset($this->entries[$serviceId]);
    }

    public function methodFor(string $serviceId) : string|null
    {
        return $this->entries[$serviceId] ?? null;
    }
}
