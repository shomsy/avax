<?php

declare(strict_types=1);

namespace Avax\API\Contracts\System\PublicSurface;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;

interface ApiContract
{
    public function path(): string;

    public function method(): string;

    public function summary(): string;

    public function version(): string;

    public function isDeprecated(): bool;

    public function requiredAuthentication(): bool;

    public function requiredPermissions(): array;

    public function requestSchema(): ?RequestDtoContract;

    public function responseSchema(): ?ResponseDtoContract;
}