<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Security\System\Capabilities\Configuration;

use RuntimeException;
use stdClass;

/**
 * SecurityConfigurationStore - Persistence for security settings.
 */
final class SecurityConfigurationStore
{
    /** @var array<string, array<string, mixed>> */
    private array $configurations = [];

    /** @var array<string, array{tenant_id: string, data: array<string, mixed>, status: string, requested_at: string, approved_at?: string, applied_at?: string}> */
    private array $changes = [];

    /**
     * @param array<string, mixed> $data
     */
    public function begin(string $tenantId, array $data) : stdClass
    {
        $requestId = hash(
            algo: 'sha256',
            data: $tenantId . '|' . serialize(value: $data) . '|' . count(value: $this->changes),
        );

        $this->changes[$requestId] = [
            'tenant_id'    => $tenantId,
            'data'         => $data,
            'status'       => 'pending',
            'requested_at' => gmdate(format: DATE_ATOM),
        ];

        return $this->readChange(requestId: $requestId);
    }

    public function readChange(string $requestId) : stdClass
    {
        $change               = $this->change(requestId: $requestId);
        $change['request_id'] = $requestId;

        return (object) $change;
    }

    /**
     * @return array{tenant_id: string, data: array<string, mixed>, status: string, requested_at: string, approved_at?:
     *                          string, applied_at?: string}
     */
    private function change(string $requestId) : array
    {
        if (! isset($this->changes[$requestId])) {
            throw new RuntimeException(message: 'Security change request was not found: ' . $requestId);
        }

        return $this->changes[$requestId];
    }

    public function approve(string $requestId) : stdClass
    {
        $change = $this->change(requestId: $requestId);

        if ($change['status'] === 'applied') {
            return $this->readChange(requestId: $requestId);
        }

        $change['status']          = 'approved';
        $change['approved_at']     = gmdate(format: DATE_ATOM);
        $this->changes[$requestId] = $change;

        return $this->readChange(requestId: $requestId);
    }

    public function apply(string $requestId) : stdClass
    {
        $change = $this->change(requestId: $requestId);

        if ($change['status'] !== 'approved' && $change['status'] !== 'applied') {
            throw new RuntimeException(message: 'Security change must be approved before it can be applied.');
        }

        if ($change['status'] !== 'applied') {
            $tenantId = $change['tenant_id'];
            $current  = (array) $this->read(tenantId: $tenantId);

            $this->configurations[$tenantId] = array_replace_recursive($current, $change['data']);
            $change['status']                = 'applied';
            $change['applied_at']            = gmdate(format: DATE_ATOM);
            $this->changes[$requestId]       = $change;
        }

        return $this->read(tenantId: $change['tenant_id']);
    }

    public function read(string $tenantId) : stdClass
    {
        return (object) ($this->configurations[$tenantId] ?? [
            'tenant_id'       => $tenantId,
            'mfa_required'    => true,
            'password_policy' => 'enterprise_strict',
        ]);
    }
}
