<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\SessionSecurity\SessionFingerprint;

use SensitiveParameter;

final class SessionFingerprint
{
    public function __construct(
        private string                       $userAgent,
        #[SensitiveParameter] private string $ipAddress,
        private string                       $acceptLanguage
    ) {}

    public static function generate() : self
    {
        return new self(
            userAgent     : $_SERVER['HTTP_USER_AGENT'] ?? '',
            ipAddress     : $_SERVER['REMOTE_ADDR'] ?? '',
            acceptLanguage: $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''
        );
    }

    public function userAgent() : string
    {
        return $this->userAgent;
    }

    public function ipAddress() : string
    {
        return $this->ipAddress;
    }

    public function acceptLanguage() : string
    {
        return $this->acceptLanguage;
    }

    public function hash() : string
    {
        return hash('sha256', $this->userAgent . $this->ipAddress . $this->acceptLanguage);
    }
}