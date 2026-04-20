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
        $encoded = json_encode($value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

        return $encoded !== false ? $encoded : '';
    }

    public function path(mixed $value) : string
    {
        $result = preg_replace('/[^a-zA-Z0-9\/._-]/', '', (string) $value);

        return is_string($result) ? $result : '';
    }

    public function regex(mixed $value) : string
    {
        $result = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $value);

        return is_string($result) ? $result : '';
    }

    public function withoutControls(mixed $value) : string
    {
        $result = preg_replace('/[\x00-\x1F\x7F]/', '', (string) $value);

        return is_string($result) ? $result : '';
    }

    public function utf8(mixed $value) : string
    {
        $result = mb_convert_encoding((string) $value, 'UTF-8', 'UTF-8');

        return is_string($result) ? $result : '';
    }
}
