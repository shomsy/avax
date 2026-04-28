<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\SessionSecurity\SessionSignature;

use SensitiveParameter;

final class SignSessionState
{
    private string $secret;

    public function __construct(#[SensitiveParameter] string $secret)
    {
        $this->secret = $secret;
    }

    public function handle(array $data) : string
    {
        $payload = json_encode($data);

        return hash_hmac('sha256', $payload, $this->secret);
    }
}