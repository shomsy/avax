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
        return $this->sanitizer->html($this->inputs->get($key, $default));
    }

    public function js(string $key, string $default = '') : string
    {
        return $this->sanitizer->js($this->inputs->get($key, $default));
    }

    public function path(string $key, string $default = '') : string
    {
        return $this->sanitizer->path($this->inputs->get($key, $default));
    }

    public function regex(string $key, string $default = '') : string
    {
        return $this->sanitizer->regex($this->inputs->get($key, $default));
    }

    public function withoutControls(string $key, string $default = '') : string
    {
        return $this->sanitizer->withoutControls($this->inputs->get($key, $default));
    }

    public function utf8(string $key, string $default = '') : string
    {
        return $this->sanitizer->utf8($this->inputs->get($key, $default));
    }

    public function string(string $key, string $default = '') : string
    {
        return $this->withoutControls($key, $default);
    }
}
