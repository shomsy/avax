<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Scim\RecoverOutage;

use Avax\Auth\System\Capability\Scim\ScimDirectory;
use Avax\Auth\System\Capability\Scim\ScimDirectoryStoreInterface;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Scim\ScimFailed;
use Avax\Auth\System\Foundation\Clock;

final readonly class RecoverScimDirectoryOutage
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
