<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\SessionSecurity\SessionEncryption;

final class EncryptSessionValue
{
    private $encrypter;

    public function __construct($encrypter)
    {
        $this->encrypter = $encrypter;
    }

    public function handle(mixed $value) : string
    {
        return $this->encrypter->encrypt($value);
    }
}