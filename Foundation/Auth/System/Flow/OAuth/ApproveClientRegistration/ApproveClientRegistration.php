<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\OAuth\ApproveClientRegistration;

use Avax\Auth\System\Capability\OAuth\OAuthClient;
use Avax\Auth\System\Capability\OAuth\OAuthClientRegistryInterface;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Foundation\Clock;
use RuntimeException;

final readonly class ApproveClientRegistration
{
    private Clock                        $clock;
    private AuditLogInterface            $auditLog;
    private OAuthClientRegistryInterface $clientRegistry;

    public function __construct(
        OAuthClientRegistryInterface $clientRegistry,
        AuditLogInterface            $auditLog,
        Clock                        $clock
    )
    {
        $this->clientRegistry = $clientRegistry;
        $this->auditLog       = $auditLog;
        $this->clock          = $clock;
    }

    public function execute(ApproveClientRegistrationData $data) : OAuthClient
    {
        $client = $this->clientRegistry->approve(
            clientId  : $data->clientId,
            approvedBy: $data->approvedBy
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
                                                       ]
                                       ));

        return $client;
    }
}
