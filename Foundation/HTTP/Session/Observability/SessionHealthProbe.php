<?php
declare(strict_types=1);

namespace Avax\Foundation\HTTP\Session\Observability;

final class SessionHealthProbe
{
    public function __construct(private string $sessionPath) {}

    public function isHealthy() : bool
    {
        return is_dir($this->sessionPath) && is_writable($this->sessionPath);
    }
}
