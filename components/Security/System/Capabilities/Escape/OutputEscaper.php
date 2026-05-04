<?php

declare(strict_types=1);

namespace Avax\Components\Security\System\Capabilities\Escape;

final readonly class OutputEscaper
{
    public static function attribute(string $value): string
    {
        return self::html(value: $value);
    }

    public static function html(string $value): string
    {
        return htmlspecialchars(string: $value, flags: ENT_QUOTES | ENT_SUBSTITUTE, encoding: 'UTF-8');
    }

    public static function json(mixed $value): string
    {
        return json_encode(value: $value, flags: JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR);
    }
}
