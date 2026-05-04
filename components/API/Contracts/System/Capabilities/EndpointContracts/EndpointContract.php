<?php

declare(strict_types=1);

namespace Avax\API\Contracts\System\Capabilities\EndpointContracts;

interface EndpointContract
{
    public function path(): string;

    public function method(): string;

    public function summary(): string;

    public function version(): string;

    public function isDeprecated(): bool;
}