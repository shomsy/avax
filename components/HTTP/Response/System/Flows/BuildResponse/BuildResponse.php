<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\System\Flows\BuildResponse;

use Avax\Components\HTTP\Response\System\PublicSurface\Response;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;

final readonly class BuildResponse
{
    public function __construct(
        private NormalizeResponseBody    $normalizeResponseBody,
        private NormalizeResponseHeaders $normalizeResponseHeaders,
    ) {}

    public function execute(mixed $content, int $status = 200, array $headers = []) : ResponseInterface
    {
        return new Response(
            statusCode: $status,
            headers   : $this->normalizeResponseHeaders->normalize($headers),
            body      : $this->normalizeResponseBody->normalize($content),
        );
    }
}
