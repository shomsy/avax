<?php

declare(strict_types=1);

namespace Avax\Components\Security\Redaction\System\Capabilities\RedactionEngine;

final class RedactionEngine
{
    /** @var list<array{pattern:string,replacement:string}> */
    private array $rules = [];

    public function __construct(
        private readonly string $mask = '***',
    ) {}

    public function addPattern(string $pattern, string $replacement = '') : self
    {
        $this->rules[] = [
            'pattern'     => $pattern,
            'replacement' => $replacement === '' ? $this->mask : $replacement,
        ];

        return $this;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public function redactArray(array $data) : array
    {
        $result = [];

        foreach ($data as $key => $value) {
            if (is_array(value: $value)) {
                $result[$key] = $this->redactArray(data: $value);
            } elseif (is_string(value: $value)) {
                $result[$key] = $this->redact(data: $value);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    public function redact(string $data) : string
    {
        $result = $data;

        foreach ($this->rules as $rule) {
            $replaced = preg_replace(
                pattern    : $rule['pattern'],
                replacement: $rule['replacement'],
                subject    : $result,
            );
            if ($replaced !== null) {
                $result = $replaced;
            }
        }

        return $result;
    }
}
