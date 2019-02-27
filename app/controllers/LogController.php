<?php

namespace Worklog\Controllers;

use Phalcon\Mvc\Model\Message as ModelMessage;
use Worklog\Controllers\BaseController;
use Worklog\Models\Log as LogModel;

class LogController extends BaseController
{

    public function create()
    {
        $logData = (array) $this->request->getJsonRawBody();

        // make sure time is logged for the current user
        $logData['user_id'] = $this->auth->data('user')->id;

        $logModel = new LogModel();
        $status = $logModel->save($logData);

        if ($status === true) {
            return $this->successResponse($logModel, 201);
        }
        return $this->errorResponse($logModel);
    }

}
