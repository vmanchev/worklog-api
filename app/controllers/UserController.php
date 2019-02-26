<?php

namespace Worklog\Controllers;

use Worklog\Controllers\BaseController;
use Worklog\Models\User as UserModel;
use Phalcon\Mvc\Model\Message as ModelMessage;

class UserController extends BaseController {

    public function create() {

        $userData = $this->request->getJsonRawBody();

        $userData->password = $this->security->hash($userData->password);

        $userModel = new UserModel();
        $status = $userModel->save((array) $userData);

        if ($status === true) {
            $userModel->password = null;
            return $this->successResponse($userModel);
        }
        return $this->errorResponse($userModel);
    }

    public function login() {

        $userData = $this->request->getJsonRawBody();

        $userModel = UserModel::findFirstByEmail($userData->email);

        if ($userModel && $this->security->checkHash($userData->password, $userModel->password)) {
            return $this->successResponse([
                'accessToken' => $this->auth->make($userModel->toArray(
                    ['id', 'email', 'firstName', 'lastName']
                ))
            ]);
        }

        $userModel->appendMessage(new ModelMessage('login.invalid'));
        return $this->errorResponse($userModel);
    }
}
