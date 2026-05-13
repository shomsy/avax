<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Routing;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Framework\System\Capabilities\Routing\Types\RouteCacheFailed;
use Closure;

/**
 * RegisterRouteCommands — provides CLI command closures for route:cache and route:clear.
 */
final readonly class RegisterRouteCommands
{
    /**
     * @return array<string, Closure>
     */
    public function __invoke(): array
    {
        return [
            'route:cache' => $this->routeCacheCommand(),
            'route:clear' => $this->routeClearCommand(),
        ];
    }

    private function routeCacheCommand(): Closure
    {
        return static function (array $args): string {
            $output = "\033[33mRoute Cache\033[0m\n\n";

            // Proof slice: create a minimal route table to demonstrate the mechanism
            // Full route table compilation requires V4-05+ integration
            $routeTable = [
                'GET /' => ['handler' => 'root', 'middleware' => []],
                'GET /health' => ['handler' => 'health', 'middleware' => []],
            ];

            try {
                $cacheRouteTable = new CacheRouteTable(new Filesystem());
                $cacheFile = $cacheRouteTable->write(routeTable: $routeTable);

                $output .= "Route table compiled: ".count($routeTable)." routes\n";
                $output .= "Cache written to: {$cacheFile}\n";
                $output .= "\n\033[32mRoute cache generated.\033[0m\n";
            } catch (RouteCacheFailed $e) {
                $output .= "\033[31mRoute cache failed: ".$e->getMessage()."\033[0m\n";
            }

            return $output;
        };
    }

    private function routeClearCommand(): Closure
    {
        return static function (array $args): string {
            $output = "\033[33mRoute Clear\033[0m\n\n";

            try {
                $loadCachedRoutes = new LoadCachedRoutes(new Filesystem());

                if (!$loadCachedRoutes->exists()) {
                    $output .= "No route cache found to clear\n";

                    return $output;
                }

                $loadCachedRoutes->clear();
                $output .= "\033[32mRoute cache cleared.\033[0m\n";
            } catch (RouteCacheFailed $e) {
                $output .= "\033[31mRoute clear failed: ".$e->getMessage()."\033[0m\n";
            }

            return $output;
        };
    }
}
