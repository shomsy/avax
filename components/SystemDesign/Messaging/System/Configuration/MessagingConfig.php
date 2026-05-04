<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\Messaging\System\Configuration;

final readonly class MessagingConfig
{
    public function __construct(
        public bool $enabled = true,
    )
    {
    }
}
