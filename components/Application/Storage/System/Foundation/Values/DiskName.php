<?php

declare(strict_types=1);

namespace Avax\Components\Application\Storage\System\Foundation\Values;

final readonly class DiskName
{
    public function __construct(
        public string $name,
    ) {}

    public function isValid() : bool
    {
        return $this->name !== '' && preg_match('/^[a-zA-Z0-9_-]+$/', $this->name) === 1;
    }
}