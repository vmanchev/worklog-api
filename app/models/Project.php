<?php

namespace Worklog\Models;

use Phalcon\Mvc\Model;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;

class Project extends Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(column="id", type="integer", length=10, nullable=false)
     */
    public $id;

    /**
     *
     * @var integer
     * @Column(column="user_id", type="integer", length=10, nullable=false)
     */
    public $user_id;

    /**
     *
     * @var string
     * @Column(column="name", type="string", length=255, nullable=false)
     */
    public $name;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->belongsTo('user_id', '\Worklog\Models\User', 'id', [
            'alias' => 'User',
            'foreignKey' => [
                'message' => 'user_id.invalid',
            ],
        ]);

        $this->hasMany('id', '\Worklog\Models\Log', 'project_id', ['alias' => 'Logs']);

        $this->setup(
            array('notNullValidations' => false) //switch off
        );
    }

    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            'name',
            new PresenceOf([
                'message' => 'name.required',
            ])
        );

        $validator->add(
            'user_id',
            new PresenceOf([
                'message' => 'user_id.required',
            ])
        );

        return $this->validate($validator);
    }
}
