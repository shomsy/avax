<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\System\Capabilities\Messaging\Cqrs;

/**
 * CQRS command side model.
 *
 * @experimental V3 labs
 *
 * Models the write side of a CQRS architecture:
 * commands, command handlers, and write consistency.
 */
final readonly class CommandSide
{
    public function __construct(
        public string $aggregate,
        public bool   $usesOutbox,
        public string $consistencyModel,
        public int    $commandTimeoutMs,
    ) {}

    /**
     * @return array{valid: bool, errors: list<string>}
     */
    public function validate() : array
    {
        $errors = [];

        if ($this->aggregate === '') {
            $errors[] = 'aggregate must not be empty.';
        }

        if ($this->commandTimeoutMs <= 0) {
            $errors[] = 'command_timeout_ms must be positive.';
        }

        return ['valid' => $errors === [], 'errors' => $errors];
    }
}
