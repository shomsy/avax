<?php

declare(strict_types=1);

use components\Container\Features\Think\Model\MethodPrototype;
use components\Container\Features\Think\Model\ParameterPrototype;
use components\Container\Features\Think\Model\ServicePrototype;
use components\HTTP\Router\Routing\RouteMatcher;
use components\HTTP\Router\System\Flows\ResolveRequest\Matching\RouteMatcher;

return ServicePrototype::__set_state(array: [
                                                'class'              => RouteMatcher::class,
                                                'constructor'        => MethodPrototype::__set_state(array: [
                                                                                            'name'       => '__construct',
                                                                                            'parameters' => [
                                                                                                    ParameterPrototype::__set_state(array: [
                                                                                                                                               'name'       => 'logger',
                                                                                                                                               'type'       => 'Psr\\Log\\LoggerInterface',
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
