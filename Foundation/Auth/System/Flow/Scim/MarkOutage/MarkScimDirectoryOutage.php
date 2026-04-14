<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Scim\MarkOutage;

use Avax\Auth\System\Capability\Scim\ScimDirectory;
use Avax\Auth\System\Capability\Scim\ScimDirectoryStoreInterface;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Scim\ScimFailed;
use Avax\Auth\System\Foundation\Clock;

final readonly class MarkScimDirectoryOutage
{
    public function __construct(
        private ScimDirectoryStoreInterface $directoryStore,
        private AuditLogInterface $auditLog,
        private Clock $clock
    ) {}

    public function execute(MarkScimDirectoryOutageData $data) : ScimDirectory
    {
        $directory = $this->directoryStore->find(directoryId: $data->directoryId);

        if ($directory === null) {
            throw ScimFailed::unknownDirectory();
        }

        $marked = $directory->markOutage(startedAt: $this->clock->now(), reason: $data->reason);
        $this->directoryStore->save(directory: $marked);
        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.scim.directory.outage_marked',
            occurredAt: $this->clock->now(),
            context   : [
                'directory_id' => $marked->directoryId,
                'tenant' => $marked->tenantSlug,
                'reason' => $marked->outageReason,
            ]
        ));

        return $marked;
    }
}
