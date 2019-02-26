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
}
