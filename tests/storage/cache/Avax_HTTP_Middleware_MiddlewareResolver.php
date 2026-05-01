<?php

declare(strict_types=1);

use Avax\Components\Application\Container\Features\Think\Model\MethodPrototype;
use Avax\Components\Application\Container\Features\Think\Model\ParameterPrototype;
use Avax\Components\Application\Container\Features\Think\Model\ServicePrototype;
use Avax\Components\HTTP\Middleware\MiddlewareGroupResolver;
use Avax\Components\HTTP\Middleware\MiddlewareResolver;

return ServicePrototype::__set_state(array: [
                                                'class'              => MiddlewareResolver::class,
                                                'constructor'        => MethodPrototype::__set_state(array: [
                                                                                            'name'       => '__construct',
                                                                                            'parameters' => [
                                                                                                    ParameterPrototype::__set_state(array: [
                                                                                                                                               'name'       => 'groupResolver',
                                                                                                                                               'type' => MiddlewareGroupResolver::class,
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
