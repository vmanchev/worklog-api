<?php

namespace Worklog\Controllers;

use Phalcon\Mvc\Model\Message as ModelMessage;
use Worklog\Controllers\BaseController;
use Worklog\Models\User as UserModel;

class UserController extends BaseController
{

    public function create()
    {

        $userData = $this->request->getJsonRawBody();

        $userData->password = $this->security->hash($userData->password);

        $userModel = new UserModel();
        $status = $userModel->save((array) $userData);

        if ($status === true) {
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
}
