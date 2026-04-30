<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\DiscoverComponents;

use Avax\Framework\System\Capabilities\ComponentManifest\ComponentDiscovery;

final readonly class DiscoverComponents
{
    public function __construct(
        private ComponentDiscovery $discovery = new ComponentDiscovery(),
    ) {}

    /**
     * @param list<string> $missingDeps
     */
    public static function printReport(array $manifests, array $missingDeps) : int
    {
        echo "\033[33mComponent Discovery Report\033[0m\n\n";

        if (empty($manifests)) {
            echo "No component manifests found.\n";

            return 0;
        }

        echo sprintf("Found: %d component(s)\n\n", count($manifests));

        foreach ($missingDeps as $missing) {
            echo sprintf("\033[31m[MISSING] %s\033[0m\n", $missing);
        }

        if (empty($missingDeps)) {
            echo "\033[32mAll dependencies satisfied.\033[0m\n";
        }

        return empty($missingDeps) ? 0 : 1;
    }

    /**
     * @param list<string> $searchPaths
     */
    public function search(array $searchPaths) : self
    {
        $this->discovery->discover($searchPaths);

        return $this;
    }

    /**
     * @return list<string>
     */
    public function detectMissingDeps() : array
    {
        return $this->discovery->detectMissingDependencies();
    }
}
