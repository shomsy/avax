<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Sanitization;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\RequestedInputs;

/**
 * SanitizedRequestedInputs - Proxy for RequestedInputs that applies sanitization.
 */
final readonly class SanitizedRequestedInputs
{
    public function __construct(
        private RequestedInputs $inputs,
        private InputSanitizer  $sanitizer,
    ) {}

    public function html(string $key, string $default = '') : string
    {
        return $this->sanitizer->html(value: $this->inputs->get(key: $key, default: $default));
    }

    public function js(string $key, string $default = '') : string
    {
        return $this->sanitizer->js(value: $this->inputs->get(key: $key, default: $default));
    }

    public function path(string $key, string $default = '') : string
    {
        return $this->sanitizer->path(value: $this->inputs->get(key: $key, default: $default));
    }

    public function regex(string $key, string $default = '') : string
    {
        return $this->sanitizer->regex(value: $this->inputs->get(key: $key, default: $default));
    }

    public function withoutControls(string $key, string $default = '') : string
    {
        return $this->sanitizer->withoutControls(value: $this->inputs->get(key: $key, default: $default));
    }

    public function utf8(string $key, string $default = '') : string
    {
        return $this->sanitizer->utf8(value: $this->inputs->get(key: $key, default: $default));
    }

    public function string(string $key, string $default = '') : string
    {
        return $this->withoutControls(key: $key, default: $default);
    }
}
