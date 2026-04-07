<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Registrations;

use Avax\Container\DependencyInjection\Scopes\Lifetimes\TransientLifetime;

/**
 * One stored service registration.
 */
final class ServiceRegistration
{
    public mixed $concrete = null;

    public string $lifetime = TransientLifetime::NAME;

    /** @var list<string> */
    public array $tags = [];

    /** @var array<string, mixed> */
    public array $arguments = [];

    public function __construct(
        public readonly string $abstract
    ) {}

    public function to(string|callable|null $concrete) : self
    {
        $this->concrete = $concrete;

        return $this;
    }

    public function tag(string|array $tags) : self
    {
        $this->tags = array_values(array_unique(array_merge($this->tags, (array) $tags)));

        return $this;
    }

    /**
     * @param array<string, mixed> $arguments
     */
    public function withArguments(array $arguments) : self
    {
        $this->arguments = array_merge($this->arguments, $arguments);

        return $this;
    }

    public function withArgument(string $name, mixed $value) : self
    {
        return $this->withArguments(arguments: [$name => $value]);
    }

    public static function __set_state(array $array) : self
    {
        $registration = new self(abstract: $array['abstract']);
        $registration->concrete = $array['concrete'] ?? null;
        $registration->lifetime = $array['lifetime'] ?? TransientLifetime::NAME;
        $registration->tags = $array['tags'] ?? [];
        $registration->arguments = $array['arguments'] ?? [];

        return $registration;
    }
}
