<?php

class UserBookController extends APIModelController {
    
    public function model() {
        return Copy::model();
    }
    
    public function add() {
        
        $ean = $this->getApiRequest()->getDataByKey('ean');
        
        if(empty($ean))
            throw new CHttpException(400, 'ean required');
    
        $copyExists = Copy::model()->findOne([
            'ean' => $ean,
            'owner_ref.$id' => Yii::app()->user->me->_id
        ]);
        
        if($copyExists == null) {
            
            $copy = new Copy;
            $copy->owner = Yii::app()->user->me;
            $copy->product = Product::getProductByEan($ean);

            if(!$copy->save()) {
                throw new ModelException($copy);
            } else {
                return $copy->getRepresentation(APIElement::REP_ITEM);
            }
            
        } else {
            $rep = $copyExists->getRepresentation(APIElement::REP_ITEM);
            if($copyExists->deleted) {
                $copyExists->deleted = false;
                $copyExists->update(['deleted']);
            } else {
                $rep += array(
                    'alreadyExists' => true
                );
            }
            
            return $rep;
        }
    }
    
    public function delete($id) {
        $item = $this->getItem($id, API::ACTION_DELETE);
        
        $item->deleted = true;
        $item->update(['deleted']);
        
        return true;
    }
    
    public function edit($id) {
        
        $item = $this->getItem($id, API::ACTION_EDIT);
        
        $postData = $this->getApiRequest()->getData();
        if($postData == null)
            throw new CHttpException(400);
        
        $item->attributes = $postData;
        
        if($item->save()) {
            return $item->getRepresentation(APIElement::REP_ITEM);
        } else {
            throw new ModelException($item);
        }
    }
    
    public function read($id) {
        return $this->getItem($id, API::ACTION_READ, $this->getContextRecord() )->getRepresentation(APIElement::REP_ITEM);    
    }

    public function browse() {
        
        if(!$this->model()->hasAccess(API::ACTION_BROWSE, $this->getContextRecord()))
            throw new CHttpException(403);
        
        $items = $this->getContextRecord()->copies;
        
        $ret = [];
        foreach($items as $item) {
            if(!isset($item->isAdmin))
                $ret[] = $item->getRepresentation(APIElement::REP_ITEM);
        }
        
        return $ret;
    }
    
}
