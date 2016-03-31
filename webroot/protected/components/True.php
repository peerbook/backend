<?php

/**
 * Validators that validates a conditions, which should be true to succeed
 */
class True extends CValidator {

    /**
     * This can be either a function or a bool. The result of the function is parsed to a boolean 
     * Parameters of the function are function ($attribute, $object) {}
     * @var function or bool
     */
    public $condition = true;
    public $message = 'The extra condition was evaluated false';

    protected function validateAttribute($object, $attribute) {
	$attr = $object->$attribute;

	if (is_callable($this->condition)) {
	    $result = call_user_func($this->condition, $attr, $object);
	} else
	    $result = $this->condition;

	if (!$result) {
	    $this->addError($object, $attribute, $this->message);
	}
    }

}
