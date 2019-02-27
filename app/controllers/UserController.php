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

    public function forgotPassword()
    {

        $email = $this->request->getJsonRawBody()->email;

        $userModel = UserModel::findFirstByEmail($email);

        if ($userModel) {

            $userData = $userModel->toArray();
            $userData['plainTextPassword'] = $this->generatePassword();

            $userModel->password = $this->security->hash($userData['plainTextPassword']);

            $userModel->save();

            $this->sendEmail('forgot', 'Your new password', $userData);

            return $this->successResponse(['email' => $email], 200);
        }
        return $this->errorResponse($userModel, 404);

    }

    /**
     * Lookup user by id, using the accessToken data
     */
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

    public function update()
    {
        $userModel = UserModel::findFirst($this->auth->data('user')->id);

        if (!$userModel) {
            $userModel->appendMessage(new ModelMessage('user.notFound'));
            return $this->errorResponse($userModel);
        }

        // get the submitted data
        $userRequest = (array) $this->request->getJsonRawBody();

        // edit own profile
        if ($userRequest['id'] !== $userModel->id) {
            $userModel->appendMessage(new ModelMessage('user.accessDenied'));
            return $this->errorResponse($userModel);
        }

        // if password is submitted, we need to encode it
        if (isset($userRequest['password'])) {
            $userRequest['password'] = $this->security->hash($userRequest['password']);
        }

        $userData = array_merge($userModel->toArray(), $userRequest);

        $result = $userModel->save($userData);

        if ($result) {
          $userData['password'] = null;
          return $this->successResponse($userData);
        }

        return $this->errorResponse($userModel);
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

    /**
     * Send email message
     *
     * @param string $templateName Must match a folter name from 'emails'
     * @param string $subject Subject line for this message
     * @param array $params Template and user data to be used for this message
     */
    private function sendEmail(string $templateName, string $subject, array $params)
    {

        $contentHtml = Template::renderHtml($this->view, $templateName, $params);
        $contentTxt = Template::renderTxt($this->view, $templateName, $params);

        $this->mail->messages()->send($this->config->mailGun->domain, [
            'from' => $this->config->mailGun->defaultSender->name . '<' . $this->config->mailGun->defaultSender->email . '>',
            'to' => $params['firstName'] . ' ' . $params['lastName'] . ' <' . $params['email'] . '>',
            'subject' => $subject,
            'text' => $contentTxt,
            'html' => $contentHtml,
        ]);

    }
}
