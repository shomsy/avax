<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\ExplainConfig;

use Avax\Framework\System\Capabilities\ConfigExplanation\ConfigExplainer;
use Avax\Framework\System\Capabilities\ConfigExplanation\ConfigSource;

final readonly class ExplainConfig
{
    public function __construct(
        private ConfigExplainer $configExplainer,
    ) {
    }

    /**
     * @param  array<string, ConfigSource>  $sources
     */
    public static function printSources(array $sources, bool $safe = false): void
    {
        foreach ($sources as $key => $source) {
            $value = $safe ? self::redact($key, $source->value) : $source->value;

            echo sprintf(
                "%s = %s (source: %s)\n",
                $key,
                var_export($value, true),
                $source->source,
            );

            if ($source->envVar !== null) {
                echo sprintf("  overridden by: %s env\n", $source->envVar);
            }

            if ($source->filePath !== null) {
                echo sprintf("  source: %s\n", $source->filePath);
            }
        }
    }

    private static function redact(string $key, mixed $value): mixed
    {
        $secretPatterns = ['password', 'secret', 'key', 'token', 'api_key', 'apikey'];

        foreach ($secretPatterns as $secretPattern) {
            if (stripos($key, $secretPattern) !== false) {
                return is_string($value) && $value !== ''
                    ? substr($value, 0, 3).'***'.substr($value, -2)
                    : $value;
            }
        }

        return $value;
    }

    public function explain(string $key): string
    {
        $source = $this->configExplainer->getSource($key);

        if (! $source instanceof ConfigSource) {
            return sprintf("Config key '%s' not found in source registry.", $key);
        }

        $lines = [
            'Config: '.$key,
            sprintf('  Value: %s', var_export($source->value, true)),
            sprintf('  Source: %s', $source->source),
        ];

        if ($source->isFromEnv() && $source->envVar !== null) {
            $lines[] = sprintf('  Environment variable: %s', $source->envVar);
        }

        if ($source->isFromFile() && $source->filePath !== null) {
            $lines[] = sprintf('  File: %s', $source->filePath);
        }

        return implode("\n", $lines);
    }

    /**
     * @return array<string, ConfigSource>
     */
    public function allSources(): array
    {
        return $this->configExplainer->allSources();
    }
}
