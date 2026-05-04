<?php

declare(strict_types=1);

namespace Avax\API\Contracts\System\Capabilities\AuthContracts;

interface RequiredAuthentication
{
    public function isRequired(): bool;

    public function authenticationType(): string;
}