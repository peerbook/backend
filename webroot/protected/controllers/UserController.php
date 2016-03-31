<?php

class UserController extends APIModelController {
    
    public function model() {
	return User::model();
    }       
    
    public function getItem($id, $action, $context = null) {
        
        if($id === 'me') {
            
            $model = Yii::app()->user->me;
            $this->checkAccess($model, $action, $context);
            
            return $model;
            
        } else {
            return parent::getItem($id, $action, $context);
        }
        
    }
    
    public function children() {
        return ['book'];
    }
    
    public function read($id) {
        return $this->getItem($id, API::ACTION_READ)->getRepresentation(APIElement::REP_ITEM);
    }

    public function edit($id) {
        $model = $this->getItem($id, API::ACTION_EDIT, Yii::app()->user->me );  
        
        $model->attributes = $this->getApiRequest()->getData();
         
        if($model->save()) {
            return $model->getRepresentation(APIElement::REP_ITEM);
        } else
            throw new ModelException($model);        
    }

}
