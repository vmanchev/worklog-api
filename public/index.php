<?php

use Dmkit\Phalcon\Auth\Middleware\Micro as AuthMicro;
use Phalcon\Di\FactoryDefault;
use Phalcon\Http\Response;
use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Micro\Collection as MicroCollection;
use Worklog\Controllers\LogController;
use Worklog\Controllers\ProjectController;
use Worklog\Controllers\TeamController;
use Worklog\Controllers\UserController;

error_reporting(E_ALL);

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

try {

    $di = new FactoryDefault();
    $app = new Micro($di);

    include '../vendor/autoload.php';
    include APP_PATH . '/config/loader.php';
    include APP_PATH . '/config/services.php';

    ###################################################################
    $userCollection = new MicroCollection();
    $userCollection->setHandler(new UserController());
    $userCollection->setPrefix('/user');

    $userCollection->post('/', 'create');
    $userCollection->put('/', 'update');
    $userCollection->post('/login', 'login');
    $userCollection->get('/me', 'getUserByAuthToken');
    $userCollection->get('/{id:[0-9]+}', 'profile');
    $userCollection->get('/', 'search');
    $userCollection->post('/forgot', 'forgotPassword');

    $app->mount($userCollection);

    ###################################################################
    $projectCollection = new MicroCollection();
    $projectCollection->setHandler(new ProjectController());
    $projectCollection->setPrefix('/project');

    $projectCollection->post('/', 'create');
    $projectCollection->get('/', 'search');
    $projectCollection->get('/{id}/report', 'report');

    $app->mount($projectCollection);

    ###################################################################
    $teamCollection = new MicroCollection();
    $teamCollection->setHandler(new TeamController());
    $teamCollection->setPrefix('/project/{project_id}/team');

    $teamCollection->post('/', 'addNewTeamMember');
    $teamCollection->get('/', 'search');
    $teamCollection->put('/{user_id}', 'update');
    $teamCollection->delete('/{user_id}', 'delete');

    $app->mount($teamCollection);    

    ###################################################################

    $logCollection = new MicroCollection();
    $logCollection->setHandler(new LogController());
    $logCollection->setPrefix('/log');

    $logCollection->post('/', 'create');
    $logCollection->get('/', 'search');
    $logCollection->delete('/{id}', 'delete');
    $logCollection->put('/{id}', 'update');

    $app->mount($logCollection);
    ###################################################################

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
