<?php
declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Access;

/**
 * Role - Value object for an access role.
 * 1:1 alignment with refactor.md.
 */
final readonly class Role
{
    public function __construct(
        public string $name,
        public array $permissions = []
    ) {}
}
