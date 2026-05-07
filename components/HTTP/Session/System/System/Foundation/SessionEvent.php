<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\System\Foundation;

final readonly class SessionEvent
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        public string $name,
        public array  $data,
        public int    $timestamp,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function create(string $name, array $data = []) : self
    {
        return new self($name, $data, time());
    }
}
