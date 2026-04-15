<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\IncomingHttp\Network;

use Avax\HTTP\Request\IncomingHttp\IncomingRequest\RequestHeaders\RequestHeaders;

/**
 * Action Owner: Parses IP address chains from forwarded headers.
 */
final readonly class ParseForwardedAddresses
{
    /**
     * @return string[]
     */
    public function execute(#[\SensitiveParameter] RequestHeaders $headers) : array
    {
        $forwarded = $headers->getLine(name: 'X-Forwarded-For');

        if ($forwarded === '') {
            return [];
        }

        return array_map('trim', explode(',', $forwarded));
    }
}
