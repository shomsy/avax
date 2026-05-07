<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\System\System\Capabilities\Headers;

final class ResponseHeaders
{
    /** @var ResponseHeader[] */
    private array $headers = [];

    public function add(ResponseHeader $responseHeader) : void
    {
        $this->headers[] = $responseHeader;
    }
}
