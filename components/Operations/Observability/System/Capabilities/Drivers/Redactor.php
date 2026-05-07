<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Observability\System\Capabilities\Drivers;

final readonly class Redactor
{
    /**
     * @param list<string> $sensitiveKeys
     */
    public function __construct(
        private array $sensitiveKeys = ['password', 'secret', 'token', 'api_key', 'authorization'],
    ) {}

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public function redact(array $data) : array
    {
        $result = [];

        foreach ($data as $key => $value) {
            if ($this->isSensitive($key)) {
                $result[$key] = '***REDACTED***';
            } elseif (is_array($value)) {
                $result[$key] = $this->redact($value);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    private function isSensitive(string $key) : bool
    {
        $key = strtolower($key);

        foreach ($this->sensitiveKeys as $sensitive) {
            if (str_contains($key, $sensitive)) {
                return true;
            }
        }

        return false;
    }
}
