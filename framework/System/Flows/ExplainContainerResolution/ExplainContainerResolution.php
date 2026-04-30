<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\ExplainContainerService;

use Avax\Components\Application\Container\System\ContainerInterface;
use Avax\Framework\System\Capabilities\ContainerIntelligence\ContainerAnalyzer;
use Avax\Framework\System\Capabilities\ContainerIntelligence\ContainerServiceExplanation;

final readonly class ExplainContainerService
{
    public function __construct(
        private ContainerInterface $container,
    ) {}

    public static function printExplanation(ContainerServiceExplanation $explanation) : void
    {
        echo "\033[33mService: {$explanation->serviceId}\033[0m\n";
        echo sprintf("  Scope: %s\n", $explanation->scope);
        echo sprintf("  Shared: %s\n", $explanation->isShared ? 'yes' : 'no');
        echo sprintf("  Lazy: %s\n", $explanation->isLazy ? 'yes' : 'no');
        echo sprintf("  Deferred: %s\n", $explanation->isDeferred ? 'yes' : 'no');
        echo sprintf("  Worker-safe: %s\n", $explanation->workerSafe ? 'yes' : 'no');

        if (! empty($explanation->dependencies)) {
            echo "  Dependencies:\n";
            foreach ($explanation->dependencies as $dep) {
                echo sprintf("    - %s\n", $dep);
            }
        }

        echo "\n";
    }

    public function explain(string $id) : ContainerServiceExplanation
    {
        $analyzer = new ContainerAnalyzer($this->container);

        return $analyzer->why($id);
    }

    /**
     * @return list<string>
     */
    public function whoUses(string $id) : array
    {
        $analyzer = new ContainerAnalyzer($this->container);

        return $analyzer->whoUses($id);
    }

    /**
     * @return list<string>
     */
    public function whatBreaksIf(string $id) : array
    {
        $analyzer = new ContainerAnalyzer($this->container);

        return $analyzer->whatBreaksIf($id);
    }
}
