<?php

return new \Phalcon\Config([
    'database' => [
        'adapter'    => 'Mysql',
        'host'       => 'localhost',
        'username'   => getenv('DB_USER'),
        'password'   => getenv('DB_PASS'),
        'dbname'     => getenv('DB_NAME'),
        'charset'    => 'utf8',
    ],

    'jwtAuth' => [
      'secretKey' => getenv('JWT_SECRET'),
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
        'modelsDir'      => APP_PATH . '/models/',
        'migrationsDir'  => APP_PATH . '/migrations/'
    ]
]);
