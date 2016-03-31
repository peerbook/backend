<?php

class Interaction extends APIModel implements Indexable {

    public $ean; 
        
    public $deleted = false;
    
    public $copy_ref;
    public $issuer_ref;
    public $owner_ref;
    
    public $messages = [];
    
    public $create_date;
    public $last_updated_date;
    
    public static function model($cl = __CLASS__) {
        return parent::model($cl);
    }
    
    public function collectionName() {
        return 'interactions';
    }

    public function relations() {
        return array(
            'copy' => array('one', 'Copy', '_id', 'on' => 'copy_ref'),
            'owner' => array('one', 'User', '_id', 'on' => 'owner_ref', 'embed' => true),
            'issuer' => array('one', 'User', '_id', 'on' => 'issuer_ref', 'embed' => true)
        );
    }		
    
    public function beforeValidate() {
        
        if(!isset($this->ean) && isset($this->copy))
            $this->ean = $this->copy->ean;
        
        return parent::beforeValidate();
    }
    
    public function beforeSave() {
        
        if($this->isNewRecord) {
            $this->create_date = new MongoDate();
        }
        
        $this->last_updated_date = new MongoDate();
        
        return parent::beforeSave();
    }
    
    public function rules() {
        return array(
            array('issuer, copy, owner', 'isReference', 'on' => 'insert'),
            array('ean', 'required'),
        );
    }
    
    public function fields($type) {
        if($type === 'messages') {
            return [
                'id',
                'ean',
                'messages'
            ];
        } else {
            return [
                'id', 
                'ean', 
                'issuer',
                'owner',                
                'target' => function () {
                    return mongoId($this->owner_ref) == mongoId(Yii::app()->user->me) ? 'issuer' : 'owner';
                },
                'lastMessage' => function ($obj) {
                
                    $len = count($obj->messages);
                
                    if(isset($obj->messages[$len-1])) 
                        return $obj->messages[$len-1];
                    else
                        return null;
                    
                },
                'copy' => function ($obj) {
                    return $obj->copy->product->getRepresentation(APIElement::REP_EMBED);
                }
            ];
        }
    }
    
    public function hasAccess($action, $context = null) {
        if($action === API::ACTION_ADD || $action === API::ACTION_BROWSE) {
            return true; // TODO can start interaction on anyone
        } else if($action === API::ACTION_READ) {
            $me = mongoId(Yii::app()->user->me);
            return $me === mongoId($this->issuer_ref) || $me === mongoId($this->copy->owner_ref);
        } else
            return false;
    }

    public function indexes() {
         
        $this->getCollection()->createIndex(array(
            'ean' => 1
        ));
        $this->getCollection()->createIndex(array(
            'copy_ref.$id' => 1
        ));
         $this->getCollection()->createIndex(array(
            'owner_ref.$id' => 1
        ));
        $this->getCollection()->createIndex(array(
            'issuer_ref.$id' => 1
        ));
        $this->getCollection()->createIndex(array(
            'last_updated_date' => 1
        ));         
    }
    
    public function defaultScope() {
        return array(
            'deleted' => false
        );
    }

    
    public function createMessageObject($message) {
        return array(
            'message' => $message,
            'date' => new MongoDate(),
            'user' => Yii::app()->user->me->getRepresentation(APIElement::REP_EMBED)
        );
    }
    
    public function addMessage($messageObject) {
        
        if(is_string($messageObject))
            $messageObject = $this->createMessageObject($messageObject);
        
        $result = $this->updateByPk($this->_id, [
            '$push' => ['messages' => $messageObject],
            '$set' => ['last_updated_date' => new MongoDate()]
        ]);
        
        return $result['ok'] ? $messageObject : null;
    }
}