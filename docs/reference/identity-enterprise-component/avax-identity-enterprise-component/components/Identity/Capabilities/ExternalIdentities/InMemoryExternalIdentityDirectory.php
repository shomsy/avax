<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Capabilities\ExternalIdentities;

use Avax\Components\Identity\Foundation\State\ResettableIdentityState;
use Avax\Components\Identity\Foundation\Values\ExternalProvider;
use Avax\Components\Identity\Foundation\Values\ExternalSubject;

final class InMemoryExternalIdentityDirectory implements ExternalIdentityDirectory, ResettableIdentityState
{
    /** @var array<string, ExternalIdentityLink> */
    private array $links = [];

    public function link(ExternalIdentityLink $link): void
    {
        $this->links[$this->key($link->provider(), $link->subject())] = $link;
    }

    public function find(ExternalProvider $provider, ExternalSubject $subject): ExternalIdentityLink|null
    {
        return $this->links[$this->key($provider, $subject)] ?? null;
    }

    public function reset(): void
    {
        $this->links = [];
    }

    private function key(ExternalProvider $provider, ExternalSubject $subject): string
    {
        return $provider->name().'|'.$subject->toString();
    }
}
