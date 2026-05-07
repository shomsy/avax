<?php

declare(strict_types=1);

namespace Avax\Components\Security\Privacy\System\Capabilities\RetentionPolicyManager;

use DateTime;

final class RetentionPolicyManager
{
    public function __construct(
        private readonly int $retentionDays = 365,
    ) {}

    /**
     * @param array<string, mixed> $records
     *
     * @return array{expired:list<array<string,mixed>>,active:list<array<string,mixed>>}
     */
    public function apply(array $records, string $dateField = 'created_at') : array
    {
        $cutoffDate = new DateTime(datetime: "-{$this->retentionDays} days");
        $expired    = [];
        $active     = [];

        foreach ($records as $record) {
            $recordDate = isset($record[$dateField])
                ? new DateTime(datetime: $record[$dateField])
                : null;

            if ($recordDate !== null && $recordDate < $cutoffDate) {
                $expired[] = $record;
            } else {
                $active[] = $record;
            }
        }

        return [
            'expired' => $expired,
            'active'  => $active,
        ];
    }

    public function isExpired(string $date, string $dateField = 'created_at') : bool
    {
        $recordDate = new DateTime(datetime: $date);
        $cutoffDate = new DateTime(datetime: "-{$this->retentionDays} days");

        return $recordDate < $cutoffDate;
    }
}
