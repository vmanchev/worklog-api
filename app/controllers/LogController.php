<?php

namespace Worklog\Controllers;

use Worklog\Controllers\BaseController;
use Worklog\Models\Log as LogModel;
use Worklog\Models\Project as ProjectModel;

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
        
        if(!empty($queryParams)) {
          return $this->successResponse(LogModel::searchWithParams($queryParams));
        }

        return $this->successResponse(
          (new LogModel())->searchWithinParticipatingProjects($this->auth->data('user')->id)
        );
    }

    /**
     * Deletes a log entry
     * 
     * It should either the log entry author or project admin
     * 
     * @params int $id
     */
    public function delete(int $id) {

      $log = LogModel::findFirst($id);

      // invalid id
      if (!$log) {
        return $this->errorResponse(new LogModel(), 404);
      }
      
      // current user is worklog author
      if ($log->user_id === $this->auth->data('user')->id) {
        return $this->deleteLog($log);
      }

      // find the project owner
      $project = ProjectModel::findFirst($log->project_id);

      // delete only if current user is project owner
      if ($project->user_id === $this->auth->data('user')->id) {
        return $this->deleteLog($log);
      }

      return $this->errorResponse($log);
    }

    /**
     * Perform the deletion and return relevant response
     */
    private function deleteLog(\Worklog\Models\Log $log) {
      if ($log->delete()) {
        return $this->successResponse();
      }

      return $this->errorResponse($log);
    }
}
