<?php

declare(strict_types=1);

namespace Avax\Components\API\Contracts\System\Flows\DeprecateEndpoint;

use Avax\Components\API\Contracts\System\Capabilities\DeprecationTracker\DeprecationTracker;

final class DeprecateEndpoint
{
    /**
     * @return array{deprecated:string,sunset_date:string,replacement:string,reason:string}
     */
    public function execute(DeprecationTracker $tracker, string $method, string $path, string $sunsetDate, string $replacement = '', string $reason = '') : array
    {
        $tracker->deprecate(method: $method, path: $path, sunsetDate: $sunsetDate, replacement: $replacement, reason: $reason);

        return [
            'deprecated'  => "{$method} {$path}",
            'sunset_date' => $sunsetDate,
            'replacement' => $replacement,
            'reason'      => $reason,
        ];
    }
}
