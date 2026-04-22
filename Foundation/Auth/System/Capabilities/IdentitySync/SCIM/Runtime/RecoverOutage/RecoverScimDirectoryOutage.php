<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RecoverOutage;

use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ScimFailed;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimDirectory;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimDirectoryStoreInterface;
use Avax\Auth\System\Foundation\Clock;

final readonly class RecoverScimDirectoryOutage
{
    public function __construct(private ScimDirectoryStoreInterface $directoryStore, private AuditLogInterface $auditLog, private Clock $clock)
    {
    }

    public function execute(RecoverScimDirectoryOutageData $data) : ScimDirectory
    {
        $directory = $this->directoryStore->find(directoryId: $data->directoryId);

        if ($directory === null) {
            throw ScimFailed::unknownDirectory();
        }

        $recovered = $directory->recover(recoveredAt: $this->clock->now());
        $this->directoryStore->save(directory: $recovered);
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.scim.directory.outage_recovered',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'directory_id' => $recovered->directoryId,
                                                           'tenant'       => $recovered->tenantSlug,
                                                       ]
                                       ));

        return $recovered;
    }
}
