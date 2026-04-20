<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Scim\MarkOutage;

use Avax\Auth\System\Capabilities\Scim\ScimDirectory;
use Avax\Auth\System\Capabilities\Scim\ScimDirectoryStoreInterface;
use Avax\Auth\System\Flows\Diagnostics\AuditEvent;
use Avax\Auth\System\Flows\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flows\Scim\ScimFailed;
use Avax\Auth\System\Foundation\Clock;

final readonly class MarkScimDirectoryOutage
{
    private Clock                       $clock;
    private AuditLogInterface           $auditLog;
    private ScimDirectoryStoreInterface $directoryStore;

    public function __construct(
        ScimDirectoryStoreInterface $directoryStore,
        AuditLogInterface           $auditLog,
        Clock                       $clock
    )
    {
        $this->directoryStore = $directoryStore;
        $this->auditLog       = $auditLog;
        $this->clock          = $clock;
    }

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
                                                       ]
                                       ));

        return $marked;
    }
}
