<?php

namespace Worklog\Models;

use Phalcon\Mvc\Model;
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
}
