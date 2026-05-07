<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\AfterResponse\System\Flows\RegisterAfterResponseCallback;

final readonly class RegisterAfterResponseCallback
{
    /**
     * @param list<callable> $callbacks
     *
     * @return list<callable>
     */
    public function register(callable $callback, array $callbacks) : array
    {
        $callbacks[] = $callback;

        return $callbacks;
    }
}
