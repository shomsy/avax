<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\Configuration;

use Avax\Components\HTTP\Session\SessionStore\SessionStore;

final class BuildSession
{
    private SessionConfig $config;

    public function __construct(SessionConfig $config)
    {
        $this->config = $config;
    }

    public function build(SessionStore $store) : array
    {
        $components = [
            'store' => $store,
        ];

        if ($this->config->encrypt || $this->config->encryptionKey !== null) {
            $components['security'] = $this->buildSecurity();
        }

        return $components;
    }

    private function buildSecurity() : array
    {
        return [];
    }
}