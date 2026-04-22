<?php

return \Avax\Container\Features\Think\Model\ServicePrototype::__set_state(array(
   'class' => 'Avax\\HTTP\\Router\\RouterDsl',
   'constructor' => 
  \Avax\Container\Features\Think\Model\MethodPrototype::__set_state(array(
     'name' => '__construct',
     'parameters' => 
    array (
      0 => 
      \Avax\Container\Features\Think\Model\ParameterPrototype::__set_state(array(
         'name' => 'registrar',
         'type' => 'Avax\\HTTP\\Router\\Routing\\RouterRegistrar',
         'hasDefault' => false,
         'default' => NULL,
         'isVariadic' => false,
         'allowsNull' => false,
         'required' => true,
      )),
      1 => 
      \Avax\Container\Features\Think\Model\ParameterPrototype::__set_state(array(
         'name' => 'router',
         'type' => 'Avax\\HTTP\\Router\\Routing\\HttpRequestRouter',
         'hasDefault' => false,
         'default' => NULL,
         'isVariadic' => false,
         'allowsNull' => false,
         'required' => true,
      )),
      2 => 
      \Avax\Container\Features\Think\Model\ParameterPrototype::__set_state(array(
         'name' => 'controllerDispatcher',
         'type' => 'Avax\\HTTP\\Dispatcher\\ControllerDispatcher',
         'hasDefault' => false,
         'default' => NULL,
         'isVariadic' => false,
         'allowsNull' => false,
         'required' => true,
      )),
      3 => 
      \Avax\Container\Features\Think\Model\ParameterPrototype::__set_state(array(
         'name' => 'fallbackManager',
         'type' => 'Avax\\HTTP\\Router\\Support\\FallbackManager',
         'hasDefault' => false,
         'default' => NULL,
         'isVariadic' => false,
         'allowsNull' => false,
         'required' => true,
      )),
      4 => 
      \Avax\Container\Features\Think\Model\ParameterPrototype::__set_state(array(
         'name' => 'groupStack',
         'type' => 'Avax\\HTTP\\Router\\Routing\\RouteGroupStack',
         'hasDefault' => false,
         'default' => NULL,
         'isVariadic' => false,
         'allowsNull' => false,
         'required' => true,
      )),
      5 => 
      \Avax\Container\Features\Think\Model\ParameterPrototype::__set_state(array(
         'name' => 'registry',
         'type' => 'Avax\\HTTP\\Router\\Support\\RouteRegistry',
         'hasDefault' => false,
         'default' => NULL,
         'isVariadic' => false,
         'allowsNull' => false,
         'required' => true,
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
