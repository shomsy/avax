<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\ServerRequest\Network;

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

        $ips = array_map(callback: 'trim', array: explode(separator: ',', string: $headerLine));

        return array_filter(array: $ips, callback: static fn ($ip) => filter_var(value: $ip, filter: FILTER_VALIDATE_IP) !== false);
    }
}
