<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\SessionAudit;

final class MaskSensitiveSessionAuditFields
{
    private array $fields = ['password', 'token', 'secret', 'api_key'];

    public function handle(array $data) : array
    {
        foreach ($this->fields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '***MASKED***';
            }
        }

        return $data;
    }
}