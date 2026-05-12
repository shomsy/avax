<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Events\System\Flows\CompileEventListeners;

use Avax\Components\Operations\Events\System\Capabilities\Registry\ListenerRegistry;
use Avax\Components\Operations\Events\System\Foundation\CompiledListener;
use Avax\Components\Operations\Events\System\Foundation\CompiledListenerRegistry;
use Avax\Components\Operations\Events\System\Foundation\ListensTo;
use Avax\Components\Operations\Events\System\Foundation\ListenerExecutionMode;
use Avax\Components\Operations\Events\System\Foundation\ListenerSource;

/**
 * Compiles DSL registrations and #[ListensTo] attribute declarations
 * into one canonical CompiledListenerRegistry.
 *
 * This is a boot-time operation. Runtime dispatch does not use reflection.
 *
 * In-memory only for V5.7. Disk persistence is ROADMAP.
 */
final class CompileEventListeners
{
    public function __construct(
        private ListenerRegistry $registry,
    ) {
    }

    /**
     * Compile all known listener declarations into a frozen CompiledListenerRegistry.
     *
     * @param list<class-string> $listenerClasses Explicit listener classes to scan for #[ListensTo].
     */
    public function execute(array $listenerClasses = []): CompiledListenerRegistry
    {
        $compiled = new CompiledListenerRegistry();

        // Compile DSL registrations from the runtime ListenerRegistry.
        $this->compileDslRegistrations($compiled);

        // Compile #[ListensTo] attribute declarations.
        $this->compileAttributeDeclarations($compiled, $listenerClasses);

        $compiled->freeze();

        return $compiled;
    }

    /**
     * Compile DSL registrations from ListenerRegistry into CompiledListenerRegistry.
     */
    private function compileDslRegistrations(CompiledListenerRegistry $compiled): void
    {
        foreach ($this->registry->getAllEvents() as $eventClass) {
            $listeners = $this->registry->getListenersFor($eventClass);
            $order = 0;

            foreach ($listeners as $listener) {
                $compiledListener = new CompiledListener(
                    eventClass: $eventClass,
                    listener: $listener,
                    priority: 0,
                    source: ListenerSource::Dsl,
                    mode: ListenerExecutionMode::Sync,
                    order: $order++,
                );

                $compiled->add($compiledListener);
            }
        }
    }

    /**
     * Compile #[ListensTo] attribute declarations into CompiledListenerRegistry.
     *
     * @param list<class-string> $listenerClasses
     */
    private function compileAttributeDeclarations(CompiledListenerRegistry $compiled, array $listenerClasses): void
    {
        $order = 0;

        foreach ($listenerClasses as $class) {
            $reflection = new \ReflectionClass($class);
            $attributes = $reflection->getAttributes(ListensTo::class);

            foreach ($attributes as $attribute) {
                $listensTo = $attribute->newInstance();

                $compiledListener = new CompiledListener(
                    eventClass: $listensTo->eventClass,
                    listener: $class,
                    priority: $listensTo->priority,
                    source: ListenerSource::Attribute,
                    mode: ListenerExecutionMode::Sync,
                    order: $order++,
                );

                $compiled->add($compiledListener);
            }
        }
    }
}
