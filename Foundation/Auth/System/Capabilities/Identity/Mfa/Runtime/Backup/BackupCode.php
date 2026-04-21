<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Backup;

/**
 * Plain-text backup code shown to the caller once.
 */
final readonly class BackupCode
{
    private string $value;

    public function __construct(
        string $value
    )
    {
        $this->value = $value;
    }

    public function value() : string
    {
        return $this->value;
    }

    /**
     * @return array<string, string>
     */
    public function __debugInfo() : array
    {
        return [
            'value'  => '[REDACTED]',
            'masked' => $this->masked(),
        ];
    }

    public function masked() : string
    {
        $tail = substr($this->value, -4);

        return '****-****-' . $tail;
    }
}
