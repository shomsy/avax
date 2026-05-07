<?php

declare(strict_types=1);

namespace Avax\Components\Security\Secrets\System\Foundation\Failure;

final class SecretNotFound extends SecretException
{
    public function __construct(string $key, ?string $message = null)
    {
        $message ??= "Secret not found: {$key}";
        parent::__construct($message, 404);
    }
}
