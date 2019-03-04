<?php

namespace Worklog\Controllers;

use Worklog\Controllers\BaseController;
use Worklog\Models\ProjectTeam as TeamModel;

class TeamController extends BaseController
{
    public function search(int $project_id)
    {
        return $this->successResponse(
            TeamModel::findByProjectId($project_id)->toArray(),
            200
        );
    }

    public function create(int $project_id)
    {
        $memberData = (array) $this->request->getJsonRawBody();
        $memberData['project_id'] = $project_id;

        $teamModel = new TeamModel();
        $status = $teamModel->save($memberData);

        if ($status === true) {
            return $this->successResponse($teamModel, 201);
        }
        return $this->errorResponse($teamModel);
    }

    public function profile(int $project_id, int $user_id)
    {
        $teamMember = $this->getTeamMember($project_id, $user_id);

        if ($teamMember) {
            return $this->successResponse($teamMember, 200);
        }

        return $this->errorResponse($teamMember);
    }

    public function update(int $project_id, int $user_id)
    {
        $memberData = (array) $this->request->getJsonRawBody();

        $teamModel = $this->getTeamMember($project_id, $user_id);

        if (!$teamModel) {
            return $this->errorResponse($teamMember, 404);
        }

        $status = $teamModel->update($memberData, ['role']);

        if ($status === true) {
            return $this->successResponse($teamModel, 200);
        }
        return $this->errorResponse($teamModel);
    }

    public function delete(int $project_id, int $user_id)
    {
        $teamMember = $this->getTeamMember($project_id, $user_id);

        if (!$teamMember) {
            return $this->errorResponse(['project_id.invalid', 'user_id.invalid'], 404);
        }

        if ($teamMember->delete()) {
            return $this->successResponse();
        }

        return $this->errorResponse($teamMember);
    }

    private function getTeamMember(int $project_id, int $user_id)
    {
        return TeamModel::findFirst([
            'conditions' => 'project_id = :project_id: and user_id = :user_id:',
            'bind' => [
                'project_id' => $project_id,
                'user_id' => $user_id,
            ],
        ]);
    }
}
