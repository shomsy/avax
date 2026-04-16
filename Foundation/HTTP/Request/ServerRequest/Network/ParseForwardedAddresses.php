<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\IncomingHttp\Network;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestHeaders\RequestHeaders;
use SensitiveParameter;

/**
 * Action Owner: Parses IP address chains from forwarded headers.
 */
final readonly class ParseForwardedAddresses
{
    /**
     * @return string[]
     */
    public function execute(#[SensitiveParameter] RequestHeaders $headers) : array
    {
        $forwarded = $headers->getLine(name: 'X-Forwarded-For');

        if ($forwarded === '') {
            return [];
        }

        $ips = array_map('trim', explode(',', $forwarded));

        return array_values(array_filter($ips, static fn($ip) => filter_var($ip, FILTER_VALIDATE_IP) !== false));
    }
}
