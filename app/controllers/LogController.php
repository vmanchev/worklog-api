<?php

namespace Worklog\Controllers;

use Worklog\Controllers\BaseController;
use Worklog\Models\Log as LogModel;
use Worklog\Models\ProjectTeam as TeamModel;

class LogController extends BaseController
{

    public function create()
    {
        $logData = (array) $this->request->getJsonRawBody();

        if (!TeamModel::isTeamMember($logData['project_id'], $this->auth->data('user')->id)) {
            return $this->errorResponse(null, 403);
        }

        // make sure time is logged for the current user
        $logData['user_id'] = $this->auth->data('user')->id;

        $logModel = new LogModel();
        $status = $logModel->save($logData);

        if ($status === true) {
            return $this->successResponse($logModel, 201);
        }
        return $this->errorResponse($logModel);
    }

    /**
     * Search logs by project and/or user
     *
     * When no user_id is provided, logs for all users will be returned.
     * When user_id is provided, only the related logs will be returned.
     *
     * When project_id is provided, only logs for the related project will be returned.
     * When project_id is not provided, only logs for projects where current user
     * paraticipates (either as user or admin), will be returned.
     */
    public function search()
    {
        $queryParams = $this->request->getQuery();

        // remove _url which is always the first element
        array_shift($queryParams);

        if (!empty($queryParams)) {
            $queryParams['auth_user_id'] = $this->auth->data('user')->id;
            return $this->successResponse((new LogModel())->searchWithParams($queryParams));
        }

        // no query parameters, return results for authenticated user only
        return $this->successResponse(LogModel::findByUserId($this->auth->data('user')->id));
    }

    /**
     * Deletes a log entry
     *
     * It should either the log entry author or project admin
     *
     * @params int $id
     */
    public function delete(int $id)
    {

        $log = LogModel::findFirst($id);

        // invalid id
        if (!$log) {
            return $this->errorResponse(new LogModel(), 404);
        }

        // current user is worklog author or current user is team admin
        if ($log->user_id === $this->auth->data('user')->id || TeamModel::isTeamAdmin($log->project_id, $this->auth->data('user')->id)) {
            return $this->deleteLog($log);
        }

        return $this->errorResponse($log, 403);
    }

    public function update(int $id)
    {
        $logModel = LogModel::findFirst($id);

        if (!$logModel) {
            return $this->errorResponse(new LogModel(), 404);
        }

        if ($logModel->user_id === $this->auth->data('user')->id || TeamModel::isTeamAdmin($logModel->project_id, $this->auth->data('user')->id)) {
            $logData = (array) $this->request->getJsonRawBody();
            $logData['id'] = $id;

            if ($logModel->update($logData, ['start', 'end', 'description'])) {
                return $this->successResponse($logModel, 200);
            }

            return $this->errorResponse($logModel, 200);
        }

        return $this->errorResponse(null, 403);
    }

    /**
     * Perform the deletion and return relevant response
     */
    private function deleteLog(\Worklog\Models\Log $log)
    {
        if ($log->delete()) {
            return $this->successResponse();
        }

        return $this->errorResponse($log);
    }
}
