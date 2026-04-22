<?php

use Avax\Container\Features\Think\Model\MethodPrototype;
use Avax\Container\Features\Think\Model\ParameterPrototype;
use Avax\Container\Features\Think\Model\ServicePrototype;

return ServicePrototype::__set_state(array: [
    'class'              => 'Avax\\Config\\Service\\Config',
    'constructor'        => MethodPrototype::__set_state(array: [
        'name'       => '__construct',
        'parameters' => [
            0 => ParameterPrototype::__set_state(array: [
                'name'       => 'configLoader',
                'type'       => 'Avax\\Config\\Configurator\\FileLoader\\ConfigLoaderInterface',
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
