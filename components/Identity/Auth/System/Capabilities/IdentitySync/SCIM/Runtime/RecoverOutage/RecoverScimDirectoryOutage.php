<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RecoverOutage;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\ScimDirectory;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\ScimDirectoryStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ScimFailed;
use Avax\Components\Identity\Auth\System\Foundation\Clock;

final readonly class RecoverScimDirectoryOutage
{
    public function __construct(private ScimDirectoryStoreInterface $scimDirectoryStore, private AuditLogInterface $auditLog, private Clock $clock)
    {
    }

    public function execute(RecoverScimDirectoryOutageData $recoverScimDirectoryOutageData): ScimDirectory
    {
        $directory = $this->scimDirectoryStore->find(directoryId: $recoverScimDirectoryOutageData->directoryId);

        if (! $directory instanceof ScimDirectory) {
            throw ScimFailed::unknownDirectory();
        }

        $scimDirectory = $directory->recover(recoveredAt: $this->clock->now());
        $this->scimDirectoryStore->save(directory: $scimDirectory);
        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.scim.directory.outage_recovered',
            occurredAt: $this->clock->now(),
            context   : [
                'directory_id' => $scimDirectory->directoryId,
                'tenant' => $scimDirectory->tenantSlug,
            ],
        ));

        return $scimDirectory;
    }
}
