<?php

namespace Worklog\Controllers;

use Phalcon\Mvc\Model\Message as ModelMessage;
use Worklog\Controllers\BaseController;
use Worklog\Models\User as UserModel;

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

        return $this->errorResponse(['email.invalid', 'password.invalid']);
    }

    public function forgotPassword()
    {

        $email = $this->request->getJsonRawBody()->email;

        $userModel = UserModel::findFirstByEmail($email);

        if (!$userModel) {
            return $this->errorResponse(['email.notFound'], 404);
        }

        $userData = $userModel->toArray();
        $userData['plainTextPassword'] = $this->generatePassword();

        $userModel->password = $this->security->hash($userData['plainTextPassword']);

        $userModel->save();

        $this->sendEmail('forgot', 'Your new password', $userData);

        return $this->successResponse(['email' => $email], 200);

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

        return $this->errorResponse(['user.notFound'], 404);
    }

    public function update()
    {
        $userModel = UserModel::findFirst($this->auth->data('user')->id);

        if (!$userModel) {
          return $this->errorResponse(['user.notFound'], 404);
        }

        // get the submitted data
        $userRequest = (array) $this->request->getJsonRawBody();

        // edit own profile
        if ($userRequest['id'] !== $userModel->id) {
          return $this->errorResponse(['user_id.accessDenied'], 403);
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
            unset($user->password);
            return $this->successResponse($user);
        }

        return $this->errorResponse(['id.notFound'], 404);
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

        $responseCode = strlen($result) ? 200 : 204;

        return $this->successResponse($result, $responseCode);
    }

}
