<?php

declare(strict_types=1);

use Avax\Components\Application\Container\Features\Think\Model\MethodPrototype;
use Avax\Components\Application\Container\Features\Think\Model\ParameterPrototype;
use Avax\Components\Application\Container\Features\Think\Model\ServicePrototype;
use Avax\Components\HTTP\Router\Routing\HttpRequestRouter;
use Avax\Components\HTTP\Router\Routing\RouterRegistrar;
use Avax\Components\HTTP\Router\Support\RouteRegistry;
use Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Definitions\RouteRegistry;
use Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Definitions\RouterRegistrar;
use Avax\Components\HTTP\Router\System\Flows\ResolveRequest\HttpRequestRouter;

return ServicePrototype::__set_state(array: [
                                                'class'              => RouterRegistrar::class,
                                                'constructor'        => MethodPrototype::__set_state(array: [
                                                                                            'name'       => '__construct',
                                                                                            'parameters' => [
                                                                                                    ParameterPrototype::__set_state(array: [
                                                                                                                                               'name'       => 'registry',
                                                                                                                                               'type' => RouteRegistry::class,
                                                                                                                                               'hasDefault' => false,
                                                                                                                                               'default'    => null,
                                                                                                                                               'isVariadic' => false,
                                                                                                                                               'allowsNull' => false,
                                                                                                                                               'required'   => true,
                                                                                                                                           ]),
                                                                                                    ParameterPrototype::__set_state(array: [
                                                                                                                                               'name'       => 'httpRequestRouter',
                                                                                                                                               'type' => HttpRequestRouter::class,
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
                                                'injectedMethods'    => [
                                                    ],
                                                'isInstantiable'     => true,
                                            ]);
