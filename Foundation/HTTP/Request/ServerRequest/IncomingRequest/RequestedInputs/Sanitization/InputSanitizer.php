<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Sanitization;

/**
 * InputSanitizer - Action owner for cleaning input data.
 */
final readonly class InputSanitizer
{
    public function html(mixed $value) : string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public function js(mixed $value) : string
    {
        return json_encode($value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }

    public function path(mixed $value) : string
    {
        return preg_replace('/[^a-zA-Z0-9\/._-]/', '', (string) $value);
    }

    public function regex(mixed $value) : string
    {
        return preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $value);
    }

    public function withoutControls(mixed $value) : string
    {
        return preg_replace('/[\x00-\x1F\x7F]/', '', (string) $value);
    }

    public function utf8(mixed $value) : string
    {
        return mb_convert_encoding((string) $value, 'UTF-8', 'UTF-8');
    }
}
