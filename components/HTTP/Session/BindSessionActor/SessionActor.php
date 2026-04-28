<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\BindSessionActor;

final class SessionActor
{
    private string $id;
    private array  $data;

    public function __construct(string $id, array $data = [])
    {
        $this->id   = $id;
        $this->data = $data;
    }

    public function id() : string
    {
        return $this->id;
    }

    public function data() : array
    {
        return $this->data;
    }
}