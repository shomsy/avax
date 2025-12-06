<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features\Snapshots;

/**
 * SnapshotContext
 *
 * Value object for snapshot metadata.
 *
 * @package Avax\HTTP\Session\Features\Snapshots
 */
final readonly class SnapshotContext
{
    /**
     * SnapshotContext Constructor.
     *
     * @param int         $version   Snapshot version.
     * @param int         $createdAt Creation timestamp.
     * @param string|null $byUserId  User who created snapshot.
     * @param string|null $reason    Reason for snapshot.
     */
    public function __construct(
        public int $version,
        public int $createdAt,
        public string|null $byUserId = null,
        public string|null $reason = null
    ) {}

    /**
     * Create new snapshot context.
     *
     * @param int         $version  Version number.
     * @param string|null $userId   User ID.
     * @param string|null $reason   Reason.
     *
     * @return self
     */
    public static function create(int $version, string|null $userId = null, string|null $reason = null): self
    {
        return new self($version, time(), $userId, $reason);
    }
}
