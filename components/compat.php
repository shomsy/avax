<?php

declare(strict_types=1);

$classAliases = [
    'Avax\\DataHandling\\DataTransfer\\DataTransfer'                                          => 'Avax\\DataFoundation\\DataTransfer\\DataTransfer',
    'Avax\\DataHandling\\DataTransfer\\Capabilities\\Attributes\\CastWith'                    => 'Avax\\DataFoundation\\DataTransfer\\Capabilities\\Attributes\\CastWith',
    'Avax\\DataHandling\\DataTransfer\\Capabilities\\Attributes\\Hidden'                      => 'Avax\\DataFoundation\\DataTransfer\\Capabilities\\Attributes\\Hidden',
    'Avax\\DataHandling\\DataTransfer\\Capabilities\\Attributes\\ListOf'                      => 'Avax\\DataFoundation\\DataTransfer\\Capabilities\\Attributes\\ListOf',
    'Avax\\DataHandling\\DataTransfer\\Capabilities\\Attributes\\MapFrom'                     => 'Avax\\DataFoundation\\DataTransfer\\Capabilities\\Attributes\\MapFrom',
    'Avax\\DataHandling\\DataTransfer\\Capabilities\\ValueConversion\\ValueCasterInterface'   => 'Avax\\DataFoundation\\DataTransfer\\Capabilities\\ValueConversion\\ValueCasterInterface',
    'Avax\\DataHandling\\DataTransfer\\Capabilities\\ValueConversion\\ValueConversionContext' => 'Avax\\DataFoundation\\DataTransfer\\Capabilities\\ValueConversion\\ValueConversionContext',
    'Avax\\DataHandling\\DataTransfer\\InspectDataShape\\DataField'                           => 'Avax\\DataFoundation\\DataTransfer\\InspectDataShape\\DataField',
    'Avax\\DataHandling\\ObjectHandling\\DTO\\AbstractDTO'                                    => 'Avax\\Components\\Persistence\\System\\Capabilities\\ObjectHandling\\DTO\\AbstractDTO',
    'Avax\\DataFoundation\\ObjectHandling\\DTO\\AbstractDTO'                                  => 'Avax\\Components\\Persistence\\System\\Capabilities\\ObjectHandling\\DTO\\AbstractDTO',
    'Avax\\DataFoundation\\Collection'                                                        => 'Avax\\Components\\Data\\System\\Capabilities\\Collections\\Collection',
    'Avax\\Text\\Text'                                                                        => 'Avax\\Components\\Text\\System\\PublicSurface\\Text',
    'Avax\\DataHandling\\ObjectHandling\\DTO\\DTOValidationException'                         => 'Avax\\DataFoundation\\ObjectHandling\\DTO\\DTOValidationException',
    'Avax\\DataHandling\\Validation\\Attributes\\Rules\\EmailRule'                            => 'Avax\\DataFoundation\\Validation\\Attributes\\Rules\\EmailRule',
    'Avax\\DataHandling\\Validation\\Attributes\\Rules\\MinLengthRule'                        => 'Avax\\DataFoundation\\Validation\\Attributes\\Rules\\MinLengthRule',

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
    'Avax\\HTTP\\Request\\Request'                                                            => 'Avax\\HTTP\\Request\\Request',
    'Avax\\HTTP\\Request\\RequestDtoFactory'                                                  => 'Avax\\HTTP\\Request\\RequestDtoFactory',
    'Avax\\Logging\\LoggerFactory'                                                            => 'Avax\\Components\\Logging\\System\\Configuration\\RegisterLogging',
    'Avax\\Cache\\Cache'                                                                      => 'Avax\\Components\\Cache\\System\\PublicSurface\\Cache',
    'Avax\\Config\\Architecture\\DDD\\AppPath'                                                => 'Avax\\Components\\Config\\System\\Capabilities\\Architecture\\AppPath',
    'Avax\\Config\\Architecture\\DDD\\AppPath'                                                => 'Avax\\Components\\Config\\System\\Capabilities\\Architecture\\AppPath',
    'components\\Config\\Service\\Config'                                                     => 'Avax\\Components\\Config\\System\\PublicSurface\\Config',
    'components\\Config\\Architecture\\DDD\\AppPath'                                          => 'Avax\\Components\\Config\\System\\Capabilities\\Architecture\\AppPath',
    'Avax\\DumpDebugger\\DumpDebugger'                                                        => 'Avax\\Components\\DumpDebugger\\System\\PublicSurface\\Dump',
    'components\\HTTP\\Kernel'                                                                => 'Avax\\Components\\HTTP\\System\\Capabilities\\Kernel\\Kernel',
    'Avax\\HTTP\\HttpKernel'                                                                  => 'Avax\\Components\\HTTP\\System\\Capabilities\\Kernel\\HttpKernel',
    'Avax\\HTTP\\AppKernel'                                                                   => 'Avax\\Components\\HTTP\\System\\Capabilities\\Kernel\\AppKernel',
    'Avax\\HTTP\\ResolveRouteFromHttpRequest'                                                 => 'Avax\\Components\\HTTP\\System\\Flows\\Routing\\ResolveRouteFromHttpRequest',
    'Avax\\HTTP\\RouterBootstrapper'                                                          => 'Avax\\Components\\HTTP\\System\\Configuration\\RouterBootstrapper',
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
