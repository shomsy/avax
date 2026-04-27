<?php

declare(strict_types=1);

$classAliases = [
    'Avax\\HTTP\\Request\\ServerRequest\\IncomingRequest\\ServerRequest' => 'components\\HTTP\\Request\\ServerRequest\\IncomingRequest\\ServerRequest',
    'Avax\\HTTP\\Router\\System\\Capabilities\\RouteDefinition\\RouteDefinition' => 'components\\HTTP\\Router\\System\\Capabilities\\RouteDefinition\\RouteDefinition',
    'components\\HTTP\\Request\\ServerRequest\\IncomingRequest\\ServerInit' => 'Avax\\HTTP\\Request\\ServerRequest\\IncomingRequest\\ServerInit',
    'components\\HTTP\\Request\\ServerRequest\\IncomingRequest\\RequestInit' => 'Avax\\HTTP\\Request\\ServerRequest\\IncomingRequest\\RequestInit',
    'Avax\\HTTP\\Request\\ServerRequest\\IncomingRequest\\RequestBody\\RequestBody' => 'components\\HTTP\\Request\\ServerRequest\\IncomingRequest\\RequestBody\\RequestBody',
    'Avax\\HTTP\\Request\\ServerRequest\\IncomingRequest\\RequestHeaders\\RequestHeaders' => 'components\\HTTP\\Request\\ServerRequest\\IncomingRequest\\RequestHeaders\\RequestHeaders',
    'components\\HTTP\\Request\\ServerRequest\\IncomingRequest\\RequestHeaders\\NormalizeHeaders' => 'Avax\\HTTP\\Request\\ServerRequest\\IncomingRequest\\RequestHeaders\\NormalizeHeaders',
    'Avax\\HTTP\\Request\\ServerRequest\\IncomingRequest\\RequestTarget\\ReadRequestTarget' => 'components\\HTTP\\Request\\ServerRequest\\IncomingRequest\\RequestTarget\\ReadRequestTarget',
    'Avax\\HTTP\\Request\\ServerRequest\\IncomingRequest\\UploadedFiles\\GuardUploadedFiles' => 'components\\HTTP\\Request\\ServerRequest\\IncomingRequest\\UploadedFiles\\GuardUploadedFiles',
    'Avax\\HTTP\\Request\\ServerRequest\\IncomingRequest\\RequestBody\\Parsers\\ParseBodyByContentType' => 'components\\HTTP\\Request\\ServerRequest\\IncomingRequest\\RequestBody\\Parsers\\ParseBodyByContentType',
    'Avax\\HTTP\\Request\\ServerRequest\\IncomingRequest\\RequestBody\\Parsers\\ParseJsonBody' => 'components\\HTTP\\Request\\ServerRequest\\IncomingRequest\\RequestBody\\Parsers\\ParseJsonBody',
    'components\\HTTP\\Request\\ServerRequest\\IncomingRequest\\RequestBody\\Parsers\\ParseFormBody' => 'Avax\\HTTP\\Request\\ServerRequest\\IncomingRequest\\RequestBody\\Parsers\\ParseFormBody',
    'components\\HTTP\\Enums\\HttpMethod' => 'Avax\\HTTP\\Enums\\HttpMethod',
    'components\\HTTP\\Router\\HttpMethod' => 'Avax\\HTTP\\Router\\HttpMethod',
    'Avax\\HTTP\\Router\\System\\Foundation\\Exceptions\\ReservedRouteNameException' => 'components\\HTTP\\Router\\System\\Foundation\\Exceptions\\ReservedRouteNameException',
    'components\\HTTP\\Router\\System\\Foundation\\Exceptions\\RouterExceptionInterface' => 'Avax\\HTTP\\Router\\System\\Foundation\\Exceptions\\RouterExceptionInterface',
    'components\\HTTP\\Response\\Response' => 'Avax\\HTTP\\Response\\Response',
    'components\\HTTP\\Request\\Request' => 'Avax\\HTTP\\Request\\Request',
    'components\\HTTP\\Request\\RequestDtoFactory' => 'Avax\\HTTP\\Request\\RequestDtoFactory',
];

foreach ($classAliases as $alias => $target) {
    if (class_exists($alias, false) || interface_exists($alias, false)) {
        continue;
    }

    if (interface_exists($target) || class_exists($target)) {
        class_alias(
            class     : $target,
            alias     : $alias,
            autoload  : false
        );
    }
}
