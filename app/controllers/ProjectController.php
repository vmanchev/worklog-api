<?php

namespace Worklog\Controllers;

use Phalcon\Mvc\Model\Message as ModelMessage;
use Worklog\Controllers\BaseController;
use Worklog\Models\Project as ProjectModel;
use Worklog\Models\User as UserModel;
use Worklog\Utils\Template;

class ProjectController extends BaseController
{

    public function create()
    {
        $projectData = (array) $this->request->getJsonRawBody();

        // project owner should be the current user
        $projectData['user_id'] = $this->auth->data('user')->id;

        $projectModel = new ProjectModel();
        $status = $projectModel->save($projectData);

        if ($status === true) {
            return $this->successResponse($projectModel, 201);
        }
        return $this->errorResponse($projectModel);
    }

    
}
