<?php
declare(strict_types=1);

namespace Avax\Components\HTTP\Request\System\Capabilities\Body;

final class RequestBody
{
    public function __construct(
        private RawBody $raw,
        private ParsedBody $parsed
    ) {}

    public function raw(): RawBody
    {
        return $this->raw;
    }

    public function parsed(): ParsedBody
    {
        return $this->parsed;
    }
}
