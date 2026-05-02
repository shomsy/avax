<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\Foundation;

final readonly class SessionEvent
{
    public function __construct(
        public string $name,
        public array $data,
        public int $timestamp,
    ) {}

    public static function create(string $name, array $data = []) : self
    {
        return new self($name, $data, time());
    }
}
