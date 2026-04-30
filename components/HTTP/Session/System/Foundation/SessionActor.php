<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\Foundation;

use SensitiveParameter;

final readonly class SessionActor
{
    public function __construct(
        public string $id,
        #[SensitiveParameter]
        public array  $data = [],
    ) {}
}
