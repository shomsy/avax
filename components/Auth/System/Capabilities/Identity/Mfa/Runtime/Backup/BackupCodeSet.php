<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Backup;

use DateTimeImmutable;
use SensitiveParameter;

/**
 * Plain-text backup codes returned after generation or regeneration.
 *
 * @param list<BackupCode> $codes
 */
final readonly class BackupCodeSet
{
    /**
     * @param list<BackupCode> $codes
     */
    public function __construct(
        #[SensitiveParameter] public array $codes,
        public DateTimeImmutable           $generatedAt
    ) {}

    /**
     * @return list<string>
     */
    public function values() : array
    {
        return array_map(
            callback: static fn (#[SensitiveParameter] BackupCode $code) : string => $code->value(),
            array   : $this->codes
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function __debugInfo() : array
    {
        return [
            'codes'       => array_map(
                callback: static fn (#[SensitiveParameter] BackupCode $code) : string => $code->masked(),
                array   : $this->codes
            ),
            'generatedAt' => $this->generatedAt,
        ];
    }
}
