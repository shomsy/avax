<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\SessionSecurity\SessionSignature;

use SensitiveParameter;

final class VerifySessionStateSignature
{
    private string $secret;

    public function __construct(#[SensitiveParameter] string $secret)
    {
        $this->secret = $secret;
    }

    public function handle(array $data, string $signature) : bool
    {
        $payload  = json_encode($data);
        $expected = hash_hmac('sha256', $payload, $this->secret);

        return hash_equals($expected, $signature);
    }
}