<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\SessionEvents;

final class SessionEvent
{
    public function __construct(
        public readonly string $name,
        public readonly array  $data,
        public readonly int    $timestamp
    ) {}

    public static function create(string $name, array $data = []) : self
    {
        return new self(
            name     : $name,
            data     : $data,
            timestamp: time()
        );
    }
}