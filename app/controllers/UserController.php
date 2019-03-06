<?php

namespace Worklog\Controllers;

use Phalcon\Mvc\Model\Message as ModelMessage;
use Worklog\Controllers\BaseController;

class UserController extends BaseController
{

    public function create()
    {
        $userData = (array) $this->request->getJsonRawBody();

        $userModel = $this->register($userData);

        if ($userModel->validationHasFailed()) {
            return $this->errorResponse($userModel);
        }

        return $this->successResponse($userModel, 201);
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

        return $this->errorResponse(['login.invalid']);
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
     * Get user by id
     *
     * @param int $id User id
     */
    public function profile(int $id)
    {

        $user = UserModel::findFirst($id);

        if ($user) {
            return $this->successResponse($user);
        }

        return $this->errorResponse(null, 404);
    }

    /**
     * Search users by keyword
     */
    public function search()
    {

        $keyword = $this->request->getQuery('keyword', 'alnum', null);

        if (!$keyword || strlen($keyword) < 3) {
            return $this->errorResponse(['keyword.minLength']);
        }

        $result = UserModel::find([
            'conditions' => 'email like :email:',
            'bind' => [
                'email' => '%' . $keyword . '%',
            ],
        ])->toArray();

        if (!$result) {
            return $this->errorResponse($result);
        }

        $responseCode = strlen($result) ? 200 : 404;

        return $this->successResponse($result, $responseCode);
    }

}
