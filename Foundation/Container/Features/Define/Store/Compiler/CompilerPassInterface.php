<?php

declare(strict_types=1);
namespace Avax\Container\Features\Define\Store\Compiler;

use Avax\Container\Features\Define\Store\DefinitionStore;

/**
 * Interface for container compiler passes.
 *
 * @see docs/Features/Define/Store/Compiler/CompilerPassInterface.md#quick-summary
 */
interface CompilerPassInterface
{
    /**
     * Process the definition store.
     *
     * @param DefinitionStore $definitions Definition registry to process.
     * @return void
     * @see docs/Features/Define/Store/Compiler/CompilerPassInterface.md#method-process
     */
    public function process(DefinitionStore $definitions) : void;
}
