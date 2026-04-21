<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\JarmResponse;

use SensitiveParameter;

final readonly class BuildJarmResponseData
{
    public string|null $state;
    public string      $code;
    public string      $clientId;

    public function __construct(
        string                       $clientId,
        #[SensitiveParameter] string $code,
        string|null                  $state = null
    )
    {
        $this->clientId = $clientId;
        $this->code     = $code;
        $this->state    = $state;
    }
}
