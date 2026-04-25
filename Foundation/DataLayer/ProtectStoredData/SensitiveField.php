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
        return new self(name: 'password', level: SensitivityLevel::RESTRICTED, category: 'authentication', requiresAudit: true);
    }

    public static function ssn() : self
    {
        return new self(name: 'ssn', level: SensitivityLevel::RESTRICTED, category: 'personally_identifiable', requiresAudit: true);
    }

    public static function email() : self
    {
        return new self(name: 'email', level: SensitivityLevel::CONFIDENTIAL, category: 'contact', requiresAudit: false);
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