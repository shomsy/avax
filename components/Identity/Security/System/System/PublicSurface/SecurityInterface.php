<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Security\System\System\PublicSurface;

/**
 * SecurityInterface - Enterprise-grade security management contract.
 */
interface SecurityInterface
{
    public function readConfiguration(string $tenantId) : object;

    public function beginChange(string $tenantId, array $data) : object;

    public function approveChange(string $requestId) : void;

    public function applyChange(string $requestId) : void;
}
