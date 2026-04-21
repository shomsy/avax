<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\Logout;

use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\BackChannelLogout\BackChannelLogout;
use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\BackChannelLogout\BackChannelLogoutData;
use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\FrontChannelLogout\FrontChannelLogout;
use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\FrontChannelLogout\FrontChannelLogoutData;

final readonly class Logout
{
    private BackChannelLogout  $backChannelLogout;
    private FrontChannelLogout $frontChannelLogout;

    public function __construct(
        FrontChannelLogout $frontChannelLogout,
        BackChannelLogout  $backChannelLogout
    )
    {
        $this->frontChannelLogout = $frontChannelLogout;
        $this->backChannelLogout  = $backChannelLogout;
    }

    public function execute(LogoutData $data) : LogoutResult
    {
        if ($data->logoutToken !== null && trim($data->logoutToken) !== '') {
            return $this->backChannelLogout->execute(data: new BackChannelLogoutData(logoutToken: $data->logoutToken));
        }

        return $this->frontChannelLogout->execute(data: new FrontChannelLogoutData(
                                                            sessionId            : $data->sessionId,
                                                            idTokenHint          : $data->idTokenHint,
                                                            postLogoutRedirectUri: $data->postLogoutRedirectUri,
                                                            state                : $data->state
                                                        ));
    }
}
