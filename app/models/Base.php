<?php

namespace Worklog\Models;

use Phalcon\Mvc\Model;
use Worklog\Models\Log as LogModel;
use Worklog\Models\Project as ProjectModel;

class Base extends Model
{
    /**
     * Project IDs, where user is project owner
     * 
     * @return array Array of project IDs or empty array
     */
    public function getProjectIdsForOwner(int $user_id): array
    {
        return array_map(function (array $project) {
            return $project['id'];
        }, ProjectModel::findByUserId($user_id)->toArray());
    }

    /**
     * Project IDs, where user has logged any time
     * 
     * @return array Array of project IDs or empty array
     */
    public function getProjectIdsForUser(int $user_id): array
    {
        return array_unique(array_map(function (array $project) {
            return $project['project_id'];
        }, LogModel::findByUserId($user_id)->toArray()));
    }

    /**
     * IDs of projects, current user participates in
     *
     * When request for GET /log arrives, we need to find the projects
     * in which the current user participates in, either as user who
     * logs time or as project administrator.
     *
     * @params int $user_id
     * @return array Array of project ids
     */
    public function getProjectIdsForParticipant(int $user_id): array
    {
        return array_merge(
            $this->getProjectIdsForOwner($user_id),
            $this->getProjectIdsForUser($user_id)
        );
    }
}
