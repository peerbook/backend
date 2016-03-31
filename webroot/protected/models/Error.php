<?php

/**
 * Error logging
 * @author Han
 */
class Error extends APIModel {

    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    public function collectionName() {
        return 'errors';
    }

    public function fields($type) {
        return ['id'];
    }
    
    public function beforeSave() {
        
        $this->create_date = new MongoDate();
        $this->ip = Yii::app()->request->getUserHostAddress();
        $this->browser = Yii::app()->request->getUserAgent();
        
        return parent::beforeSave();
    }
    
}
