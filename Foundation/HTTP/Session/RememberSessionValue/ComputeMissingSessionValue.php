<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\RememberSessionValue;

final class ComputeMissingSessionValue
{
    public function handle(callable $callback) : mixed
    {
        return $callback();
    }
}