<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\PublicSurface;

use Avax\Components\Identity\Access\System\Capabilities\Authorization\AuthorizationEngine;
use Avax\Components\Identity\Access\System\Flows\AdminElevation\BeginAdminElevation;
use Avax\Components\Identity\Access\System\Flows\AdminElevation\EndAdminElevation;
use Avax\Components\Identity\Access\System\Foundation\Exception\PermissionDenied;

/**
 * Access - Main entry point for Identity/Access component.
 */
final readonly class Access implements AccessInterface
{
    public function __construct(
        private AuthorizationEngine $engine,
        private BeginAdminElevation $beginElevation,
        private EndAdminElevation $endElevation,
    ) {}

    public function allows(string $permission, mixed $resource = null) : bool
    {
        return $this->engine->check(permission: $permission, resource: $resource);
    }

    public function denies(string $permission, mixed $resource = null) : bool
    {
        return ! $this->allows(permission: $permission, resource: $resource);
    }

    public function authorize(string $permission, mixed $resource = null) : void
    {
        if ($this->denies(permission: $permission, resource: $resource)) {
            throw new PermissionDenied("Permission denied: {$permission}");
        }
    }

    public function beginElevation() : void
    {
        $this->beginElevation->execute();
    }

    public function endElevation() : void
    {
        $this->endElevation->execute();
    }

    public function isElevated() : bool
    {
        return false; // Implementation
    }
}
