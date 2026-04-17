<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Sanitization;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\RequestedInputs;

/**
 * SanitizedRequestedInputs
 *
 * Context-aware sanitized view over RequestedInputs.
 *
 * This keeps sanitization logic out of the raw data containers,
 * while still giving a pleasant DX.
 */
final readonly class SanitizedRequestedInputs
{
    public function __construct(
        private RequestedInputs $inputs,
        private InputSanitizer|null $sanitizer = null,
    ) {}

    public function html(string $key, string $default = '') : string
    {
        return $this->sanitizer->sanitizeHtml(value: $this->valueOrDefault(key: $key, default: $default));
    }

    private function valueOrDefault(string $key, mixed $default = null) : mixed
    {
        return $this->inputs->has(key: $key)
            ? $this->inputs->get(key: $key)
            : $default;
    }

    public function js(string $key, string $default = '') : string
    {
        return $this->sanitizer->sanitizeJs(value: $this->valueOrDefault(key: $key, default: $default));
    }

    public function path(string $key, string $default = '') : string
    {
        return $this->sanitizer->sanitizePath(value: $this->valueOrDefault(key: $key, default: $default));
    }

    public function regex(string $key, string $default = '') : string
    {
        return $this->sanitizer->sanitizeRegex(value: $this->valueOrDefault(key: $key, default: $default));
    }

    public function withoutControls(string $key, string $default = '') : string
    {
        return $this->sanitizer->stripControls(value: $this->valueOrDefault(key: $key, default: $default));
    }

    public function utf8(string $key, string $default = '') : string
    {
        return $this->sanitizer->normalizeUtf8(value: $this->valueOrDefault(key: $key, default: $default));
    }
}
