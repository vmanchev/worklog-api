<?php

namespace Worklog\Controllers;

use Worklog\Controllers\BaseController;
use Worklog\Models\Project as ProjectModel;
use Worklog\Models\ProjectTeam as TeamModel;
use Worklog\Models\User as UserModel;
use Worklog\Service\ReportGenerator;

class ProjectController extends BaseController
{

    public function create()
    {
        $projectData = (array) $this->request->getJsonRawBody();

        $teamModel = new TeamModel();
        $teamModel->user_id = $this->auth->data('user')->id;
        $teamModel->role = 'ROLE_ADMIN';

        $projectModel = new ProjectModel();
        $projectModel->name = $projectData['name'];
        $projectModel->teamMember = $teamModel;

        $status = $projectModel->create($projectData);

        if ($status === true) {
            return $this->successResponse($projectModel, 201);
        }
        return $this->errorResponse($projectModel);
    }

    public function report($id)
    {
        if (!TeamModel::isTeamMember($id, $this->auth->data('user')->id)) {
            return $this->errorResponse($project, 403);
        }

        // make sure the current logged user has access to this project
        $project = ProjectModel::findFirst($id);

        if (!$project) {
            return $this->errorResponse(new ProjectModel(), 404);
        }

        $user = UserModel::findFirst($this->auth->data('user')->id);

        $reportGenerator = new ReportGenerator($project, $user);

        $reportGenerator->generate()->download();
    }
}
