<?php

declare(strict_types=1);

namespace Avax\DataLayer\ProtectStoredData;

final readonly class DescribeRetentionPolicy
{
    public function __construct(
        public RetentionPolicy $policy,
        public string          $description,
        public array           $tiers
    ) {}

    public function describeResponsibility() : string
    {
        return 'describes retention policy including periods, tiers, and archival behavior.';
    }

    public static function standard() : self
    {
        $policy = RetentionPolicy::standard();

        return new self(
            policy     : $policy,
            description: 'Standard retention: 7 years, then archive.',
            tiers      : ['hot' => 90, 'warm' => 365, 'cold' => 2555]
        );
    }

    public function toMetadata() : array
    {
        return [
            'policy'      => $this->policy->toMetadata(),
            'description' => $this->description,
            'tiers'       => $this->tiers,
        ];
    }
}