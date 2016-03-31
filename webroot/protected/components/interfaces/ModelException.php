<?php

/**
 * Class special made for model validation errors
 * @author Han
 */
class ModelException extends CHttpException {

    /**
     * @var EMongoDocument
     */
    public $model;

    /**
     * @param EMongoDocument $model
     * @param string $message
     * @param number $code
     */
    public function __construct($model, $message = '', $code = 0) {

        if (empty($message))
            $message = get_class($model) . ' not correctly validated';

        parent::__construct(400, $message, $code);

        $this->model = $model;
    }

    /**
     * @return array returns a list errors occured (field name => [ list of errors ]
     */
    public function getModelErrors() {
        return $this->model->errors;
    }

}
