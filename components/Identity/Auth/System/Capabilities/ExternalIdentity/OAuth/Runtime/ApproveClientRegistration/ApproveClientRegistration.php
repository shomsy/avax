<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ApproveClientRegistration;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\OAuthClient;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\OAuthClientRegistryInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use RuntimeException;

final readonly class ApproveClientRegistration
{
    public function __construct(private OAuthClientRegistryInterface $clientRegistry, private AuditLogInterface $auditLog, private Clock $clock)
    {
    }

    public function execute(ApproveClientRegistrationData $data): OAuthClient
    {
        $client = $this->clientRegistry->approve(
            clientId  : $data->clientId,
            approvedBy: $data->approvedBy,
        );

        if ($client === null) {
            throw new RuntimeException(message: 'OAuth client was not found.');
        }

        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.oauth.client.approved',
            occurredAt: $this->clock->now(),
            context   : [
                                                           'client_id'       => $client->clientId,
                                                           'tenant_slug'     => $client->tenantSlug,
                                                           'approved_by'     => $client->approvedBy,
                                                           'approval_status' => $client->approvalStatus->value,
                                                       ],
        ));

        return $client;
    }
}
