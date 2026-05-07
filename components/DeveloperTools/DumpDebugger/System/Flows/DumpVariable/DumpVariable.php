<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\DumpDebugger\System\Flows\DumpVariable;

final readonly class DumpVariable
{
    public function dump(mixed $value, string $format = 'html') : string
    {
        return match ($format) {
            'cli'   => $this->toCli($value),
            default => $this->toHtml($value),
        };
    }

    private function toCli(mixed $value) : string
    {
        return print_r($value, true);
    }

    private function toHtml(mixed $value) : string
    {
        return '<pre>' . htmlspecialchars(print_r($value, true)) . '</pre>';
    }
}
