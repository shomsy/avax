<?php

declare(strict_types=1);

namespace Avax\Components\Security\Redaction\System\Capabilities\PatternMatcher;

final class PatternMatcher
{
    public const PATTERN_EMAIL       = '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/';
    public const PATTERN_CREDIT_CARD = '/\b\d{4}[- ]?\d{4}[- ]?\d{4}[- ]?\d{4}\b/';
    public const PATTERN_SSN         = '/\b\d{3}-\d{2}-\d{4}\b/';
    public const PATTERN_API_KEY     = '/\b(?:sk|pk|key)_[a-zA-Z0-9]{20,}\b/';
    public const PATTERN_PASSWORD    = '/password["\']?\s*[:=]\s*["\']?[^"\s,}]+/i';
    public const PATTERN_TOKEN       = '/\b(?:token|bearer|auth)["\']?\s*[:=]\s*["\']?[a-zA-Z0-9._-]+\b/i';
    public const PATTERN_IP          = '/\b(?:\d{1,3}\.){3}\d{1,3}\b/';

    /**
     * @return list<string>
     */
    public function findSensitivePatterns(string $data) : array
    {
        $matches  = [];
        $patterns = [
            'email'       => self::PATTERN_EMAIL,
            'credit_card' => self::PATTERN_CREDIT_CARD,
            'ssn'         => self::PATTERN_SSN,
            'api_key'     => self::PATTERN_API_KEY,
            'password'    => self::PATTERN_PASSWORD,
            'token'       => self::PATTERN_TOKEN,
            'ip_address'  => self::PATTERN_IP,
        ];

        foreach ($patterns as $name => $pattern) {
            if (preg_match(pattern: $pattern, subject: $data)) {
                $matches[] = $name;
            }
        }

        return $matches;
    }
}
