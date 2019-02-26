<?php

use Phalcon\Loader;

$loader = new Loader();

$loader->registerNamespaces(
    [
        'Worklog\Models' => APP_PATH . '/models/',
        'Worklog\Controllers' => APP_PATH . '/controllers/'
    ]
);

$loader->register();