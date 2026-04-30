<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\ListComponents;

use Avax\Framework\System\Capabilities\ComponentManifest\ComponentDiscovery;
use Avax\Framework\System\Capabilities\ComponentManifest\ComponentManifest;

final readonly class ListComponents
{
    public function __construct(
        private ComponentDiscovery $discovery = new ComponentDiscovery(),
    ) {}

    /**
     * @param array<string, ComponentManifest> $manifests
     */
    public static function printTable(array $manifests) : void
    {
        if (empty($manifests)) {
            echo "No components registered.\n";

            return;
        }

        $width = 100;
        echo str_repeat('=', $width) . "\n";
        echo sprintf("%-25s | %-30s | %-15s | %s\n", 'Name', 'Provides', 'Depends On', 'Resettable');
        echo str_repeat('=', $width) . "\n";

        foreach ($manifests as $manifest) {
            $provides   = implode(', ', array_slice($manifest->provides, 0, 3));
            $depends    = implode(', ', $manifest->dependsOn);
            $resettable = implode(', ', $manifest->resettable);

            if (strlen($provides) > 30) {
                $provides = substr($provides, 0, 27) . '...';
            }

            echo sprintf(
                "%-25s | %-30s | %-15s | %s\n",
                $manifest->name,
                $provides,
                $depends,
                $resettable,
            );
        }

        echo str_repeat('=', $width) . "\n";
        echo "Total: " . count($manifests) . " component(s)\n";
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
     * @return array<string, ComponentManifest>
     */
    public function list() : array
    {
        return $this->discovery->all();
    }
}
