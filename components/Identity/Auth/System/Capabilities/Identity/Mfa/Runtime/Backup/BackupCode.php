<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Backup;

/**
 * Plain-text backup code shown to the caller once.
 */
final readonly class BackupCode
{
    public function __construct(private string $value)
    {
    }

    public function value(): string
    {
        return $this->value;
    }

    /**
     * @return array<string, string>
     */
    public function __debugInfo(): array
    {
        return [
            'value'  => '[REDACTED]',
            'masked' => $this->masked(),
        ];
    }

    public function masked(): string
    {
        $tail = substr(string: $this->value, offset: -4);

        return '****-****-' . $tail;
    }
}
