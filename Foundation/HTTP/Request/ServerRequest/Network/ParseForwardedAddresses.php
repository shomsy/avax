<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\Network;

use SensitiveParameter;

/**
 * ParseForwardedAddresses - Action owner for extracting IP addresses from proxy headers.
 */
final readonly class ParseForwardedAddresses
{
    /**
     * @return string[]
     */
    public function execute(#[SensitiveParameter] string $headerLine) : array
    {
        if ($headerLine === '') {
            return [];
        }

        $ips = array_map('trim', explode(',', $headerLine));
        
        return array_filter($ips, static fn($ip) => filter_var($ip, FILTER_VALIDATE_IP) !== false);
    }
}
