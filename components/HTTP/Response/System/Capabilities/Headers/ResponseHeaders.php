<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\System\Capabilities\Headers;

final class ResponseHeaders
{
    /** @var ResponseHeader[] */
    private array $headers = [];

    public function add(ResponseHeader $header) : void
    {
        $this->headers[] = $header;
    }
}
