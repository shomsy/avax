<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Capabilities\Caching;

use DateTimeInterface;

final readonly class LastModified
{
    public function __construct(
        public DateTimeInterface $value,
    ) {}

    public function toHeaderValue() : string
    {
        return gmdate(format: 'D, d M Y H:i:s', timestamp: $this->value->getTimestamp()) . ' GMT';
    }
}
