<?php

class Copy extends APIModel implements Indexable {

    public $ean; 
        
    public $deleted = false;
    
    public $lended = false;
    
    public $owner_ref;
    public $product_ref;
    
    public $position;
    
    public $create_date;
    public $last_updated_date;
    
    public static function model($cl = __CLASS__) {
        return parent::model($cl);
    }
    
    public function collectionName() {
        return 'copies';
    }

    public function relations() {
        return array(
            'product' => array('one', 'Product', '_id', 'on' => 'product_ref', 'embed' => true),
            'owner' => array('one', 'User', '_id', 'on' => 'owner_ref', 'embed' => true)
        );
    }		
    
    public function beforeValidate() {
        
        if(!isset($this->ean) && isset($this->product))
            $this->ean = $this->product->ean;
        
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
            array('product, owner', 'isReference', 'on' => 'insert'),
            array('ean', 'required', 'on' => 'insert'),
            array('position', 'filter', 'filter' => function ($position) {
                if(empty($position) && $this->owner != null) {
                    return $this->owner->position;
                }
                return null;
            }, 'on' => 'insert'),
                    
            array('lended', 'boolean'),
        );
    }
    
    public function fields($type) {
        
        return [
            'id', 
            'ean', 
            'lended',
            'product.title',
            'distance' => function () {
                $user = Yii::app()->user->me;
                if(isset($user) && $this->position != null && $user->position != null) {
                    return round(Location::distanceGeoJSON($this->position, $user->position), 1).'km';
                }

                if($this->owner != null)
                    return $this->owner->location;

                return '';
            },
            'image' => function ($object) {
                if($object->product)
                    return $object->product->staticImage;
            },
            'user' => function ($object) {
                return $object->owner->getRepresentation(APIElement::REP_ITEM);
            }
        ];            
    }
    
    public function hasAccess($action, $context = null) {
        
        if($action === API::ACTION_DELETE || $action === API::ACTION_EDIT) {
            return mongoId($this->owner_ref['$id']) === mongoId(Yii::app()->user->me);
        }
        
        if($action === API::ACTION_READ || API::ACTION_BROWSE == $action) {
            return true;
        } else
            return false;
    }

     public function indexes() {
         
        $this->getCollection()->createIndex(array(
            'ean' => 1
        ));
        $this->getCollection()->createIndex(array(
            'owner_ref.$id' => 1
        ));
        $this->getCollection()->createIndex(array(
            'product_ref.$id' => 1
        ));
        $this->getCollection()->createIndex(array(
            'position' => '2dsphere'
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

}