<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\ComponentManifest;

/**
 * Describes a component's capabilities, dependencies, and metadata.
 */
final readonly class ComponentManifest
{
    /**
     * @param list<string> $provides
     * @param list<string> $dependsOn
     * @param list<string> $resettable
     * @param list<string> $tags
     */
    public function __construct(
        public string      $name,
        public array       $provides = [],
        public array       $dependsOn = [],
        public array       $resettable = [],
        public string|null $provider = null,
        public array       $tags = [],
        public string|null $description = null,
    ) {}

    public static function make(string $name) : self
    {
        return new self(name: $name);
    }

    public function provides(string ...$classes) : self
    {
        return new self(
            name       : $this->name,
            provides   : [...$this->provides, ...$classes],
            dependsOn  : $this->dependsOn,
            resettable : $this->resettable,
            provider   : $this->provider,
            tags       : $this->tags,
            description: $this->description,
        );
    }

    public function dependsOn(string ...$components) : self
    {
        return new self(
            name       : $this->name,
            provides   : $this->provides,
            dependsOn  : [...$this->dependsOn, ...$components],
            resettable : $this->resettable,
            provider   : $this->provider,
            tags       : $this->tags,
            description: $this->description,
        );
    }

    public function resettable(string ...$classes) : self
    {
        return new self(
            name       : $this->name,
            provides   : $this->provides,
            dependsOn  : $this->dependsOn,
            resettable : [...$this->resettable, ...$classes],
            provider   : $this->provider,
            tags       : $this->tags,
            description: $this->description,
        );
    }

    public function provider(string $class) : self
    {
        return new self(
            name       : $this->name,
            provides   : $this->provides,
            dependsOn  : $this->dependsOn,
            resettable : $this->resettable,
            provider   : $class,
            tags       : $this->tags,
            description: $this->description,
        );
    }

    public function tagged(string ...$tags) : self
    {
        return new self(
            name       : $this->name,
            provides   : $this->provides,
            dependsOn  : $this->dependsOn,
            resettable : $this->resettable,
            provider   : $this->provider,
            tags       : [...$this->tags, ...$tags],
            description: $this->description,
        );
    }

    public function description(string $desc) : self
    {
        return new self(
            name       : $this->name,
            provides   : $this->provides,
            dependsOn  : $this->dependsOn,
            resettable : $this->resettable,
            provider   : $this->provider,
            tags       : $this->tags,
            description: $desc,
        );
    }
}
