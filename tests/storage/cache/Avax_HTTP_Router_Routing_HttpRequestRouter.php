<?php

use Avax\Container\Features\Think\Model\MethodPrototype;
use Avax\Container\Features\Think\Model\ParameterPrototype;
use Avax\Container\Features\Think\Model\ServicePrototype;

return ServicePrototype::__set_state(array: [
    'class'              => 'Avax\\HTTP\\Router\\Routing\\HttpRequestRouter',
    'constructor'        =>
        MethodPrototype::__set_state(array: [
            'name'       => '__construct',
            'parameters' =>
                [
                    0 =>
                        ParameterPrototype::__set_state(array: [
                            'name'       => 'constraintValidator',
                            'type'       => 'Avax\\HTTP\\Router\\Validation\\RouteConstraintValidator',
                            'hasDefault' => false,
                            'default'    => null,
                            'isVariadic' => false,
                            'allowsNull' => false,
                            'required'   => true,
                        ]),
                    1 =>
                        ParameterPrototype::__set_state(array: [
                            'name'       => 'matcher',
                            'type'       => 'Avax\\HTTP\\Router\\Routing\\RouteMatcher',
                            'hasDefault' => false,
                            'default'    => null,
                            'isVariadic' => false,
                            'allowsNull' => false,
                            'required'   => true,
                        ]),
                    2 =>
                        ParameterPrototype::__set_state(array: [
                            'name'       => 'logger',
                            'type'       => 'Psr\\Log\\LoggerInterface',
                            'hasDefault' => true,
                            'default'    => null,
                            'isVariadic' => false,
                            'allowsNull' => true,
                            'required'   => false,
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
