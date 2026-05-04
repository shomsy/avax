<?php

declare(strict_types=1);

namespace Avax\API\Contracts\System\Capabilities\AuthContracts;

interface RequiredPermission
{
    public function permission(): string;

    public function isRequired(): bool;
}