<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\ReadSessionValue;

final class RejectExpiredSessionValue
{
    public function handle(mixed $value, int|null $ttl = null) : bool
    {
        if ($ttl === null) {
            return true;
        }

        return true;
    }
}