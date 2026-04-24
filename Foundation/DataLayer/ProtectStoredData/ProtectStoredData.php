<?php

declare(strict_types=1);

namespace Avax\DataLayer\ProtectStoredData;

use InvalidArgumentException;

final readonly class ProtectStoredData
{
    public function enforceTenantBoundary(TenantBoundary $boundary, string $column, mixed $tenantId) : string
    {
        if ($boundary->isRequired && $tenantId === null) {
            throw new InvalidArgumentException(
                sprintf('Tenant ID is required for boundary %s', $boundary->id)
            );
        }

        return $boundary->toWhereClause($tenantId);
    }

    public function classifySensitiveField(string $fieldName) : ?SensitiveField
    {
        $normalized = strtolower($fieldName);

        $mappings = [
            'password'        => SensitiveField::PASSWORD,
            'secret'          => SensitiveField::SECRET,
            'token'           => SensitiveField::TOKEN,
            'api_key'         => SensitiveField::API_KEY,
            'api_key'         => SensitiveField::API_KEY,
            'credit_card'     => SensitiveField::CREDIT_CARD,
            'cc_number'       => SensitiveField::CREDIT_CARD,
            'ssn'             => SensitiveField::SSN,
            'social_security' => SensitiveField::SSN,
            'email'           => SensitiveField::EMAIL,
            'phone'           => SensitiveField::PHONE,
            'address'         => SensitiveField::ADDRESS,
            'date_of_birth'   => SensitiveField::DATE_OF_BIRTH,
            'dob'             => SensitiveField::DATE_OF_BIRTH,
            'personal_id'     => SensitiveField::PERSONAL_ID,
        ];

        return $mappings[$normalized] ?? null;
    }

    public function maskSensitiveData(mixed $value, SensitiveField $field) : string
    {
        if ($value === null) {
            return 'NULL';
        }

        $mask = $field->maskPattern();

        if ($field === SensitiveField::EMAIL && is_string($value)) {
            return $this->maskEmail($value);
        }

        if ($field === SensitiveField::PHONE && is_string($value)) {
            return $this->maskPhone($value);
        }

        return $mask;
    }

    public function maskEmail(string $email) : string
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return '****';
        }

        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return '****';
        }

        $local  = $parts[0];
        $domain = $parts[1];

        $maskedLocal = strlen($local) > 2
            ? $local[0] . str_repeat('*', strlen($local) - 2) . $local[-1]
            : str_repeat('*', strlen($local));

        $domainParts  = explode('.', $domain);
        $maskedDomain = count($domainParts) > 1
            ? $domainParts[0][0] . str_repeat('*', strlen($domainParts[0]) - 1) . '.' . implode('.', array_slice($domainParts, 1))
            : $domain;

        return sprintf('%s@%s', $maskedLocal, $maskedDomain);
    }

    public function maskPhone(string $phone) : string
    {
        $digits = preg_replace('/\D/', '', $phone);

        if (strlen($digits) < 4) {
            return '***-***-****';
        }

        return sprintf('***-***-%s', substr($digits, -4));
    }

    public function recordAuditEntry(
        string  $action,
        string  $table,
        ?string $recordId,
        array   $oldValues,
        array   $newValues,
        string  $actorId,
        array   $options = []
    ) : DataAuditEntry
    {
        return DataAuditEntry::create(
            action   : $action,
            table    : $table,
            recordId : $recordId,
            oldValues: $this->filterSensitiveValues($oldValues),
            newValues: $this->filterSensitiveValues($newValues),
            actorId  : $actorId,
            options  : $options
        );
    }

    public function filterSensitiveValues(array $values) : array
    {
        $filtered = [];

        foreach ($values as $key => $value) {
            $sensitive = $this->classifySensitiveField($key);

            if ($sensitive !== null) {
                $filtered[$key] = $this->maskSensitiveData($value, $sensitive);
            } else {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    public function requireDataAccessPolicy(DataAccessPolicy $policy, string $operation) : void
    {
        if (! $policy->isOperationAllowed($operation)) {
            throw new InvalidArgumentException(
                sprintf('Operation %s is not allowed by policy %s', $operation, $policy->name)
            );
        }
    }
}