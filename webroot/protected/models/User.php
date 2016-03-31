<?php
/**
 * Description of User
 *
 * @author Han
 */
class User extends APIModel implements Indexable {
    
    public $uid;
    public $email;
    public $name;
    public $token;
    
    public $location;
    
    public $position;
    
    public $create_date;
    public $last_updated_date;
    
    /** @virtual */
    private $_image_before_save;
    
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }
    
    public function collectionName() {
	return 'users';
    }
    
    public function getStaticImage() {
        
        if($this->isNewRecord)
            return null;
        
        return Yii::app()->params['cdn'].'/data/'.mongoId($this).'_user.jpg';
    }
    
    public function getImage() {
        return $this->getStaticImage();
    }
    
    public function setImageBeforeSave($avt) {
        $this->_image_before_save = $avt;
    }
    
    public function fields($type) {
        
        if($type == APIElement::REP_FULL) {
            return ['id', 'displayName', 'token', 'location', 'bookCount', 'image'];
        } else if($type == APIElement::REP_EMBED) {
            return [
                'id',
                'displayName', 
                'location',
                'image'
            ];
        } else
            return [
                'id', 
                'displayName', 
                
                'location',
                'bookCount',
                
                'sub' => function () {
                    return $this->bookCount. ' boeken';
                },
                'image',
                        
                'distance' => function () {
                    $user = Yii::app()->user->me;
                    if($this->position !== null && $user->position !== null) {
                        return round(Location::distanceGeoJSON($this->position, $user->position), 1).'km';
                    }

                    if($this->location !== null)
                        return $this->location;

                    return '';
                }
            ];
    }

    public function rules() {
        
        return array(
            array('uid', 'required'),
	    array('email', 'email'),
            array('name, displayName', 'safe'),
            array('position', 'filter', 'filter' => function ($geoJSON) {
                $geoJSON = Location::convert($geoJSON);
                if(Location::isValidGeoJSON($geoJSON)) {
                    $this->location = Location::latlngToCity($geoJSON['coordinates'][1], $geoJSON['coordinates'][0]);
                    return $geoJSON;
                }
                return null;
            }),
            array('uid', 'filter', 'filter' => function ($uid) {
                return is_array($uid) ? $uid : [$uid];
            }),
            array('emails, phoneNumbers', 'filter', 'filter' => function ($data) {
            
                if(is_array($data)) {
            
                    $ret = [];
                    foreach($data as $v) {
                        if(isset($v['value']))
                            $ret[] = $v['value'];
                        else
                            $ret[] = $v;
                    }

                    return array_values(array_unique($ret));
                } else
                    return [];
                
            }),
                    
            array('imageData', 'StoreImageData'),
	    
	    array('email', 'True', 'condition' => function ($email, $model) {
                if(empty($email))
                    return true;
                
		return $model->findOne(array('email' => $email)) == null;
	    }, 'on' => 'insert', 'message' => 'Email already exists'),
                                
        );
    }

    public function relations() {
        return [
            'copies' => array('many', 'Copy', 'owner_ref.$id', 'on' => '_id', 'where' => ['deleted' => false], 'sort' => ['last_updated_date' => -1],
            
                // update owner when this embedded is changed
                'embedded' => true,
                'embedded_relation' => 'owner'
            )
        ];
    }
    
    public function getBookCount() {
        return Yii::app()->cache->retrieve('book_count_' . mongoId($this), function () {
            return Copy::model()->count([
                'deleted' => false,
                'owner_ref.$id' => $this->_id
            ]);
        });
    }
    
    private function generateToken() {
        $data = $this->uid[0] . microtime(true) . mt_rand(0, mt_getrandmax()) . mt_rand(0, mt_getrandmax());
        return hash('sha256', $data);
    }
    
    public function beforeSave() {
        
        if($this->isNewRecord) {
            $this->create_date = new MongoDate();
            $this->token = $this->generateToken();
        }
        
        $this->last_updated_date = new MongoDate();
        
        return parent::beforeSave();
    }
    
    public function afterValidate() {
        $res = parent::afterValidate();
        
        if(!isset($this->email) && isset($this->emails[0]))
            $this->email = $this->emails[0];
        
        return $res;
    }
    
    public function afterSave() {
        
        parent::afterSave();
        
        // update children positions
        if($this->position) {
            Copy::model()->updateAll([
                'owner_ref.$id' => $this->_id
            ], [
                '$set' => ['position' => $this->position]
            ]);
        }
        
        if($this->_image_before_save != null) {
            $path = Yii::app()->basePath.'/../data/'.$this->_image_before_save;
            $newPath = Yii::app()->basePath.'/../data/'.mongoId($this).'_user_org.'.substr($path, -3);
            if(file_exists($path))
                rename($path, $newPath);
        }
        
    }
    
    public function hasAccess($action, $context = null) {
        if($action === API::ACTION_EDIT) {
            return mongoId($context) === mongoId($this);            
        } else if($action === API::ACTION_READ) {
            return true;
        } else if($action === API::ACTION_EDIT) {
            return mongoId($this) === mongoId($context);
        } else 
            return false;
    }
    
    public static function getUserByToken($token) {
       
        if(empty($token)) {
            throw new CHttpException(403, 'Token required');
        }

        $model = User::model()->findOne(['token' => $token]);
        
        return $model;
    }
    
    public function indexes() {
        $this->getCollection()->createIndex(array(
            'token' => 1
        ));
        $this->getCollection()->createIndex(array(
            'uid' => 1
        ));
        $this->getCollection()->createIndex(array(
            'position' => '2dsphere'
        ));
    }
}
