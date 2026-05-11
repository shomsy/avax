<?php

declare(strict_types=1);

namespace Avax\Components\API\ApiBlueprint\System\Capabilities\JsonApi;

final class BuildResourceObject
{
    private string $type;

    private string|int $id;

    /**
     * @var array<string, mixed>
     */
    private array $attributes = [];

    /**
     * @var array<string, mixed>
     */
    private array $relationships = [];

    /**
     * @var array<string, mixed>
     */
    private array $links = [];

    public function __construct(string $type, string|int $id)
    {
        $this->type = $type;
        $this->id   = $id;
    }

    public function attribute(string $key, mixed $value) : self
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function attributes(array $attributes) : self
    {
        $this->attributes = array_merge($this->attributes, $attributes);

        return $this;
    }

    /**
     * @param array<string, mixed>|null $links
     */
    public function relationship(string $name, string $type, string|int $id, array|null $links = null) : self
    {
        $relationship = [
            'data' => [
                'type' => $type,
                'id'   => $id,
            ],
        ];

        if ($links !== null) {
            $relationship['links'] = $links;
        }

        $this->relationships[$name] = $relationship;

        return $this;
    }

    public function link(string $rel, string $url) : self
    {
        $this->links[$rel] = $url;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function build() : array
    {
        $resource = [
            'type' => $this->type,
            'id'   => (string) $this->id,
        ];

        if ($this->attributes !== []) {
            $resource['attributes'] = $this->attributes;
        }

        if ($this->relationships !== []) {
            $resource['relationships'] = $this->relationships;
        }

        if ($this->links !== []) {
            $resource['links'] = $this->links;
        }

        return $resource;
    }
}
