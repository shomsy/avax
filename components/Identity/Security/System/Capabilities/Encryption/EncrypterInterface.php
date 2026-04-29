<?php
declare(strict_types=1);

namespace Avax\Components\Identity\Security\System\Capabilities\Encryption;

interface EncrypterInterface
{
    public function encrypt(mixed $value): string;
    public function decrypt(string $payload): mixed;
}
