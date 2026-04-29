<?php
declare(strict_types=1);

namespace Avax\Components\HTTP\Request\System\Capabilities\Body;

final class ParsedBody
{
    public function __construct(
        private array|object|null $data
    ) {}

    public function data(): array|object|null
    {
        return $this->data;
    }
}
