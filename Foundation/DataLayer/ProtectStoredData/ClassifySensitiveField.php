<?php

declare(strict_types=1);

namespace Avax\DataLayer\ProtectStoredData;

enum SensitivityLevel: string
{
    case PUBLIC       = 'public';
    case INTERNAL     = 'internal';
    case CONFIDENTIAL = 'confidential';
    case RESTRICTED   = 'restricted';
}

final readonly class ClassifySensitiveField
{
    public function __construct(
        private SensitivityLevel $defaultLevel
    ) {}

    public function describeResponsibility() : string
    {
        return 'classifies sensitive fields based on name patterns and configuration.';
    }

    public function classify(string $fieldName, array $patterns) : SensitivityLevel
    {
        foreach ($patterns as $pattern => $level) {
            if (stripos($fieldName, $pattern) !== false) {
                return $level;
            }
        }

        return $this->defaultLevel;
    }

    public static function defaultClassification() : self
    {
        return new self(defaultLevel: SensitivityLevel::INTERNAL);
    }

    public function toMetadata() : array
    {
        return ['default_level' => $this->defaultLevel->value];
    }
}