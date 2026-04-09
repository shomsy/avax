<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Mfa;

use DateTimeImmutable;

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
        public array             $codes,
        public DateTimeImmutable $generatedAt
    ) {}

    /**
     * @return list<string>
     */
    public function values() : array
    {
        return array_map(
            static fn (BackupCode $code) : string => $code->value(),
            $this->codes
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function __debugInfo() : array
    {
        return [
            'codes'       => array_map(
                static fn (BackupCode $code) : string => $code->masked(),
                $this->codes
            ),
            'generatedAt' => $this->generatedAt,
        ];
    }
}
