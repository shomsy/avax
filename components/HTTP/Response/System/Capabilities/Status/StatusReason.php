<?php
declare(strict_types=1);

namespace Avax\Components\HTTP\Response\System\Capabilities\Status;

final class StatusReason
{
    public function __construct(
        private string $reason
    ) {}

    public function toString(): string
    {
        return $this->reason;
    }
}
