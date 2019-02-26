<?php

namespace Worklog\Controllers;

use Phalcon\Mvc\Controller;

class BaseController extends Controller {

    function successResponse($model): \Phalcon\Http\Response
    {    
        $this->response->setJsonContent(
            [
                'status' => 'OK',
                'data' => array_filter(
                    ($model instanceof \Phalcon\Mvc\Model) ? $model->toArray() : $model
                ),
            ]
        );
    
        return $this->response;
    }
    
    function errorResponse(\Phalcon\Mvc\Model $model): \Phalcon\Http\Response
    {
        // Change the HTTP status
        $this->response->setStatusCode(409, 'Conflict');
    
        // Send errors to the client
        $errors = [];
    
        foreach ($model->getMessages() as $message) {
            $errors[] = $message->getMessage();
        }
    
        $this->response->setJsonContent(
            [
                'status' => 'ERROR',
                'errors' => $errors,
            ]
        );
    
        return $this->response;
    }

}
