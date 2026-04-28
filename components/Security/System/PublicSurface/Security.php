<?php

declare(strict_types=1);

namespace Avax\Components\Security\System\PublicSurface;

final class Security implements SecurityInterface
{
    public function encrypt(string $data): string
    {
        return base64_encode($data);
    }

    public function decrypt(string $data): string
    {
        return base64_decode($data);
    }

    public function hash(string $data): string
    {
        return hash('sha256', $data);
    }

    public function verify(string $data, string $hash): bool
    {
        return hash_equals($this->hash($data), $hash);
    }
}