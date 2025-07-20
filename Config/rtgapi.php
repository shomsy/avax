<?php

declare(strict_types=1);

/**
 * Base part of the API URLs that is common across different casino endpoints.
 */
const BASE_URL_PART = 'RTGWebAPI';

$pacificspinsCommon = [
    'url' => 'https://admin.firefoxcasino.com/FRXZFAWKYQBEJGVNGWGP/' . BASE_URL_PART,
    'key' => 'cc6ec3eb-d41a-471f-a917-e38aa9b900e3',
];

/**
 * Returns an array of casino backend API endpoints.
 *
 * This associative array maps casino identifiers to their corresponding admin URLs.
 * The URLs point to the Swagger UI for the respective casinos' RTGWebAPI.
 * The common part of the URLs is defined in the BASE_URL_PART constant.
 *
 * @return array<string, array<string, string>> An associative array where the key is the casino identifier,
 *                                              and the value is an array with 'url' and 'key'.
 */
return [
    'extreme'      => [
        'url' => 'https://admin.casinoextreme.eu/ALEKSMENWNQMDKTOIDJG/' . BASE_URL_PART,
        'key' => '04b57beb-8fce-435b-8267-67b96f768e82',
    ],
    'brango'       => [
        'url' => 'https://admin.casinobrango.com/BRNG2USDZDPYTSZWZTQP/' . BASE_URL_PART,
        'key' => '8cae32ff-c1b8-4a10-bb3e-25559e87f2ef',
    ],
    'yabby'        => [
        'url' => 'https://admin.yabbycasino.com/YABBYECVSUGMOQMOIPQO/' . BASE_URL_PART,
        'key' => '209b63e6-bac6-457a-a844-109b3f30c935',
    ],
    'limitless'    => [
        'url' => 'https://mcclimcasweb.limitlesscasino.com/TXPAKBRQGSUOZSVKZVHD/' . BASE_URL_PART,
        'key' => 'e9ae3f26-72e6-454d-ad2f-3cb8f577e556',
    ],
    'pacificspins' => $pacificspinsCommon,
    'firefox'      => $pacificspinsCommon,
    'pacific'      => $pacificspinsCommon,
    'orbitspins'   => [
        'url' => 'https://web.orbitspins.com/ORBT2USDZDPYTSZWDMBT/' . BASE_URL_PART,
        'key' => '53974538-a592-4c1c-b250-8acbea52e0f0',
    ],
    'bonusblitz'   => [
        'url' => 'https://admin.bonusblitz.com/NOITKEIBKYYBSWNPAVPF/' . BASE_URL_PART,
        'key' => '19e9de80-b35b-4df9-933b-63647cec3ec7',
    ],
    'bettywins'    => [
        'url' => 'https://mccbettywsweb.bettywins.com/PFTIDWTPWFCMHQFXYOVW/' . BASE_URL_PART,
        'key' => 'dc73b0fc-b274-4570-896a-de97da797ec3',
    ],
];

