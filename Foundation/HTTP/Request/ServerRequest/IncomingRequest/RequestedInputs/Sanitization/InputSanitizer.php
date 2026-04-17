<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Sanitization;

use Normalizer;
use Stringable;

/**
 * InputSanitizer
 *
 * Stateless sanitizer / escaper helper for requested input values.
 *
 * This is not a value object.
 * This is a small service with context-specific output-safe transformations.
 */
final class InputSanitizer
{
    public function sanitizeHtml(mixed $value) : string
    {
        $string = $this->toString(value: $value);

        if ($string === null) {
            return '';
        }

        return htmlspecialchars(
            $string,
            ENT_QUOTES | ENT_HTML5,
            'UTF-8',
        );
    }

    private function toString(mixed $value) : string|null
    {
        if (is_scalar($value) || $value instanceof Stringable) {
            return (string) $value;
        }

        return null;
    }

    public function sanitizeJs(mixed $value) : string
    {
        $string = $this->toString(value: $value);

        if ($string === null) {
            return '';
        }

        $string = addcslashes($string, "\\'\"\\\\\n\r\t\v\f\$`");

        return preg_replace('/[\x00-\x1F\x7F]/u', '', $string) ?? '';
    }

    public function sanitizePath(mixed $value) : string
    {
        $string = $this->toString(value: $value);

        if ($string === null) {
            return '';
        }

        $string = str_replace("\0", '', $string);
        $string = preg_replace('/^[a-zA-Z]:/', '', $string) ?? '';
        $string = str_replace('\\', '/', $string);

        $segments = [];

        foreach (explode('/', $string) as $segment) {
            $segment = trim($segment);

            if ($segment === '' || $segment === '.' || $segment === '..') {
                continue;
            }

            $segments[] = $segment;
        }

        return implode('/', $segments);
    }

    public function sanitizeRegex(mixed $value) : string
    {
        $string = $this->toString(value: $value);

        if ($string === null) {
            return '';
        }

        return preg_quote($string, '/');
    }

    public function stripControls(mixed $value) : string
    {
        $string = $this->toString(value: $value);

        if ($string === null) {
            return '';
        }

        return preg_replace('/[\x00-\x1F\x7F]/u', '', $string) ?? '';
    }

    public function normalizeUtf8(mixed $value) : string
    {
        $string = $this->toString(value: $value);

        if ($string === null) {
            return '';
        }

        $string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');
        $string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $string) ?? '';

        if (class_exists(Normalizer::class)) {
            $normalized = Normalizer::normalize($string, form: Normalizer::FORM_C);

            if ($normalized !== false) {
                $string = $normalized;
            }
        }

        return $string;
    }
}
