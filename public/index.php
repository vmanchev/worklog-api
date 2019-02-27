<?php

use Phalcon\Http\Response;
use Phalcon\Mvc\Micro;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Model\Message as ModelMessage;
use Worklog\Models\User as UserModel;
use Worklog\Controllers\ProjectController;
use Worklog\Controllers\UserController;
use Dmkit\Phalcon\Auth\Middleware\Micro as AuthMicro;
use Phalcon\Mvc\Micro\Collection as MicroCollection;

error_reporting(E_ALL);

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

try {

  $di = new FactoryDefault();
  $app = new Micro($di);

  include '../vendor/autoload.php';
  include APP_PATH . '/config/loader.php';
  include APP_PATH . '/config/services.php';

  $userCollection = new MicroCollection();
  $userCollection->setHandler(new UserController());
  $userCollection->setPrefix('/user');

  $userCollection->post('/', 'create');
  $userCollection->put('/', 'update');
  $userCollection->post('/login', 'login');
  $userCollection->get('/me', 'getUserByAuthToken');
  $userCollection->post('/forgot', 'forgotPassword');

  $app->mount($userCollection);

  $projectCollection = new MicroCollection();
  $projectCollection->setHandler(new ProjectController());
  $projectCollection->setPrefix('/project');

  $projectCollection->post('/', 'create');
  
  $app->mount($projectCollection);  

  $app->get(
      '/',
      function () {
          $response = new Response();
          $response->setJsonContent([
              'message' => 'hello world',
          ]);
          return $response;
      }
  );
  $auth = new AuthMicro($app);
  $app->notFound(function () use ($app) {
      $app->response->setStatusCode(404, "Not Found")->sendHeaders();
  });
  $app->handle();


} catch (\Exception $e) {
      echo $e->getMessage() . '<br>';
      echo '<pre>' . $e->getTraceAsString() . '</pre>';
}
