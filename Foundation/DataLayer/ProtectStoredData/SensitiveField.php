<?php

declare(strict_types=1);

namespace Avax\DataLayer\ProtectStoredData;

use InvalidArgumentException;

enum SensitivityLevel: string
{
    case PUBLIC       = 'public';
    case INTERNAL     = 'internal';
    case CONFIDENTIAL = 'confidential';
    case RESTRICTED   = 'restricted';
}

final readonly class SensitiveField
{
    public function __construct(
        public string           $name,
        public SensitivityLevel $level,
        public string           $category,
        public bool             $requiresAudit
    ) {}

    public function describeResponsibility() : string
    {
        return 'records field sensitivity level, category, and audit requirements.';
    }

    public static function password() : self
    {
        return new self('password', SensitivityLevel::RESTRICTED, 'authentication', true);
    }

    public static function ssn() : self
    {
        return new self('ssn', SensitivityLevel::RESTRICTED, 'personally_identifiable', true);
    }

    public static function email() : self
    {
        return new self('email', SensitivityLevel::CONFIDENTIAL, 'contact', false);
    }

    public function isRestricted() : bool
    {
        return $this->level === SensitivityLevel::RESTRICTED;
    }

    public function toMetadata() : array
    {
        return [
            'name'           => $this->name,
            'level'          => $this->level->value,
            'category'       => $this->category,
            'requires_audit' => $this->requiresAudit,
        ];
    }
}