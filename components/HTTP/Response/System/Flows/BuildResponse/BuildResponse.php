<?php
declare(strict_types=1);

namespace Avax\Components\HTTP\Response\System\Flows\BuildResponse;

use Avax\Components\HTTP\Response\System\PublicSurface\Response;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;

final class BuildResponse
{
    public function __construct(
        private NormalizeResponseBody $bodyNormalizer,
        private NormalizeResponseHeaders $headerNormalizer
    ) {}

    public function execute(mixed $content, int $status = 200, array $headers = []): ResponseInterface
    {
        return new Response(
            statusCode: $status,
            headers: $this->headerNormalizer->normalize($headers),
            body: $this->bodyNormalizer->normalize($content)
        );
    }
}
