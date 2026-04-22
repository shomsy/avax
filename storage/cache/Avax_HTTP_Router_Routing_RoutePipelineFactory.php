<?php

return \Avax\Container\Features\Think\Model\ServicePrototype::__set_state(array(
   'class' => 'Avax\\HTTP\\Router\\Routing\\RoutePipelineFactory',
   'constructor' => 
  \Avax\Container\Features\Think\Model\MethodPrototype::__set_state(array(
     'name' => '__construct',
     'parameters' => 
    array (
      0 => 
      \Avax\Container\Features\Think\Model\ParameterPrototype::__set_state(array(
         'name' => 'container',
         'type' => 'Avax\\Container\\Features\\Core\\Contracts\\ContainerInterface',
         'hasDefault' => false,
         'default' => NULL,
         'isVariadic' => false,
         'allowsNull' => false,
         'required' => true,
      )),
      1 => 
      \Avax\Container\Features\Think\Model\ParameterPrototype::__set_state(array(
         'name' => 'dispatcher',
         'type' => 'Avax\\HTTP\\Dispatcher\\ControllerDispatcher',
         'hasDefault' => false,
         'default' => NULL,
         'isVariadic' => false,
         'allowsNull' => false,
         'required' => true,
      )),
      2 => 
      \Avax\Container\Features\Think\Model\ParameterPrototype::__set_state(array(
         'name' => 'middlewareResolver',
         'type' => 'Avax\\HTTP\\Middleware\\MiddlewareResolver',
         'hasDefault' => false,
         'default' => NULL,
         'isVariadic' => false,
         'allowsNull' => false,
         'required' => true,
      )),
      3 => 
      \Avax\Container\Features\Think\Model\ParameterPrototype::__set_state(array(
         'name' => 'stageChain',
         'type' => 'Avax\\HTTP\\Router\\Routing\\StageChain',
         'hasDefault' => false,
         'default' => NULL,
         'isVariadic' => false,
         'allowsNull' => false,
         'required' => true,
      )),
      4 => 
      \Avax\Container\Features\Think\Model\ParameterPrototype::__set_state(array(
         'name' => 'logger',
         'type' => 'Psr\\Log\\LoggerInterface',
         'hasDefault' => true,
         'default' => 
        \Psr\Log\NullLogger::__set_state(array(
        )),
         'isVariadic' => false,
         'allowsNull' => false,
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
