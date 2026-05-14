<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\MarkOutage;

use Avax\Components\Identity\Auth\System\Capabilities\AuthDiagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\AuthDiagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\ScimDirectory;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\ScimDirectoryStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ScimFailed;
use Avax\Components\Identity\Auth\System\Foundation\Clock;

final readonly class MarkScimDirectoryOutage
{
    public function __construct(private ScimDirectoryStoreInterface $scimDirectoryStore, private AuditLogInterface $auditLog, private Clock $clock) {}

    public function execute(MarkScimDirectoryOutageData $markScimDirectoryOutageData) : ScimDirectory
    {
        $directory = $this->scimDirectoryStore->find(directoryId: $markScimDirectoryOutageData->directoryId);

        if (! $directory instanceof ScimDirectory) {
            throw ScimFailed::unknownDirectory();
        }

        $scimDirectory = $directory->markOutage(startedAt: $this->clock->now(), reason: $markScimDirectoryOutageData->reason);
        $this->scimDirectoryStore->save(directory: $scimDirectory);
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.scim.directory.outage_marked',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'directory_id' => $scimDirectory->directoryId,
                                                           'tenant'       => $scimDirectory->tenantSlug,
                                                           'reason'       => $scimDirectory->outageReason,
                                                       ],
                                       ));

        return $scimDirectory;
    }
}
