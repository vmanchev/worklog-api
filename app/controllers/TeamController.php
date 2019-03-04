<?php

namespace Worklog\Controllers;

use Worklog\Controllers\BaseController;
use Worklog\Models\ProjectTeam as TeamModel;

class TeamController extends BaseController
{
    /**
     * List of team members for a selected project
     */
    public function search(int $project_id)
    {
        if (!TeamModel::isTeamMember($project_id, $this->auth->data('user')->id)) {
            return $this->errorResponse(null, 403);
        }

        return $this->successResponse(
            TeamModel::findByProjectId($project_id)->toArray(),
            200
        );
    }

    /**
     * Add new member to a team
     */
    public function create(int $project_id)
    {
        if (!TeamModel::isTeamAdmin($project_id, $this->auth->data('user')->id)) {
            return $this->errorResponse(null, 403);
        }

        $memberData = (array) $this->request->getJsonRawBody();
        $memberData['project_id'] = $project_id;

        $teamModel = new TeamModel();
        $status = $teamModel->save($memberData);

        if ($status === true) {
            return $this->successResponse($teamModel, 201);
        }
        return $this->errorResponse($teamModel);
    }

    public function update(int $project_id, int $user_id)
    {
        if (!TeamModel::isTeamAdmin($project_id, $this->auth->data('user')->id)) {
            return $this->errorResponse(null, 403);
        }

        $memberData = (array) $this->request->getJsonRawBody();

        $teamModel = TeamModel::getTeamMember($project_id, $user_id);

        if (!$teamModel) {
            return $this->errorResponse(null, 404);
        }

        $status = $teamModel->update($memberData, ['role']);

        if ($status === true) {
            return $this->successResponse($teamModel, 200);
        }
        return $this->errorResponse($teamModel);
    }

    public function delete(int $project_id, int $user_id)
    {
        if (!TeamModel::isTeamAdmin($project_id, $this->auth->data('user')->id)) {
            return $this->errorResponse(null, 403);
        }

        $teamMember = TeamModel::getTeamMember($project_id, $user_id);

        if (!$teamMember) {
            return $this->errorResponse(['project_id.invalid', 'user_id.invalid'], 404);
        }

        if ($teamMember->delete()) {
            return $this->successResponse();
        }

        return $this->errorResponse($teamMember);
    }
}
