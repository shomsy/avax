<?php

use Avax\Container\DependencyInjection\Capability\Prototypes\Model\MethodPrototype;
use Avax\Container\DependencyInjection\Capability\Prototypes\Model\ParameterPrototype;
use Avax\Container\DependencyInjection\Capability\Prototypes\Model\ServicePrototype;

return ServicePrototype::__set_state(array: [
                                                'class'              => 'Avax\\Logging\\ErrorLogger',
                                                'constructor'        => MethodPrototype::__set_state(array: [
                                                                                                                'name'       => '__construct',
                                                                                                                'parameters' => [
                                                                                                                    0 => ParameterPrototype::__set_state(array: [
                                                                                                                                                                    'name'       => 'logWriter',
                                                                                                                                                                    'type'       => 'Avax\\Logging\\LogWriterInterface',
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
