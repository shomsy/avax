<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Security;

use Avax\HTTP\Session\Contracts\Security\ServerContext;

/**
 * NativeServerContext - PHP Native Server Context
 *
 * Default implementation using PHP's $_SERVER superglobal.
 * 
 * @package Avax\HTTP\Session\Security
 */
final class NativeServerContext implements ServerContext
{
    /**
     * {@inheritdoc}
     */
    public function getUserAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    /**
     * {@inheritdoc}
     */
    public function isSecure(): bool
    {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    }

    /**
     * {@inheritdoc}
     */
    public function getClientIp(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
