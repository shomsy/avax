<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\Logout;

use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\BackChannelLogout\BackChannelLogout;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\BackChannelLogout\BackChannelLogoutData;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\FrontChannelLogout\FrontChannelLogout;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\FrontChannelLogout\FrontChannelLogoutData;

final readonly class Logout
{
    public function __construct(private FrontChannelLogout $frontChannelLogout, private BackChannelLogout $backChannelLogout) {}

    public function execute(LogoutData $data) : LogoutResult
    {
        if ($data->logoutToken !== null && trim(string: $data->logoutToken) !== '') {
            return $this->backChannelLogout->execute(data: new BackChannelLogoutData(logoutToken: $data->logoutToken));
        }

        return $this->frontChannelLogout->execute(data: new FrontChannelLogoutData(
                                                            sessionId            : $data->sessionId,
                                                            idTokenHint          : $data->idTokenHint,
                                                            postLogoutRedirectUri: $data->postLogoutRedirectUri,
                                                            state                : $data->state,
                                                        ));
    }
}
