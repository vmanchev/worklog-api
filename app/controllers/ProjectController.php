<?php

namespace Worklog\Controllers;

use Worklog\Controllers\BaseController;
use Worklog\Models\Project as ProjectModel;
use Worklog\Models\User as UserModel;
use Worklog\Service\ReportGenerator;

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

    public function report($id)
    {
        // make sure the current logged user has access to this project
        $project = ProjectModel::findFirst($id);

        if (!$project) {
            return $this->errorResponse(new ProjectModel(), 404);
        }

        if (!in_array($project->id, $project->getProjectIdsForParticipant($this->auth->data('user')->id))) {
            return $this->errorResponse($project, 403);
        }

        $user = UserModel::findFirst($this->auth->data('user')->id);

        $reportGenerator = new ReportGenerator($project, $user);

        $reportGenerator->generate()->download();
    }
}
