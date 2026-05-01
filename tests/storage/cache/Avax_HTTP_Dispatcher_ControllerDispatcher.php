<?php

declare(strict_types=1);

use Avax\Components\Application\Container\Features\Think\Model\MethodPrototype;
use Avax\Components\Application\Container\Features\Think\Model\ParameterPrototype;
use Avax\Components\Application\Container\Features\Think\Model\ServicePrototype;
use Avax\Components\HTTP\Dispatcher\ControllerDispatcher;

return ServicePrototype::__set_state(array: [
                                                'class'           => ControllerDispatcher::class,
                                                'constructor'     => MethodPrototype::__set_state(array: [
                                                                                            'name'       => '__construct',
                                                                                            'parameters' => [
                                                                                                    ParameterPrototype::__set_state(array: [
                                                                                                                                               'name'       => 'container',
                                                                                                                                               'type'       => 'Psr\\Container\\ContainerInterface',
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
