<?php

declare(strict_types=1);

use Avax\Components\Application\Container\Features\Think\Model\MethodPrototype;
use Avax\Components\Application\Container\Features\Think\Model\ParameterPrototype;
use Avax\Components\Application\Container\Features\Think\Model\ServicePrototype;
use Avax\Components\HTTP\Dispatcher\ControllerDispatcher;
use Avax\Components\HTTP\Router\RouterDsl;
use Avax\Components\HTTP\Router\Routing\HttpRequestRouter;
use Avax\Components\HTTP\Router\Routing\RouterRegistrar;
use Avax\Components\HTTP\Router\Support\FallbackManager;
use Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Definitions\RouterRegistrar;
use Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\RouterDsl;
use Avax\Components\HTTP\Router\System\Flows\ResolveRequest\HttpRequestRouter;

return ServicePrototype::__set_state(array: [
                                                'class'           => RouterDsl::class,
                                                'constructor'     => MethodPrototype::__set_state(array: [
                                                                                            'name'       => '__construct',
                                                                                            'parameters' => [
                                                                                                    ParameterPrototype::__set_state(array: [
                                                                                                                                               'name'       => 'registrar',
                                                                                                                                               'type' => RouterRegistrar::class,
                                                                                                                                               'hasDefault' => false,
                                                                                                                                               'default'    => null,
                                                                                                                                               'isVariadic' => false,
                                                                                                                                               'allowsNull' => false,
                                                                                                                                               'required'   => true,
                                                                                                                                           ]),
                                                                                                    ParameterPrototype::__set_state(array: [
                                                                                                                                               'name'       => 'router',
                                                                                                                                               'type' => HttpRequestRouter::class,
                                                                                                                                               'hasDefault' => false,
                                                                                                                                               'default'    => null,
                                                                                                                                               'isVariadic' => false,
                                                                                                                                               'allowsNull' => false,
                                                                                                                                               'required'   => true,
                                                                                                                                           ]),
                                                                                                    ParameterPrototype::__set_state(array: [
                                                                                                                                               'name'       => 'controllerDispatcher',
                                                                                                                                               'type' => ControllerDispatcher::class,
                                                                                                                                               'hasDefault' => false,
                                                                                                                                               'default'    => null,
                                                                                                                                               'isVariadic' => false,
                                                                                                                                               'allowsNull' => false,
                                                                                                                                               'required'   => true,
                                                                                                                                           ]),
                                                                                                    ParameterPrototype::__set_state(array: [
                                                                                                                                               'name'       => 'fallbackManager',
                                                                                                                                               'type' => FallbackManager::class,
                                                                                                                                               'hasDefault' => false,
                                                                                                                                               'default'    => null,
                                                                                                                                               'isVariadic' => false,
                                                                                                                                               'allowsNull' => false,
                                                                                                                                               'required'   => true,
                                                                                                                                           ]),
                                                                                                ],
                                                                                        ]),
                                                'injectedProperties' => [
                                                    ],
                                                'injectedMethods' => [
                                                    ],
                                                'isInstantiable'  => true,
                                            ]);
