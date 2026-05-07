<?php

declare(strict_types=1);

namespace Avax\Components\API\Contracts\System\Flows\ValidateApiContract;

use Avax\Components\API\Contracts\System\Capabilities\EndpointRegistry\EndpointRegistry;

final readonly class ValidateApiContract
{
    /**
     * @return array{valid:bool,errors:list<string>,count:int}
     */
    public function execute(EndpointRegistry $registry) : array
    {
        $endpoints = $registry->all();
        $errors    = [];

        foreach ($endpoints as $endpoint) {
            if ($endpoint['version'] === '') {
                $errors[] = "Endpoint {$endpoint['method']} {$endpoint['path']} has no version";
            }
            if (! preg_match(pattern: '/^\d+\.\d+\.\d+$/', subject: $endpoint['version'])) {
                $errors[] = "Endpoint {$endpoint['method']} {$endpoint['path']} has invalid version format: {$endpoint['version']}";
            }
        }

        return [
            'valid'  => empty($errors),
            'errors' => $errors,
            'count'  => count(value: $endpoints),
        ];
    }
}
