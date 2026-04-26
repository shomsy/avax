<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\SessionSecurity;

use Throwable;

final class SessionSecurity
{
    /**
     * @var array<int, callable(string, array): bool>
     */
    private array $policies;

    public function __construct(array $policies = [])
    {
        $this->policies = $policies;
    }

    public function verifyPolicy(string $action, array $context = []) : bool
    {
        foreach ($this->policies as $policy) {
            try {
                if ($policy($action, $context) !== true) {
                    return false;
                }
            } catch (Throwable) {
                return false;
            }
        }

        return true;
    }
}
