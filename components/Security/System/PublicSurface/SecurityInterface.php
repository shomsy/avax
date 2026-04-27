<?php

declare(strict_types=1);

namespace Avax\Components\Security\System\PublicSurface;

interface SecurityInterface
{
    public function encrypt(string $data): string;

    public function decrypt(string $data): string;

    public function hash(string $data): string;

    public function verify(string $data, string $hash): bool;
}