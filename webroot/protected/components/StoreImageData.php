<?php

/**
 * A validator that uploads and image through a imagedata object
 */
class StoreImageData extends CValidator {

    private function getExt($imageData) {
        
        $imageType = substr(strstr($imageData,';', true),5);

        $ext = 'png';
        if($imageType === 'image/jpeg') 
            $ext = 'jpg';
        
        return $ext;
    }
    
    private function writeImage($path, $imageData) {
        
        $success = false;
        
        $data = base64_decode(substr($imageData, strpos($imageData,',') + 1 ));      
        
        if($data) {
            $fp = fopen($path, 'wb');        
            if($fp && fwrite($fp, $data)) {
                $success = true;
            }
            fclose($fp);
        }
        
        return $success;
    }
    
    protected function validateAttribute($object, $attribute) {
	$imageData = $object->$attribute;

        if(!empty($imageData) && substr($imageData, 0, 11) === 'data:image/') {
            
            $ext = $this->getExt($imageData);
            
            if($object->isNewRecord) {
                $file = md5(microtime() . substr($imageData, 20, 10) ) . '_user_org';
                $object->setImageBeforeSave($file.'.'.$ext);
            } else {
                $file = mongoId($object) . '_user_org';
            }           
            
            $filepath = Yii::app()->basePath.'/../data/'.$file.'.'.$ext;
            
            if(!$this->writeImage($filepath, $imageData)) {                
                $this->addError($object, $attribute, "Image cannot be saved. Invalid format or cannot write to destination");
            }
            
            unset($object->$attribute);
        }
    }

}
