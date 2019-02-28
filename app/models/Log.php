<?php

namespace Worklog\Models;

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;

class Log extends Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(column="id", type="integer", length=20, nullable=false)
     */
    public $id;

    /**
     *
     * @var integer
     * @Column(column="project_id", type="integer", length=10, nullable=false)
     */
    public $project_id;

    /**
     *
     * @var integer
     * @Column(column="user_id", type="integer", length=10, nullable=false)
     */
    public $user_id;

    /**
     *
     * @var string
     * @Column(column="start", type="string", nullable=false)
     */
    public $start;

    /**
     *
     * @var string
     * @Column(column="end", type="string", nullable=false)
     */
    public $end;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->belongsTo('project_id', '\Worklog\Models\Project', 'id', ['alias' => 'Project', 'foreignKey' => ['message' => 'project_id.invalid']]);
        $this->belongsTo('user_id', '\Worklog\Models\User', 'id', ['alias' => 'User', 'foreignKey' => ['message' => 'user_id.invalid']]);

        $this->setup(
            array('notNullValidations' => false) //switch off
        );
    }

    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            'user_id',
            new PresenceOf([
                'message' => 'user_id.required',
            ])
        );

        $validator->add(
            'project_id',
            new PresenceOf([
                'message' => 'project_id.required',
            ])
        );

        $validator->add(
            'start',
            new PresenceOf([
                'message' => 'start.required',
            ])
        );

        $validator->add(
            'end',
            new PresenceOf([
                'message' => 'end.required',
            ])
        );

        return $this->validate($validator);
    }

    /**
     * Search for worklogs
     *
     * @params array $params Associative array, which could
     * have 2 possible keys - user_id and/or project_id
     * @return array Array of worklog entries
     */
    public static function searchWithParams(array $params): array
    {

        $conditions = [];
        $bind = [];

        if (isset($params['user_id']) && $params['user_id'] > 0) {
            $conditions[] = 'user_id = :user_id:';
            $bind['user_id'] = $params['user_id'];
        }

        if (isset($params['project_id']) && $params['project_id'] > 0) {
            $conditions[] = 'project_id = :project_id:';
            $bind['project_id'] = $params['project_id'];
        }

        if (!empty($conditions)) {
            $logs = self::find([
                'conditions' => implode(' AND ', $conditions),
                'order' => ['start', 'end'],
                'bind' => $bind,
            ]);
            return $logs->count() ? $logs->toArray() : [];
        }

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
    public function userParticipatesInProjects(int $user_id): array
    {
        $sql = '(select id as project_id from project where user_id = ?)
                union
              (select project_id from log where user_id = ?)';

        $projectIds = (new Resultset(
            null,
            $this,
            $this->getReadConnection()->query($sql, [$user_id, $user_id])
        ))->toArray();

        if (empty($projectIds)) {
            return $projectIds;
        }

        $projectIds = array_map(function ($project) {
            return $project['project_id'];
        }, $projectIds);

        return array_unique($projectIds);
    }

    /**
     * Worklog entries for user own and/or participating projects
     * 
     * @params int $user_id
     * @return array Worklog entries
     */
    public function searchWithinParticipatingProjects(int $user_id)
    {
        $logs = $this->find([
            'conditions' => 'project_id in (' . implode(', ', $this->userParticipatesInProjects($user_id)) . ')',
            'order' => ['project_id', 'start', 'end'],
        ]);

        return $logs->count() ? $logs->toArray() : [];
    }
}
