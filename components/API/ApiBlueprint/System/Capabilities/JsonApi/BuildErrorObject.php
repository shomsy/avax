<?php

declare(strict_types=1);

namespace Avax\Components\API\ApiBlueprint\System\Capabilities\JsonApi;

final class BuildErrorObject
{
    private string|null $id = null;

    private string|null $status = null;

    private string|null $code = null;

    private string|null $title = null;

    private string|null $detail = null;

    /**
     * @var array{pointer?: string, parameter?: string}|null
     */
    private array|null $source = null;

    /**
     * @var array<string, mixed>|null
     */
    private array|null $meta = null;

    public function withId(string $id) : self
    {
        $this->id = $id;

        return $this;
    }

    public function withStatus(string $status) : self
    {
        $this->status = $status;

        return $this;
    }

    public function withCode(string $code) : self
    {
        $this->code = $code;

        return $this;
    }

    public function withTitle(string $title) : self
    {
        $this->title = $title;

        return $this;
    }

    public function withDetail(string $detail) : self
    {
        $this->detail = $detail;

        return $this;
    }

    /**
     * @param array{pointer?: string, parameter?: string} $source
     */
    public function withSource(array $source) : self
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @param array<string, mixed> $meta
     */
    public function withMeta(array $meta) : self
    {
        $this->meta = $meta;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function build() : array
    {
        $error = [];

        if ($this->id !== null) {
            $error['id'] = $this->id;
        }

        if ($this->status !== null) {
            $error['status'] = $this->status;
        }

        if ($this->code !== null) {
            $error['code'] = $this->code;
        }

        if ($this->title !== null) {
            $error['title'] = $this->title;
        }

        if ($this->detail !== null) {
            $error['detail'] = $this->detail;
        }

        if ($this->source !== null) {
            $error['source'] = $this->source;
        }

        if ($this->meta !== null) {
            $error['meta'] = $this->meta;
        }

        return $error;
    }
}
