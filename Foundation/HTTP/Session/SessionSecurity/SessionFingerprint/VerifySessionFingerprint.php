<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\SessionSecurity\SessionFingerprint;

final class VerifySessionFingerprint
{
    public function handle(string $expected, string $actual) : bool
    {
        return hash_equals($expected, $actual);
    }
}