<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Federation\VerifyDomain;

use Avax\Auth\System\Capability\Federation\FederationConnection;
use Avax\Auth\System\Capability\Federation\FederationConnectionStoreInterface;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Federation\FederationFailed;
use Avax\Auth\System\Foundation\Clock;

final readonly class VerifyFederationDomain
{
    public function __construct(
        private FederationConnectionStoreInterface $connectionStore,
        private AuditLogInterface $auditLog,
        private Clock $clock
    ) {}

    /**
     * @throws FederationFailed
     */
    public function execute(VerifyFederationDomainData $data) : FederationConnection
    {
        $connection = $this->connectionStore->find($data->connectionId);

        if ($connection === null) {
            throw FederationFailed::notFound();
        }

        if (
            $connection->domainVerificationToken === null
            || ! hash_equals($connection->domainVerificationToken, trim($data->verificationToken))
        ) {
            throw FederationFailed::invalidDomainVerificationToken();
        }

        $verified = $connection->withVerifiedDomain($this->clock->now());
        $this->connectionStore->save($verified);
        $this->auditLog->record(new AuditEvent(
            name      : 'auth.federation.domain.verified',
            occurredAt: $this->clock->now(),
            context   : [
                'connection_id' => $verified->connectionId,
                'tenant' => $verified->tenantSlug,
                'domain' => $verified->domain,
            ]
        ));

        return $verified;
    }
}
