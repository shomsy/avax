<?php

declare(strict_types=1);

namespace Avax\Components\Security\Redaction\System\Capabilities\PolicyEngine;

use Avax\Components\Security\Redaction\System\Capabilities\DataClassifier\DataClassifier;
use Avax\Components\Security\Redaction\System\Capabilities\PatternMatcher\PatternMatcher;
use Avax\Components\Security\Redaction\System\Capabilities\RedactionEngine\RedactionEngine;

final class PolicyEngine
{
    public function __construct(
        private readonly DataClassifier  $classifier = new DataClassifier(),
        private readonly RedactionEngine $engine = new RedactionEngine(),
    )
    {
        $this->registerDefaultPatterns();
    }

    private function registerDefaultPatterns() : void
    {
        $this->engine
            ->addPattern(pattern: PatternMatcher::PATTERN_PASSWORD, replacement: 'password": "***')
            ->addPattern(pattern: PatternMatcher::PATTERN_TOKEN, replacement: 'token": "***')
            ->addPattern(pattern: PatternMatcher::PATTERN_API_KEY, replacement: '***')
            ->addPattern(pattern: PatternMatcher::PATTERN_CREDIT_CARD, replacement: '****-****-****-****')
            ->addPattern(pattern: PatternMatcher::PATTERN_SSN, replacement: '***-**-****')
            ->addPattern(pattern: PatternMatcher::PATTERN_EMAIL, replacement: '***@***.***');
    }

    /**
     * @param array<string, mixed> $data
     * @param list<string>         $sensitiveKeys
     *
     * @return array<string, mixed>
     */
    public function apply(array $data, array $sensitiveKeys = ['password', 'secret', 'token', 'key', 'authorization']) : array
    {
        return $this->redactByKeys(data: $data, keys: $sensitiveKeys);
    }

    /**
     * @param array<string, mixed> $data
     * @param list<string>         $keys
     *
     * @return array<string, mixed>
     */
    private function redactByKeys(array $data, array $keys) : array
    {
        $result = [];

        foreach ($data as $key => $value) {
            if (in_array(needle: strtolower(string: $key), haystack: $keys, strict: true)) {
                $result[$key] = '***';
            } elseif (is_array(value: $value)) {
                $result[$key] = $this->redactByKeys(data: $value, keys: $keys);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
