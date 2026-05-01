<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Capabilities\RequirePhishingResistantAuthentication;

use RuntimeException;

final class PhishingResistantAuthenticationRequired extends RuntimeException
{
    public function __construct(
        string $message = 'Phishing-resistant authentication is required.',
    ) {
        parent::__construct(message: $message, code: 403);
    }
}
