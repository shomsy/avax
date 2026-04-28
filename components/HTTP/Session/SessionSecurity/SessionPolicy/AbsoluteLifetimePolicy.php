<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\SessionSecurity\SessionPolicy;

final class AbsoluteLifetimePolicy
{
    private int $lifetime;

    public function __construct(int $lifetime)
    {
        $this->lifetime = $lifetime;
    }

    public function evaluate(array $context) : bool
    {
        $createdAt = $context['created_at'] ?? 0;

        return (time() - $createdAt) <= $this->lifetime;
    }
}