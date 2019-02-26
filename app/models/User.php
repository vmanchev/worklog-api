<?php

namespace Worklog\Models;

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Message;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;
use Phalcon\Validation\Validator\Email;

class User extends Model
{
    public function validation()
    {
        $validator = new Validation();
    
        $validator->add(
            'email',
            new PresenceOf([
                'field'   => 'email',
                'message' => 'email.required',
            ])
        );

        $validator->add(
            'email',
            new Email([
                'field'   => 'email',
                'message' => 'email.invalid',
            ])
        );

        $validator->add(
            'email',
            new Uniqueness([
                'field'   => 'email',
                'message' => 'email.duplicate',
            ])
        );

        $validator->add(
            'firstName',
            new PresenceOf([
                'field'   => 'firstName',
                'message' => 'firstName.required',
            ])
        );

        $validator->add(
            'lastName',
            new PresenceOf([
                'field'   => 'lastName',
                'message' => 'lastName.required',
            ])
        );
    
        return $this->validate($validator);
    }
}
