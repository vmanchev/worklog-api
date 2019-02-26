<?php

use Phalcon\Db\Adapter\Pdo\Mysql as PdoMysql;
use Phalcon\Security;

$di->setShared('config', function () {
    return include constant('APP_PATH') . "/config/config." . getenv('APPLICATION_ENV') . ".php";
});

$di->setShared('db', function () use ($app) {
    return new PdoMysql($app->config->database->toArray());
});

$di->setShared('security', function () {
    $security = new Security();
    $security->setWorkFactor(12);
    return $security;
});
