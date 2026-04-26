<?php

use components\Container\DependencyInjection\Capability\Prototypes\Model\MethodPrototype;
use components\Container\DependencyInjection\Capability\Prototypes\Model\ParameterPrototype;
use components\Container\DependencyInjection\Capability\Prototypes\Model\ServicePrototype;
use components\Logging\ErrorHandler;

return ServicePrototype::__set_state(array: [
                                                'class' => ErrorHandler::class,
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
