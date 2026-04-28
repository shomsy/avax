<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\Capabilities\Body\Problem;

final readonly class ProblemDetails
{
    public function __construct(
        public string      $title,
        public int         $status,
        public string      $detail = '',
        public string      $type = 'about:blank',
        public string|null $instance = null,
        public array       $extensions = [],
    ) {}

    public function toArray() : array
    {
        $payload = [
            'type'   => $this->type,
            'title'  => $this->title,
            'status' => $this->status,
        ];

        if ($this->detail !== '') {
            $payload['detail'] = $this->detail;
        }

        if ($this->instance !== null) {
            $payload['instance'] = $this->instance;
        }

        foreach ($this->extensions as $key => $value) {
            $payload[$key] = $value;
        }

        return $payload;
    }
}
