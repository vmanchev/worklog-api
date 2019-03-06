<?php

namespace Worklog\Controllers;

use Phalcon\Mvc\Controller;
use Worklog\Utils\Template as EmailTemplate;
use Worklog\Models\User as UserModel;

class BaseController extends Controller
{

    public function successResponse($model = null, int $httpCode = 200): \Phalcon\Http\Response
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

    public function errorResponse($model, int $httpCode = 409): \Phalcon\Http\Response
    {
        // Change the HTTP status
        $this->response->setStatusCode($httpCode);

        // Send errors to the client
        $errors = [];

        if ($model instanceof \Phalcon\Mvc\Model) {
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

    /**
     * New user registration
     *
     * Try to register a new user. In case of success, send the user password to
     * the provided email address. In case of error, return false.
     */
    public function register(array $userData, bool $indirect = false): UserModel
    {
        $userData['plainTextPassword'] = $this->generatePassword();
        $userData['password'] = $this->security->hash($userData['plainTextPassword']);

        $userModel = new UserModel();
        $status = $userModel->save($userData);

        if ($status === true) {

          $template = $indirect ? 'register-indirect' : 'register';

            $this->sendEmail($template, 'Your password', $userData);

            $userModel->password = null;

            return $userModel;
        }

        return $userModel;
    }

    /**
     * Password generator
     *
     * @return string
     * @see https://docs.phalconphp.com/3.4/en/api/Phalcon_Security_Random
     */
    private function generatePassword(): string
    {
        return (new \Phalcon\Security\Random())->base58();
    }
}
