<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\System\PublicSurface;

/**
 * AccessInterface - Enterprise-grade access control and elevation contract.
 */
interface AccessInterface
{
    public function allows(string $permission, mixed $resource = null) : bool;

    public function denies(string $permission, mixed $resource = null) : bool;

    public function authorize(string $permission, mixed $resource = null) : void;

    public function beginElevation() : void;

    public function endElevation() : void;

    public function isElevated() : bool;
}
