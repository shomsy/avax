<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\RememberSessionValue;

final class ComputeMissingSessionValue
{
    public function handle(callable $callback) : mixed
    {
        return $callback();
    }
}