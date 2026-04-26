<?php

declare(strict_types=1);

namespace components\HTTP\Session\SessionRecovery;

final class SessionStateChecksum
{
    public function verify(string $expected, array $data) : bool
    {
        return hash_equals($expected, $this->compute(data: $data));
    }

    public function compute(array $data) : string
    {
        return hash('sha256', serialize($data));
    }
}