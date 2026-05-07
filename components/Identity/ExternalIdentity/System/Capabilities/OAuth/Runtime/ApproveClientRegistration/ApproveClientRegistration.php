<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\ApproveClientRegistration;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\OAuthClient;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\OAuthClientRegistryInterface;
use RuntimeException;

final readonly class ApproveClientRegistration
{
    public function __construct(private OAuthClientRegistryInterface $oAuthClientRegistry, private AuditLogInterface $auditLog, private Clock $clock) {}

    public function execute(ApproveClientRegistrationData $approveClientRegistrationData) : OAuthClient
    {
        $client = $this->oAuthClientRegistry->approve(
            clientId  : $approveClientRegistrationData->clientId,
            approvedBy: $approveClientRegistrationData->approvedBy,
        );

        if (! $client instanceof OAuthClient) {
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
