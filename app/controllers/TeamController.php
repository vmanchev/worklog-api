<?php

namespace Worklog\Controllers;

use Worklog\Controllers\BaseController;
use Worklog\Models\Project as ProjectModel;
use Worklog\Models\ProjectTeam as TeamModel;
use Worklog\Models\User as UserModel;

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
    public function addNewTeamMember(int $project_id)
    {
        if (!TeamModel::isTeamAdmin($project_id, $this->auth->data('user')->id)) {
            return $this->errorResponse(null, 403);
        }

        $memberData = (array) $this->request->getJsonRawBody();

        if (!$memberData['user_id']) {

            if (!$memberData['email'] && !$memberData['firstName'] && !$memberData['lastName']) {
                return $this->errorResponse([
                    'email.required',
                    'firstName.required',
                    'lastName.required',
                ], 409);
            }

            $memberData['adminNames'] = UserModel::findFirst($this->auth->data('user')->id)->getFullName();
            $memberData['projectName'] = ProjectModel::findFirst($project_id)->name;

            $userModel = $this->register($memberData, true);

            if ($userModel->validationHasFailed()) {
                return $this->errorResponse($userModel, 409);
            }

            $memberData['user_id'] = $userModel->id;
        }

        $memberData['project_id'] = $project_id;

        $teamModel = new TeamModel();
        $status = $teamModel->save($memberData);

        if ($status === true) {
            $this->sendEmailToTeamMember($project_id, $memberData['user_id'], 'add-team-member');
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
            $this->sendEmailToTeamMember($project_id, $user_id, 'delete-team-member');
            return $this->successResponse();
        }

        return $this->errorResponse($teamMember);
    }

    private function sendEmailToTeamMember(int $project_id, int $user_id, string $template)
    {

        $projectModel = ProjectModel::findFirst($project_id);
        $userModel = UserModel::findFirst($user_id);

        $this->sendEmail(
            $template,
            $this->getSubjectByTemplate($template, $projectModel),
            [
                'projectName' => $projectModel->name,
                'firstName' => $userModel->firstName,
                'lastName' => $userModel->lastName,
                'email' => $userModel->email,
                'adminNames' => UserModel::findFirst($this->auth->data('user')->id)->getFullName(),
                'dateTimeNow' => date('Y-m-d H:i:s'),
            ]);

    }

    /**
     * Get email subject, based on the template name
     */
    private function getSubjectByTemplate(string $template, ProjectModel $projectModel): string
    {
        $templateSubjectMap = [
            'add-team-member' => 'Welcome to ' . $projectModel->name,
            'delete-team-member' => 'Say goodbye to ' . $projectModel->name,
        ];

        return $templateSubjectMap[$template];
    }
}
