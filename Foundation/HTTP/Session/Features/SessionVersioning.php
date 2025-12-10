<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features;

use DateTimeImmutable;

/**
 * SessionVersioning
 *
 * Manages versioned session snapshots for rollback and audit recovery.
 *
 * @package Avax\HTTP\Session\Features
 */
final class SessionVersioning
{
    private array $versions = [];

    /**
     * @throws \Random\RandomException
     */
    public function createSnapshot(array $data) : string
    {
        $versionId                  = bin2hex(random_bytes(8));
        $this->versions[$versionId] = [
            'timestamp' => new DateTimeImmutable(),
            'data'      => $data
        ];

        return $versionId;
    }

    public function restoreSnapshot(string $versionId) : array|null
    {
        return $this->versions[$versionId]['data'] ?? null;
    }

    public function listVersions() : array
    {
        return array_map(
            fn($v) => $v['timestamp']->format(DATE_ATOM),
            $this->versions
        );
    }
}
