<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Flows\CreateResponse;

use Avax\Components\HTTP\Response\System\Capabilities\CreateHttpResponse\CreateHttpResponse;
use Avax\Components\HTTP\Response\System\PublicSurface\Response;

/**
 * CreateResponse — static convenience shortcuts for common HTTP responses.
 *
 * Delegates to the canonical CreateHttpResponse capability.
 */
final class CreateResponse
{
    public static function json(mixed $data, int $status = 200, array $headers = []) : Response
    {
        return (new CreateHttpResponse())->json(data: $data, status: $status, headers: $headers);
    }

    public static function html(string $html, int $status = 200, array $headers = []) : Response
    {
        return (new CreateHttpResponse())->html(body: $html, status: $status, headers: $headers);
    }

    public static function redirect(string $url, int $status = 302, array $headers = []) : Response
    {
        return (new CreateHttpResponse())->redirect(url: $url, status: $status, headers: $headers);
    }

    public static function plain(string $text, int $status = 200, array $headers = []) : Response
    {
        return (new CreateHttpResponse())->text(body: $text, status: $status, headers: $headers);
    }
}
