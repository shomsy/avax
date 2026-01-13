<?php

use Avax\Container\Features\Think\Model\MethodPrototype;
use Avax\Container\Features\Think\Model\ParameterPrototype;
use Avax\Container\Features\Think\Model\ServicePrototype;

return ServicePrototype::__set_state(array: [
    'class'              => 'Avax\\HTTP\\Router\\Routing\\RouterRegistrar',
    'constructor'        =>
        MethodPrototype::__set_state(array: [
            'name'       => '__construct',
            'parameters' =>
                [
                    0 =>
                        ParameterPrototype::__set_state(array: [
                            'name'       => 'registry',
                            'type'       => 'Avax\\HTTP\\Router\\Support\\RouteRegistry',
                            'hasDefault' => false,
                            'default'    => null,
                            'isVariadic' => false,
                            'allowsNull' => false,
                            'required'   => true,
                        ]),
                    1 =>
                        ParameterPrototype::__set_state(array: [
                            'name'       => 'httpRequestRouter',
                            'type'       => 'Avax\\HTTP\\Router\\Routing\\HttpRequestRouter',
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
