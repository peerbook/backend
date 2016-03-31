<?php

abstract class APIModelController extends APIChildController {

    abstract function model();

    public function getItem($id, $action, $context = null) {

        if (!MongoId::isValid($id)) {
            throw new CHttpException(404, 'Item not found');
        }

        $model = $this->model()->findByPk($id);
        $this->checkAccess($model, $action, $context);
        return $model;
    }

    protected function checkAccess($model, $action, $context = null) {
        if ($model == null) {
            throw new CHttpException(404, 'Item of type "' . get_class($this->model()) . '" not found');
        }

        if (!$model->hasAccess($action, $context)) {
            throw new CHttpException(403, 'Cannot "' . $action . '" this item');
        }
    }

    public function browse() {
		
		if(!$this->model()->hasAccess(API::ACTION_BROWSE)) {
			throw new CHttpException(403);
		}
		
        $result = $this->model()->find();
        $ret = array();
        foreach ($result as $item) {
            $ret[] = $item->getRepresentation(APIElement::REP_ITEM);
        }
        return $ret;
    }

    public function read($id) {
        return $this->getItem($id, API::ACTION_READ)->getRepresentation(APIElement::REP_ITEM);
    }

    public function edit($id) {
        throw new CHttpException(501);
    }

    public function delete($id) {
        throw new CHttpException(501);
    }

    public function add() {
        throw new CHttpException(501);
    }

}
