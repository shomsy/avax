<?php

use Avax\Container\Features\Think\Model\MethodPrototype;
use Avax\Container\Features\Think\Model\ParameterPrototype;
use Avax\Container\Features\Think\Model\ServicePrototype;
use Avax\HTTP\Middleware\MiddlewareGroupResolver;
use Avax\HTTP\Middleware\MiddlewareResolver;

return ServicePrototype::__set_state(array: [
                                                'class'              => MiddlewareResolver::class,
                                                'constructor'        =>
                                                    MethodPrototype::__set_state(array: [
                                                                                            'name'       => '__construct',
                                                                                            'parameters' =>
                                                                                                [
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
                                                'injectedProperties' =>
                                                    [
                                                    ],
                                                'injectedMethods'    =>
                                                    [
                                                    ],
                                                'isInstantiable'     => true,
                                            ]);
