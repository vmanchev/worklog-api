<?php

use Phalcon\Loader;

$loader = new Loader();

$loader->registerNamespaces(
    [
        'Worklog\Models' => APP_PATH . '/models/',
        'Worklog\Validators' => APP_PATH . '/validators/',
        'Worklog\Controllers' => APP_PATH . '/controllers/',
        'Worklog\Service' => APP_PATH . '/services/',
        'Worklog\Utils' => APP_PATH . '/utils/'
    ]
);

$loader->register();