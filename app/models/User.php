<?php

namespace Worklog\Models;

use Phalcon\Mvc\Model;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

class User extends Model
{
    public function initialize()
    {
      $this->hasMany('id', '\Worklog\Models\Project', 'user_id', ['alias' => 'Projects']);
      $this->hasMany('id', '\Worklog\Models\ProjectTeam', 'user_id', ['alias' => 'ProjectTeams']);
      $this->hasMany('id', '\Worklog\Models\Log', 'user_id', ['alias' => 'Logs']);

        $this->setup(
            array('notNullValidations' => false) //switch off
        );
    }

    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            'email',
            new PresenceOf([
                'message' => 'email.required',
            ])
        );

        $validator->add(
            'email',
            new Email([
                'message' => 'email.invalid',
            ])
        );

        $validator->add(
            'email',
            new Uniqueness([
                'message' => 'email.duplicate',
            ])
        );

        $validator->add(
            'firstName',
            new PresenceOf([
                'field' => 'firstName',
                'message' => 'firstName.required',
            ])
        );

        $validator->add(
            'lastName',
            new PresenceOf([
                'field' => 'lastName',
                'message' => 'lastName.required',
            ])
        );

        return $this->validate($validator);
    }

    public function getFullName() : string {
      return $this->firstName . ' ' . $this->lastName;
    }
}
