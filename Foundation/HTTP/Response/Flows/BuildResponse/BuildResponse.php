<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Flows\BuildResponse;

use Avax\HTTP\Response\Capabilities\Body\NormalizeResponseBody;
use Avax\HTTP\Response\Capabilities\Message\ResponseMessage;
use Psr\Http\Message\ResponseInterface;

/**
 * Canonical response assembly entrypoint.
 */
final class BuildResponse
{
    public function __invoke(
        int|null    $status = null,
        array|null  $headers = null,
        mixed       $body = null,
        string|null $reasonPhrase = null,
        string      $protocolVersion = '1.1',
    ) : ResponseInterface
    {
        $status       ??= 200;
        $headers      ??= [];
        $reasonPhrase ??= '';
        if (in_array(needle: $status, haystack: [204, 304], strict: true)) {
            unset($headers['Content-Type'], $headers['content-type'], $headers['Content-Length'], $headers['content-length']);
            $body = '';
        }

        return new ResponseMessage(
            statusCode     : $status,
            reasonPhrase   : $reasonPhrase,
            protocolVersion: $protocolVersion,
            headers        : $headers,
            body           : new NormalizeResponseBody()($body),
        );
    }
}
