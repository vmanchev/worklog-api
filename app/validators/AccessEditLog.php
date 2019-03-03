<?php

namespace Worklog\Validators;

use Phalcon\Validation;
use Phalcon\Validation\Message;
use Phalcon\Validation\Validator;

class AccessEditLog extends Validator
{
    private $validator;
    private $model;
    private $user_id;
    private $current_user_id;

    /**
     * @param Validation $validation
     * @param string $attribute
     *
     * @return bool
     */
    public function validate(Validation $validator, $attribute)
    {
        $this->validator = $validator;
        $this->user_id = $validator->getValue($attribute);
        $this->current_user_id = $validator->auth->data('user')->id;
        $this->model = $validator->getEntity();

        if ($this->isLogOwner() || $this->isProjectOwner()) {
            return true;
        }

        $this->setErrorMessage();
        return false;
    }

    /**
     * Returns true when current identified user is author of this worklog
     */
    private function isLogOwner(): bool
    {
        return $this->model->user_id === $this->current_user_id;
    }

    /**
     * Returns true when current identified user is project owner where this work was logged
     */
    private function isProjectOwner(): bool
    {
        return in_array($this->model->project_id, $this->model->getProjectIdsForOwner($this->current_user_id));
    }

    private function setErrorMessage()
    {

        $message = $this->getOption('message');
        if (!$message) {
            //message was not provided, so set some default
            $message = "accessDenied";
        }

        //add message object
        $this->validator->appendMessage(new Message($message, $attribute, 'AccessEditLog'));

    }
}
