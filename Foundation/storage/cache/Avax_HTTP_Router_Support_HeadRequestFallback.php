<?php

use Avax\Container\Features\Think\Model\MethodPrototype;
use Avax\Container\Features\Think\Model\ParameterPrototype;
use Avax\Container\Features\Think\Model\ServicePrototype;

return ServicePrototype::__set_state(array: [
                                                'class'              => \Avax\HTTP\Router\Support\HeadRequestFallback::class,
                                                'constructor'        =>
                                                    MethodPrototype::__set_state(array: [
                                                                                            'name'       => '__construct',
                                                                                            'parameters' =>
                                                                                                [
                                                                                                    ParameterPrototype::__set_state(array: [
                                                                                                                                               'name'       => 'router',
                                                                                                                                               'type'       => \Avax\HTTP\Router\Routing\HttpRequestRouter::class,
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
