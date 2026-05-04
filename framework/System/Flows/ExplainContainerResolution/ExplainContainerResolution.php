<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\ExplainContainerResolution;

use Avax\Components\Application\Container\System\ContainerInterface;
use Avax\Framework\System\Capabilities\ContainerIntelligence\ContainerAnalyzer;
use Avax\Framework\System\Capabilities\ContainerIntelligence\ContainerDependencyExplanation;

final readonly class ExplainContainerResolution
{
    public function __construct(
        private ContainerInterface $container,
    ) {
    }

    public static function printExplanation(ContainerDependencyExplanation $containerDependencyExplanation) : void
    {
        echo "\033[33mService: {$containerDependencyExplanation->serviceId}\033[0m\n";
        echo sprintf("  Scope: %s\n", $containerDependencyExplanation->scope);
        echo sprintf("  Shared: %s\n", $containerDependencyExplanation->isShared ? 'yes' : 'no');
        echo sprintf("  Lazy: %s\n", $containerDependencyExplanation->isLazy ? 'yes' : 'no');
        echo sprintf("  Deferred: %s\n", $containerDependencyExplanation->isDeferred ? 'yes' : 'no');
        echo sprintf("  Worker-safe: %s\n", $containerDependencyExplanation->workerSafe ? 'yes' : 'no');

        if ($containerDependencyExplanation->dependencies !== []) {
            echo "  Dependencies:\n";
            foreach ($containerDependencyExplanation->dependencies as $dep) {
                echo sprintf("    - %s\n", $dep);
            }
        }

        echo "\n";
    }

    public function explain(string $id): ContainerDependencyExplanation
    {
        $containerAnalyzer = new ContainerAnalyzer($this->container);

        return $containerAnalyzer->why($id);
    }

    /**
     * @return list<string>
     */
    public function whoUses() : array
    {
        $containerAnalyzer = new ContainerAnalyzer($this->container);

        return $containerAnalyzer->whoUses();
    }

    /**
     * @return list<string>
     */
    public function whatBreaksIf() : array
    {
        $containerAnalyzer = new ContainerAnalyzer($this->container);

        return $containerAnalyzer->whatBreaksIf();
    }
}
