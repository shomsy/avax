<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\System\Capabilities\Status;

final readonly class StatusCode
{
    public function __construct(
        private int $code,
    ) {
    }

    public function value(): int
    {
        return $this->code;
    }
}
