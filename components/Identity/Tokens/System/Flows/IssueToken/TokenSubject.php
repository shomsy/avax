<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\Flows\IssueToken;

/**
 * Value object representing the subject of a token to be issued.
 */
final readonly class TokenSubject
{
    public function __construct(
        public string $userId,
        /** @var array<string, mixed> */
        public array $claims = [],
    ) {}
}
