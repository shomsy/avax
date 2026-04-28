<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\VerifyDomain;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\FederationFailed;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationConnection;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationConnectionStoreInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;

final readonly class VerifyFederationDomain
{
    public function __construct(private FederationConnectionStoreInterface $connectionStore, private AuditLogInterface $auditLog, private Clock $clock) {}

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
            || ! hash_equals(known_string: $connection->domainVerificationToken, user_string: trim(string: $data->verificationToken))
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
