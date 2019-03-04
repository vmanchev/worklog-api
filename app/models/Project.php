<?php

namespace Worklog\Models;

use Phalcon\Mvc\Model;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Worklog\Models\Base as BaseModel;
class Project extends BaseModel
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
     * @var string
     * @Column(column="name", type="string", length=255, nullable=false)
     */
    public $name;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->hasMany(
          'id', 
          '\Worklog\Models\Log', 
          'project_id', 
          [
            'alias' => 'Logs'
          ]
        );

        $this->hasMany(
          'id', 
          '\Worklog\Models\ProjectTeam', 
          'project_id', 
          [
            'alias' => 'teamMember'
          ]
        );

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

        return $this->validate($validator);
    }
}
