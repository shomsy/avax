<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\SessionSecurity\SessionEncryption;

final class DecryptSessionValue
{
    private $encrypter;

    public function __construct($encrypter)
    {
        $this->encrypter = $encrypter;
    }

    public function handle(string $data) : mixed
    {
        return $this->encrypter->decrypt($data);
    }
}