<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\ChangeEmail;

use DateTimeImmutable;
use SensitiveParameter;

final readonly class EmailChangeChallenge
{
    public DateTimeImmutable|null $expiresAt;
    public string|null            $token;
    public bool                   $dispatched;

    public function __construct(
        bool                              $dispatched,
        #[SensitiveParameter] string|null $token = null,
        DateTimeImmutable|null            $expiresAt = null
    )
    {
        $this->dispatched = $dispatched;
        $this->token      = $token;
        $this->expiresAt  = $expiresAt;
    }
}
