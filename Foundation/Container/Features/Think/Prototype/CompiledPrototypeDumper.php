<?php

declare(strict_types=1);

namespace Avax\Container\Features\Think\Prototype;

use Avax\Container\Features\Define\Store\DefinitionStore;
use Avax\Container\Features\Define\Store\ServiceDefinition;

/**
 * Ahead-of-time (AOT) compiler for container service blueprints.
 *
 * The CompiledPrototypeDumper is responsible for transforming the "Living"
 * container configuration into a "Cold", static PHP file. This process, known
 * as "Dumping" or "Compiling", is a critical performance optimization for
 * production environments. By converting definitions into a static PHP array,
 * the container can bypass the entire "Think" phase (reflection and analysis)
 * at runtime, loading the pre-calculated plans instantly via PHP's opcache.
 *
 * @see     docs/Features/Think/Prototype/CompiledPrototypeDumper.md
 */
final readonly class CompiledPrototypeDumper
{
    /**
     * Initializes the dumper with the source of truth for definitions.
     *
     * @param DefinitionStore $definitions The store containing all current service rules.
     */
    public function __construct(
        private DefinitionStore $definitions
    ) {}

    /**
     * Generate a production-ready PHP payload string of all definitions.
     *
     * This method:
     * 1. Extracts every {@see ServiceDefinition} from the store.
     * 2. Converts them into raw arrays via `toArray()`.
     * 3. Wraps them in a metadata envelope (including a timestamp).
     * 4. Uses `var_export` to create a valid PHP file string.
     *
     * @return string Valid PHP code that returns the compiled definition array.
     *
     * @see docs/Features/Think/Prototype/CompiledPrototypeDumper.md#method-dump
     */
    public function dump() : string
    {
        // 1. Transform definitions into serializable primitives
        $definitions = array_map(
            static fn(ServiceDefinition $definition) : array => $definition->toArray(),
            $this->definitions->getAllDefinitions()
        );

        // 2. Prepare the metadata envelope
        $payload = [
            'generated_at' => time(),
            'definitions'  => $definitions,
        ];

        // 3. Export to a high-speed, opcache-friendly PHP script
        return "<?php\n\nreturn " . var_export(value: $payload, return: true) . ";\n";
    }
}
