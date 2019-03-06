<?php

namespace Worklog\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\InclusionIn;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;
use Phalcon\Validation\Validator\Callback;
use Worklog\Models\Base as BaseModel;
use Worklog\Models\User as UserModel;

class ProjectTeam extends BaseModel
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
     * @Column(column="role", type="string", length=50, nullable=false)
     */
    public $role;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->belongsTo('project_id', '\Worklog\Models\Project', 'id', ['alias' => 'Project']);
        $this->belongsTo('user_id', '\Worklog\Models\User', 'id', ['alias' => 'User']);
    }

    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            'project_id',
            new PresenceOf([
                'message' => 'project_id.required',
            ])
        );

        $validator->add(
            'user_id',
            new PresenceOf([
                'message' => 'user_id.required',
            ])
        );

        $validator->add(
            'user_id',
            new Callback([
                'message' => 'user_id.invalid',
                'callback' => function($model) {
                  return !!UserModel::findFirst($model->user_id);
                }
            ])
        );

        $validator->add(
            ['project_id', 'user_id'],
            new Uniqueness([
                'message' => 'user_id.exists',
            ])
        );

        $validator->add(
            'role',
            new PresenceOf([
                'message' => 'role.required',
            ])
        );

        $validator->add(
            'role',
            new InclusionIn([
                'domain' => ['ROLE_USER', 'ROLE_MANAGER', 'ROLE_ADMIN'],
                'message' => 'role.invalid',
            ])
        );

        return $this->validate($validator);
    }

    public static function getTeamMember(int $project_id, int $user_id)
    {
        return self::findFirst([
            'conditions' => 'project_id = :project_id: AND user_id = :user_id:',
            'bind' => [
                'project_id' => $project_id,
                'user_id' => $user_id,
            ],
        ]);
    }

    public static function isTeamMember(int $project_id, int $user_id): bool
    {
        return !!self::findFirst([
            'conditions' => 'project_id = :project_id: AND user_id = :user_id:',
            'bind' => [
                'project_id' => $project_id,
                'user_id' => $user_id,
            ],
        ]);
    }

    public static function isTeamManager(int $project_id, int $user_id): bool
    {
        return self::isTeamMemberByRole($project_id, $user_id, 'ROLE_MANAGER');
    }

    public static function isTeamAdmin(int $project_id, int $user_id): bool
    {
        return self::isTeamMemberByRole($project_id, $user_id, 'ROLE_ADMIN');
    }

    private static function isTeamMemberByRole(int $project_id, int $user_id, string $role): bool
    {
        return !!self::findFirst([
            'conditions' => 'project_id = :project_id: AND user_id = :user_id: AND role = :role:',
            'bind' => [
                'project_id' => $project_id,
                'user_id' => $user_id,
                'role' => $role,
            ],
        ]);
    }
}
