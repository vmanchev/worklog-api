<?php

namespace Worklog\Controllers;

use Phalcon\Mvc\Model\Message as ModelMessage;
use Worklog\Controllers\BaseController;
use Worklog\Models\User as UserModel;
use Worklog\Utils\Template;

class UserController extends BaseController
{

    public function create()
    {

        $userData = (array) $this->request->getJsonRawBody();

        $userData['plainTextPassword'] = $this->generatePassword();

        $userData['password'] = $this->security->hash($userData['plainTextPassword']);

        $userModel = new UserModel();
        $status = $userModel->save($userData);

        if ($status === true) {

            $this->sendEmail('register', 'Your password', $userData);

            $userModel->password = null;
            return $this->successResponse($userModel, 201);
        }
        return $this->errorResponse($userModel);
    }

    public function login()
    {

        $userData = $this->request->getJsonRawBody();

        $userModel = UserModel::findFirstByEmail($userData->email);

        if ($userModel && $this->security->checkHash($userData->password, $userModel->password)) {
            return $this->successResponse([
                'accessToken' => $this->auth->make(['user' => ['id' => $userModel->id]]),
            ]);
        }

        $userModel->appendMessage(new ModelMessage('login.invalid'));
        return $this->errorResponse($userModel);
    }

    public function getUserByAuthToken()
    {
        $userModel = UserModel::findFirst($this->auth->data('user')->id);

        if ($userModel) {
            $userData = $userModel->toArray();
            $userData['password'] = null;
            return $this->successResponse($userData);
        }

        $userModel->appendMessage(new ModelMessage('user.notFound'));
        return $this->errorResponse($userModel);
    }

    private function generatePassword(): string {
      return (new \Phalcon\Security\Random())->base58();
    }

    private function sendEmail(string $templateName, string $subject, array $userData) {
      
      $contentHtml = Template::renderHtml($this->view, 'register', $userData);
      $contentTxt = Template::renderTxt($this->view, 'register', $userData);

      $this->mail->messages()->send($this->config->mailGun->domain, [
          'from' => $this->config->mailGun->defaultSender->name . '<' . $this->config->mailGun->defaultSender->email . '>',
          'to' => $userData['firstName'] . ' ' . $userData['lastName'] . ' <' . $userData['email'] . '>',
          'subject' => $subject,
          'text' => $contentTxt,
          'html' => $contentHtml,
      ]);

    }
}
