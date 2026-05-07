<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\ContentNegotiation\System\Configuration;

/**
 * @phpstan-type DefaultTypes = list<string>
 */
final readonly class ContentNegotiationConfiguration
{
    /**
     * @param DefaultTypes $defaultTypes
     */
    public function __construct(
        public array  $defaultTypes = ['application/json', 'text/html'],
        public string $fallbackType = 'application/json',
    ) {}
}
