<?php

namespace Worklog\Validators;

use Phalcon\Validation;
use Phalcon\Validation\Message;
use Phalcon\Validation\Validator;

class EndDate extends Validator
{
    private $validator;

    /**
     * @param Validation $validation
     * @param string $attribute
     *
     * @return bool
     */
    public function validate(Validation $validator, $attribute)
    {
        $this->validator = $validator;

        $end = $validator->getValue($attribute);
        $start = $validator->getEntity()->start;

        if (strtotime($end) > strtotime($start)) {
            return true;
        }

        $this->setErrorMessage();
        return false;
    }

    private function setErrorMessage()
    {

        $message = $this->getOption('message');
        if (!$message) {
            $message = 'end.invalid';
        }

        $this->validator->appendMessage(new Message($message, $attribute, 'EndDate'));

    }
}
