<?php

namespace Worklog\Controllers;

use Phalcon\Mvc\Controller;

class BaseController extends Controller {

    function successResponse($model = null, int $httpCode = 200): \Phalcon\Http\Response
    {    
        $this->response->setStatusCode($httpCode);
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
    
    function errorResponse($model, int $httpCode = 409): \Phalcon\Http\Response
    {
        // Change the HTTP status
        $this->response->setStatusCode($httpCode);
    
        // Send errors to the client
        $errors = [];
    
        if($model instanceof \Phalcon\Mvc\Model) {
          foreach ($model->getMessages() as $message) {
              $errors[] = $message->getMessage();
          }
        } else {
          $errors = $model;
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
