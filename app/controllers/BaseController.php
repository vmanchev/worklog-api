<?php

namespace Worklog\Controllers;

use Phalcon\Mvc\Controller;
use Worklog\Utils\Template as EmailTemplate;

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

    /**
     * Send email message
     *
     * @param string $templateName Must match a folter name from 'emails'
     * @param string $subject Subject line for this message
     * @param array $params Template and user data to be used for this message
     */
    public function sendEmail(string $templateName, string $subject, array $params)
    {

        $contentHtml = EmailTemplate::renderHtml($this->view, $templateName, $params);
        $contentTxt = EmailTemplate::renderTxt($this->view, $templateName, $params);

        $this->mail->messages()->send($this->config->mailGun->domain, [
            'from' => $this->config->mailGun->defaultSender->name . '<' . $this->config->mailGun->defaultSender->email . '>',
            'to' => $params['firstName'] . ' ' . $params['lastName'] . ' <' . $params['email'] . '>',
            'subject' => $subject,
            'text' => $contentTxt,
            'html' => $contentHtml,
        ]);

    }

}
