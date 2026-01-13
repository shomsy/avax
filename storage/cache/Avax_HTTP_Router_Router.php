<?php

return \Avax\Container\Features\Think\Model\ServicePrototype::__set_state(array(
   'class' => 'Avax\\HTTP\\Router\\Router',
   'constructor' => 
  \Avax\Container\Features\Think\Model\MethodPrototype::__set_state(array(
     'name' => '__construct',
     'parameters' => 
    array (
      0 => 
      \Avax\Container\Features\Think\Model\ParameterPrototype::__set_state(array(
         'name' => 'httpRequestRouter',
         'type' => 'Avax\\HTTP\\Router\\Routing\\HttpRequestRouter',
         'hasDefault' => false,
         'default' => NULL,
         'isVariadic' => false,
         'allowsNull' => false,
         'required' => true,
      )),
      1 => 
      \Avax\Container\Features\Think\Model\ParameterPrototype::__set_state(array(
         'name' => 'kernel',
         'type' => 'Avax\\HTTP\\Router\\Kernel\\RouterKernel',
         'hasDefault' => false,
         'default' => NULL,
         'isVariadic' => false,
         'allowsNull' => false,
         'required' => true,
      )),
      2 => 
      \Avax\Container\Features\Think\Model\ParameterPrototype::__set_state(array(
         'name' => 'fallbackManager',
         'type' => 'Avax\\HTTP\\Router\\Support\\FallbackManager',
         'hasDefault' => false,
         'default' => NULL,
         'isVariadic' => false,
         'allowsNull' => false,
         'required' => true,
      )),
      3 => 
      \Avax\Container\Features\Think\Model\ParameterPrototype::__set_state(array(
         'name' => 'errorFactory',
         'type' => 'Avax\\HTTP\\Router\\Routing\\ErrorResponseFactory',
         'hasDefault' => false,
         'default' => NULL,
         'isVariadic' => false,
         'allowsNull' => false,
         'required' => true,
      )),
      4 => 
      \Avax\Container\Features\Think\Model\ParameterPrototype::__set_state(array(
         'name' => 'dslRouter',
         'type' => 'Avax\\HTTP\\Router\\RouterInterface',
         'hasDefault' => true,
         'default' => NULL,
         'isVariadic' => false,
         'allowsNull' => true,
         'required' => false,
      )),
      5 => 
      \Avax\Container\Features\Think\Model\ParameterPrototype::__set_state(array(
         'name' => 'groupStack',
         'type' => 'Avax\\HTTP\\Router\\Routing\\RouteGroupStack',
         'hasDefault' => true,
         'default' => NULL,
         'isVariadic' => false,
         'allowsNull' => true,
         'required' => false,
      )),
      6 => 
      \Avax\Container\Features\Think\Model\ParameterPrototype::__set_state(array(
         'name' => 'routeRegistry',
         'type' => 'Avax\\HTTP\\Router\\Support\\RouteRegistry',
         'hasDefault' => true,
         'default' => NULL,
         'isVariadic' => false,
         'allowsNull' => true,
         'required' => false,
      )),
    ),
  )),
   'injectedProperties' => 
  array (
  ),
   'injectedMethods' => 
  array (
  ),
   'isInstantiable' => true,
));
