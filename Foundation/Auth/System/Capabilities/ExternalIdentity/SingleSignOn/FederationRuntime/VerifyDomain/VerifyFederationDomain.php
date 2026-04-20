<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Federation\VerifyDomain;

use Avax\Auth\System\Capabilities\Federation\FederationConnection;
use Avax\Auth\System\Capabilities\Federation\FederationConnectionStoreInterface;
use Avax\Auth\System\Flows\Diagnostics\AuditEvent;
use Avax\Auth\System\Flows\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flows\Federation\FederationFailed;
use Avax\Auth\System\Foundation\Clock;

final readonly class VerifyFederationDomain
{
    private Clock                              $clock;
    private AuditLogInterface                  $auditLog;
    private FederationConnectionStoreInterface $connectionStore;

    public function __construct(
        FederationConnectionStoreInterface $connectionStore,
        AuditLogInterface                  $auditLog,
        Clock                              $clock
    )
    {
        $this->connectionStore = $connectionStore;
        $this->auditLog        = $auditLog;
        $this->clock           = $clock;
    }

    /**
     * @throws FederationFailed
     */
    public function execute(VerifyFederationDomainData $data) : FederationConnection
    {
        $connection = $this->connectionStore->find(connectionId: $data->connectionId);

        if ($connection === null) {
            throw FederationFailed::notFound();
        }

        if (
            $connection->domainVerificationToken === null
            || ! hash_equals($connection->domainVerificationToken, trim($data->verificationToken))
        ) {
            throw FederationFailed::invalidDomainVerificationToken();
        }

        $verified = $connection->withVerifiedDomain(verifiedAt: $this->clock->now());
        $this->connectionStore->save(connection: $verified);
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.federation.domain.verified',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'connection_id' => $verified->connectionId,
                                                           'tenant'        => $verified->tenantSlug,
                                                           'domain'        => $verified->domain,
                                                       ]
                                       ));

        return $verified;
    }
}
