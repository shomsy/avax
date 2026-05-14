<?php

declare(strict_types=1);

namespace Avax\Components\Security\Privacy\System\PublicSurface;

use Avax\Components\Security\Privacy\System\Capabilities\DataDeleter\DataDeleter;
use Avax\Components\Security\Privacy\System\Capabilities\DataExporter\DataExporter;
use Avax\Components\Security\Privacy\System\Capabilities\EnforceRetentionPolicy\EnforceRetentionPolicy;
use Avax\Components\Security\Privacy\System\Configuration\PrivacyConfiguration;
use Avax\Components\Security\Privacy\System\Flows\ApplyRetentionPolicy\ApplyRetentionPolicy;
use Avax\Components\Security\Privacy\System\Flows\DeleteUserData\DeleteUserData;
use Avax\Components\Security\Privacy\System\Flows\ExportUserData\ExportUserData;

final class Privacy
{
    public static function exporter() : DataExporter
    {
        return new DataExporter();
    }

    public static function deleter() : DataDeleter
    {
        return new DataDeleter();
    }

    public static function retentionManager(int $retentionDays = 365) : EnforceRetentionPolicy
    {
        return new EnforceRetentionPolicy(retentionDays: $retentionDays);
    }

    /**
     * @param array<string, mixed> $userData
     */
    public static function export(array $userData, string $format = 'json') : string
    {
        return (new ExportUserData())->execute(userData: $userData, format: $format);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public static function delete(array $data, string $userId, bool $anonymize = false) : array
    {
        return (new DeleteUserData())->execute(data: $data, userId: $userId, anonymize: $anonymize);
    }

    /**
     * @param array<string, mixed> $records
     *
     * @return array{expired_count:int,active_count:int,expired:list<array<string,mixed>>,active:list<array<string,mixed>>}
     */
    public static function applyRetention(array $records, int $retentionDays = 365, string $dateField = 'created_at') : array
    {
        return (new ApplyRetentionPolicy(policyManager: new EnforceRetentionPolicy(retentionDays: $retentionDays)))
            ->execute(records: $records, dateField: $dateField);
    }

    public static function isExpired(string $date, int $retentionDays = 365) : bool
    {
        return (new EnforceRetentionPolicy(retentionDays: $retentionDays))->isExpired(date: $date);
    }
}
