<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\System\Capabilities;

/**
 * Permission - Value object for an access permission.
 * 1:1 alignment with refactor.md.
 */
final readonly class Permission
{
    public function __construct(
        public string $name,
    ) {}
}
