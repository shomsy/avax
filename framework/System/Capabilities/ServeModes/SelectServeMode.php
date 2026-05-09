<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\ServeModes;

/**
 * SelectServeMode — Determines which runtime to use for serving.
 */
final class SelectServeMode
{
    public const BUILT_IN = 'built-in';
    public const REACT_PHP = 'reactphp';

    /**
     * @param array<string, string> $options
     */
    public function select(array $options): string
    {
        $runtime = $options['runtime'] ?? self::BUILT_IN;

        if ($runtime === self::REACT_PHP && ! $this->isReactPhpAvailable()) {
            return self::BUILT_IN;
        }

        return match ($runtime) {
            self::REACT_PHP => self::REACT_PHP,
            default => self::BUILT_IN,
        };
    }

    public function isReactPhpAvailable(): bool
    {
        return class_exists(\React\Http\HttpServer::class);
    }
}
