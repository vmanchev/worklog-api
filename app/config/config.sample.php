<?php

return new \Phalcon\Config([
    'database' => [
        'adapter'    => 'Mysql',
        'host'       => '',
        'username'   => '',
        'password'   => '',
        'dbname'     => '',
        'charset'    => 'utf8',
    ],

    'jwtAuth' => [
      'secretKey' => '',
      'payload' => [
          'exp' => 1440,
          'iss' => 'phalcon-jwt-auth',
      ],
      'ignoreUri' => [
          '/',
          '/user:POST',
          '/user/login'
      ],
    ],

    'application' => [
        'modelsDir'      => constant('APP_PATH') . '/models/',
        'migrationsDir'  => constant('APP_PATH') . '/migrations/'
    ]
]);
