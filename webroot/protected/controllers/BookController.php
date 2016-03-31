<?php

/**
 * This API is for retrieving existing books and take action on that book
 */
class BookController extends APIModelController {    
    
    public function getItem($id, $action, $context = null) {
        $model = $this->model()->findOne(['ean' => (string) $id]);       
        $this->checkAccess($model, $action, $context);
        return $model;
    }
    
    public function model() {
        return Product::model();
    }
    
    
     public function read($id) {
        return $this->getItem($id, API::ACTION_READ)->getRepresentation(APIElement::REP_FULL);
    }
}