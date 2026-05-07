<?php

declare(strict_types=1);

namespace Avax\Components\Security\Redaction\System\PublicSurface;

use Avax\Components\Security\Redaction\System\Capabilities\DataClassifier\DataClassifier;
use Avax\Components\Security\Redaction\System\Capabilities\PatternMatcher\PatternMatcher;
use Avax\Components\Security\Redaction\System\Capabilities\PolicyEngine\PolicyEngine;
use Avax\Components\Security\Redaction\System\Capabilities\RedactionEngine\RedactionEngine;
use Avax\Components\Security\Redaction\System\Configuration\RedactionConfiguration;
use Avax\Components\Security\Redaction\System\Flows\ApplyRedactionPolicy\ApplyRedactionPolicy;
use Avax\Components\Security\Redaction\System\Flows\ClassifySensitiveData\ClassifySensitiveData;
use Avax\Components\Security\Redaction\System\Flows\RedactLogData\RedactLogData;

final class Redaction
{
    public static function policyEngine(?RedactionConfiguration $config = null) : PolicyEngine
    {
        return new PolicyEngine();
    }

    public static function classifier() : DataClassifier
    {
        return new DataClassifier();
    }

    public static function patternMatcher() : PatternMatcher
    {
        return new PatternMatcher();
    }

    public static function engine(string $mask = '***') : RedactionEngine
    {
        return new RedactionEngine(mask: $mask);
    }

    /**
     * @param array<string, mixed> $logData
     * @param list<string>         $sensitiveKeys
     *
     * @return array<string, mixed>
     */
    public static function redactLog(array $logData, array $sensitiveKeys = []) : array
    {
        return (new RedactLogData())->execute(logData: $logData, sensitiveKeys: $sensitiveKeys);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array{redacted:array<string,mixed>,detected_patterns:list<string>,was_redacted:bool}
     */
    public static function applyPolicy(array $data) : array
    {
        return (new ApplyRedactionPolicy())->execute(data: $data);
    }

    /**
     * @return list<array{type:string,value:string,confidence:float}>
     */
    public static function classify(string $data) : array
    {
        return (new ClassifySensitiveData())->execute(data: $data);
    }

    public static function isSensitive(string $data) : bool
    {
        return (new DataClassifier())->isSensitive(data: $data);
    }
}
