<?php

use Phalcon\Db\Adapter\Pdo\Mysql as PdoMysql;
use Phalcon\Security;

$di->setShared('config', function () {
    return include APP_PATH . "/config/config.php";
});

$di->setShared('db', function () use ($app) {
    return new PdoMysql($app->config->database->toArray());
});

$di->setShared('security', function () {
    $security = new Security();
    $security->setWorkFactor(12);
    return $security;
});
