<?php

declare(strict_types=1);

namespace Avax\API\Contracts\System\Capabilities\RequestContracts;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;

interface RequestDtoContract
{
    public function validate(RequestInterface $request): bool;

    public function getValidatedData(RequestInterface $request): ?array;

    public function schema(): array;
}