<?php

declare(strict_types=1);

namespace Avax\Components\Security\Redaction\System\Capabilities\DataClassifier;

final class DataClassifier
{
    public const SENSITIVE_EMAIL       = 'email';
    public const SENSITIVE_PHONE       = 'phone';
    public const SENSITIVE_SSN         = 'ssn';
    public const SENSITIVE_CREDIT_CARD = 'credit_card';
    public const SENSITIVE_API_KEY     = 'api_key';
    public const SENSITIVE_PASSWORD    = 'password';
    public const SENSITIVE_TOKEN       = 'token';
    public const SENSITIVE_IP          = 'ip_address';

    public function isSensitive(string $data) : bool
    {
        return ! empty($this->classify(data: $data));
    }

    /**
     * @return list<array{type:string,value:string,confidence:float}>
     */
    public function classify(string $data) : array
    {
        $results = [];

        if ($this->isEmail(data: $data)) {
            $results[] = ['type' => self::SENSITIVE_EMAIL, 'value' => $data, 'confidence' => 1.0];
        }
        if ($this->isPhone(data: $data)) {
            $results[] = ['type' => self::SENSITIVE_PHONE, 'value' => $data, 'confidence' => 0.9];
        }
        if ($this->isCreditCard(data: $data)) {
            $results[] = ['type' => self::SENSITIVE_CREDIT_CARD, 'value' => $data, 'confidence' => 0.95];
        }
        if ($this->isSsn(data: $data)) {
            $results[] = ['type' => self::SENSITIVE_SSN, 'value' => $data, 'confidence' => 0.95];
        }
        if ($this->isApiKey(data: $data)) {
            $results[] = ['type' => self::SENSITIVE_API_KEY, 'value' => $data, 'confidence' => 0.8];
        }
        if ($this->isIp(data: $data)) {
            $results[] = ['type' => self::SENSITIVE_IP, 'value' => $data, 'confidence' => 0.9];
        }

        return $results;
    }

    private function isEmail(string $data) : bool
    {
        return (bool) filter_var(value: $data, filter: FILTER_VALIDATE_EMAIL);
    }

    private function isPhone(string $data) : bool
    {
        return (bool) preg_match(pattern: '/^\+?\d{7,15}$/', subject: $data);
    }

    private function isCreditCard(string $data) : bool
    {
        return (bool) preg_match(pattern: '/^\d{13,19}$/', subject: $data);
    }

    private function isSsn(string $data) : bool
    {
        return (bool) preg_match(pattern: '/^\d{3}-?\d{2}-?\d{4}$/', subject: $data);
    }

    private function isApiKey(string $data) : bool
    {
        return str_contains(haystack: $data, needle: 'key_') || str_contains(haystack: $data, needle: 'sk_') || str_contains(haystack: $data, needle: 'pk_');
    }

    private function isIp(string $data) : bool
    {
        return (bool) filter_var(value: $data, filter: FILTER_VALIDATE_IP);
    }
}
