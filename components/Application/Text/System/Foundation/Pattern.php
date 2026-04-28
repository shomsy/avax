<?php

declare(strict_types=1);

namespace Avax\Components\Application\Text\System\Foundation;

/**
 * Regex Pattern primitive.
 */
final readonly class Pattern
{
    private function __construct(private string $raw, private string $flags = '') {}

    public static function of(string $raw, string $flags = '') : self
    {
        return new self($raw, $flags);
    }

    public function test(string $subject) : bool
    {
        return (bool) preg_match($this->getFinalPattern(), $subject);
    }

    private function getFinalPattern() : string
    {
        return "~{$this->raw}~{$this->flags}";
    }
}