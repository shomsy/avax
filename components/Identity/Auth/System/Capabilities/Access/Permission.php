<?php
declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Access;

/**
 * Permission - Value object for an access permission.
 * 1:1 alignment with refactor.md.
 */
final readonly class Permission
{
    public function __construct(
        public string $name
    ) {}
}
