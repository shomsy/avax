<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\System\Flows\CreateJsonResponse;

use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;
use Avax\Components\HTTP\System\Flows\BuildResponse\BuildResponse;

final class CreateJsonResponse
{
    public function execute(mixed $data, int $status = 200) : ResponseInterface
    {
        return BuildResponse::execute(
            $status,
            ['Content-Type' => 'application/json'],
            json_encode($data) ?: '{}'
        );
    }
}
