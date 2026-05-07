<?php

declare(strict_types=1);

namespace Avax\Components\API\Surface\System\Capabilities\JsonApi;

final class BuildCompoundDocument
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private array $resources = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    private array $included = [];

    /**
     * @var array<string, true>
     */
    private array $includedKeys = [];

    /**
     * @param list<array<string, mixed>> $resources
     */
    public function primaries(array $resources) : self
    {
        foreach ($resources as $resource) {
            $this->primary($resource);
        }

        return $this;
    }

    /**
     * @param array<string, mixed> $resource
     */
    public function primary(array $resource) : self
    {
        $this->resources[] = $resource;

        return $this;
    }

    /**
     * @param list<array<string, mixed>> $resources
     */
    public function includes(array $resources) : self
    {
        foreach ($resources as $resource) {
            $this->include($resource);
        }

        return $this;
    }

    /**
     * @param array<string, mixed> $resource
     */
    public function include(array $resource) : self
    {
        $key = "{$resource['type']}:{$resource['id']}";

        if (! isset($this->includedKeys[$key])) {
            $this->includedKeys[$key] = true;
            $this->included[]         = $resource;
        }

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function build() : array
    {
        $document = [];

        if (count($this->resources) === 1) {
            $document['data'] = $this->resources[0];
        } else {
            $document['data'] = $this->resources;
        }

        if ($this->included !== []) {
            $document['included'] = $this->included;
        }

        return $document;
    }
}
