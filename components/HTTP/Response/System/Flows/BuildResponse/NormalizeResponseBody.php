<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\System\Flows\BuildResponse;

use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\StreamInterface;

final class NormalizeResponseBody
{
    public function normalize(mixed $content): StreamInterface
    {
        return Utils::streamFor((string) $content);
    }
}
