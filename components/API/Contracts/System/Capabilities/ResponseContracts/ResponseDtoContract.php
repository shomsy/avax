<?php

declare(strict_types=1);

namespace Avax\API\Contracts\System\Capabilities\ResponseContracts;

use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;

interface ResponseDtoContract
{
    public function validate(ResponseInterface $response): bool;

    public function schema(): array;

    public function statusCode(): int;
}