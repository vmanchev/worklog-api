<?php

namespace Worklog\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;
use Phalcon\Validation\Validator\InclusionIn;
use Worklog\Models\Base as BaseModel;

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
}
