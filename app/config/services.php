<?php

use Phalcon\Db\Adapter\Pdo\Mysql as PdoMysql;
use Phalcon\Security;
use Phalcon\Mvc\View;
use \Mailgun\Mailgun;

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

$di->setShared('mail', function() use ($app) {
  return Mailgun::create($app->config->mailGun->apiKey);
});

$di->setShared('view', function () use ($app) {

  $view = new View();
  $view->setViewsDir($app->config->application->templatesDir);
  
  return $view;
});