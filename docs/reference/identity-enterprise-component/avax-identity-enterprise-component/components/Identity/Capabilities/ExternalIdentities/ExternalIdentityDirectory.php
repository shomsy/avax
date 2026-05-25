<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Capabilities\ExternalIdentities;

use Avax\Components\Identity\Foundation\Values\ExternalProvider;
use Avax\Components\Identity\Foundation\Values\ExternalSubject;

interface ExternalIdentityDirectory
{
    public function link(ExternalIdentityLink $link): void;

    public function find(ExternalProvider $provider, ExternalSubject $subject): ExternalIdentityLink|null;
}
