<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\SessionSecurity\SessionSignature;

final class SignSessionState
{
    private string $secret;

    public function __construct(string $secret)
    {
        $this->secret = $secret;
    }

    public function handle(array $data) : string
    {
        $payload = json_encode($data);

        return hash_hmac('sha256', $payload, $this->secret);
    }
}