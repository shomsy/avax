<?php

declare(strict_types=1);

namespace Avax\Components\Security\Redaction\System\Flows\ApplyRedactionPolicy;

use Avax\Components\Security\Redaction\System\Capabilities\PatternMatcher\PatternMatcher;
use Avax\Components\Security\Redaction\System\Capabilities\PolicyEngine\PolicyEngine;

final readonly class ApplyRedactionPolicy
{
    public function __construct(
        private PolicyEngine   $policyEngine = new PolicyEngine(),
        private PatternMatcher $patternMatcher = new PatternMatcher(),
    ) {}

    /**
     * @param array<string, mixed> $data
     *
     * @return array{redacted:array<string,mixed>,detected_patterns:list<string>,was_redacted:bool}
     */
    public function execute(array $data) : array
    {
        $serialized = json_encode(value: $data, flags: JSON_THROW_ON_ERROR);
        $patterns   = $this->patternMatcher->findSensitivePatterns(data: $serialized);
        $redacted   = $this->policyEngine->apply(data: $data);

        return [
            'redacted'          => $redacted,
            'detected_patterns' => $patterns,
            'was_redacted'      => ! empty($patterns),
        ];
    }
}
