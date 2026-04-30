<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\MarkOutage;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ScimFailed;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimDirectory;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimDirectoryStoreInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;

final readonly class MarkScimDirectoryOutage
{
    public function __construct(private ScimDirectoryStoreInterface $directoryStore, private AuditLogInterface $auditLog, private Clock $clock) {}

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
                                                           'tenant'       => $marked->tenantSlug,
                                                           'reason'       => $marked->outageReason,
                                                       ],
                                       ));

        return $marked;
    }
}
