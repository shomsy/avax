<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features\Decorators;

/**
 * CompositeSessionDecorator
 *
 * Composes multiple decorators into a chain.
 *
 * @package Avax\HTTP\Session\Features\Decorators
 */
final class CompositeSessionDecorator implements SessionDecoratorInterface
{
    /**
     * Registered decorators.
     *
     * @var array<SessionDecoratorInterface>
     */
    private array $decorators = [];

    /**
     * Add a decorator to the chain.
     *
     * @param SessionDecoratorInterface $decorator The decorator.
     *
     * @return self
     */
    public function add(SessionDecoratorInterface $decorator): self
    {
        $this->decorators[] = $decorator;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, mixed $value): void
    {
        foreach ($this->decorators as $decorator) {
            $decorator->set($key, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $result = $default;

        foreach ($this->decorators as $decorator) {
            $result = $decorator->get($key, $result);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $key): void
    {
        foreach ($this->decorators as $decorator) {
            $decorator->delete($key);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function flush(): void
    {
        foreach ($this->decorators as $decorator) {
            $decorator->flush();
        }
    }
}
