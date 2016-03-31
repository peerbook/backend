<?php

class InteractionController extends APIModelController {
    
    public function model() {
        return Interaction::model();
    }
 
    public function children() {
        return ['message'];
    }
    
    public function add() {
        
        $copy_id = $this->getApiRequest()->getDataByKey('copy_id');
        $copy = Copy::model()->findByPk($copy_id);
        if($copy == null)
            throw new CHttpException(403);
        
        $checkInteraction = Interaction::model()->findOne([
            'issuer_ref.$id' => Yii::app()->user->me->_id,
            'copy_ref.$id' => $copy->_id,
            'deleted' => false
        ]);
        
        if($checkInteraction != null)
            return $checkInteraction->getRepresentation(APIElement::REP_ITEM);
        else {
            $int = new Interaction;
            $int->copy = $copy;
            $int->owner = $copy->owner;
            $int->issuer = Yii::app()->user->me;
            
            if($int->save()) {
                
                if(isset($copy->isAdmin)) { 
                    $int->addMessage(array(
                        'type' => 'notice',
                        'message' => 'Dit is ons supportkanaal, laat maar weten wat je van Peerbook vindt.',
                        'date' => new MongoDate(),
                        'user' => array(
                            'displayName' => 'Peerbook Support'
                        )
                    ));
                }
                
                return $int->getRepresentation(APIElement::REP_ITEM) + array(
                    'new' => true
                );                
            } else
                throw new ModelException($int);
            
        }
        
    }    
    
    public function read($id) {
        $int = $this->getItem($id, API::ACTION_READ, Yii::app()->user->me);
        return $int->getRepresentation(APIElement::REP_ITEM);
    }
    
    public function browse() {
        
        if(!$this->model()->hasAccess(API::ACTION_BROWSE))
            throw new CHttpException(403);        
        
        $me = Yii::app()->user->me->_id;
        
        $ints = Interaction::model()->find([
            '$or' => [
                ['owner_ref.$id' => $me],
                ['issuer_ref.$id' => $me]
            ],
            'deleted' => false
        ]);
        $ints->sort(['last_updated_date' => -1]);
        
        $ret = [];
        foreach($ints as $int) {
            $ret[] = $int->getRepresentation(APIElement::REP_ITEM);
        }          
        
        return $ret;
    }
    
}