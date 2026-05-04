<?php

declare(strict_types=1);

namespace Avax\API\System\Capabilities;

interface ApiCapability
{
    public function name(): string;

    public function version(): string;
}