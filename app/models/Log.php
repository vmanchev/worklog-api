<?php

namespace Worklog\Models;

use Phalcon\Mvc\Model;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Date as DateValidator;
use Phalcon\Validation\Validator\PresenceOf;
use Worklog\Models\Base as BaseModel;
use Worklog\Validators\AccessEditLog;
use Worklog\Validators\EndDate;

class Log extends BaseModel
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
     *
     * @var int
     * @Column(column="elapsed", type="integer", nullable=false)
     */
    public $elapsed;

    /**
     * @var string
     * @Column(column="description", type="varchar", nullable=true)
     */
    public $description;

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

    public function beforeValidation()
    {
        $this->elapsed = strtotime($this->end) - strtotime($this->start);
    }

    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            'user_id',
            new AccessEditLog([
                'message' => 'user_id.accessDenied',
            ])
        );

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
            'start',
            new DateValidator([
                'format' => 'Y-m-d H:i:s',
                'message' => 'start.format',
            ])
        );

        $validator->add(
            'end',
            new PresenceOf([
                'message' => 'end.required',
            ])
        );

        $validator->add(
            'end',
            new DateValidator([
                'format' => 'Y-m-d H:i:s',
                'message' => 'end.format',
            ])
        );

        $validator->add(
            'end',
            new EndDate()
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
    public function searchWithParams(array $params): array
    {

        $conditions = [];
        $bind = [];

        $defaultParams = ['project_id' => null, 'user_id' => null];
        $params = array_merge($defaultParams, $params);
        $params = array_map('intval', $params);

        if ($params['project_id'] > 0 && $params['user_id'] > 0) {
          $query = $this->modelsManager->createQuery(
            'select * from Worklog\Models\Log l 
              where 
                l.user_id = :user_id: and 
                l.project_id = :project_id: and 
                l.project_id in (
                  select pt.project_id from Worklog\Models\ProjectTeam pt 
                    where pt.user_id = :auth_user_id:
                  )'
          );
        } elseif ($params['project_id'] > 0 && $params['user_id'] === 0) {
          $query = $this->modelsManager->createQuery(
            'select * from Worklog\Models\Log l 
              where 
                l.project_id = :project_id: and 
                l.project_id in (
                  select pt.project_id from Worklog\Models\ProjectTeam pt 
                    where pt.user_id = :auth_user_id:
                  )'
          );
        } elseif ($params['project_id'] === 0 && $params['user_id'] > 0) {
          $query = $this->modelsManager->createQuery(
            'select * from Worklog\Models\Log l 
              where 
                l.user_id = :user_id: and 
                l.project_id in (
                  select pt.project_id from Worklog\Models\ProjectTeam pt 
                    where pt.user_id = :auth_user_id:
                  )'
          );
        }

        return $query->execute(array_filter($params))->toArray();
    }

    /**
     * Worklog entries for user own and/or participating projects
     *
     * @params int $user_id
     * @return array Worklog entries
     */
    public function searchWithinParticipatingProjects(int $user_id): array
    {
        $logs = $this->find([
            'conditions' => 'project_id in (' . implode(', ', $this->getProjectIdsForParticipant($user_id)) . ')',
            'order' => ['project_id', 'start', 'end'],
        ]);

        return $logs->count() ? $logs->toArray() : [];
    }

    public static function displayTime(int $time): string
    {

        $hours = floor($time / 3600);
        $minutes = floor(($time / 60) % 60);
        return $hours . ':' . $minutes;
    }
}
